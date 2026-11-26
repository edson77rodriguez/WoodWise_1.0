#!/usr/bin/env python3
"""SIGMAD: crea una pirámide HD completa en CRS UTM para revisión IA.

Se ejecuta SOLO en el equipo local que tiene el ortomosaico GeoTIFF original.
NO modifica el raster de origen. Requiere pip install rasterio pillow numpy.
No mezcla los límites ni las capas de las misiones de brigada.
"""
from __future__ import annotations

import argparse
import json
import math
import re
import shutil
from datetime import datetime, timezone
from pathlib import Path
from uuid import UUID


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--ortomosaico', type=Path, required=True, help='GeoTIFF/COG maestro georreferenciado')
    parser.add_argument('--analysis-uuid', required=True, help='UUID del análisis compartido por las misiones')
    parser.add_argument('--mision', type=Path, help='JSON misión original, usado para revisar CRS/identificador y cobertura')
    parser.add_argument('--salida', type=Path, required=True, help='Carpeta vacía de salida, por ejemplo C:\\SIGMAD\\master_hd')
    parser.add_argument('--plan', action='store_true', help='Solo planificación: no crea ningún archivo')
    parser.add_argument('--max-tiles', type=int, default=12000, help='Límite preventivo de teselas, default 12000')
    parser.add_argument('--quality', type=int, default=94, help='Compresión WebP, rango 80-100')
    parser.add_argument('--overwrite', action='store_true', help='Permite reemplazar una carpeta de salida existente')
    a = parser.parse_args()
    try:
        from rasterio import open as rio_open
        from rasterio.enums import Resampling
        from rasterio.vrt import WarpedVRT
        from rasterio.windows import Window
        from affine import Affine
        import numpy as np
        from PIL import Image
    except ImportError as e:
        parser.error(f'Dependencia ausente: {e}. Ejecuta pip install rasterio pillow numpy')

    try:
        id_clean = str(UUID(a.analysis_uuid))
    except ValueError as e:
        parser.error(f'--analysis-uuid inválido: {e}')
    if id_clean != a.analysis_uuid.lower() or not 80 <= a.quality <= 100:
        parser.error('El UUID debe ser canónico y --quality debe estar entre 80 y 100.')
    if not a.ortomosaico.is_file():
        parser.error('No existe el ortomosaico original.')
    if a.mision:
        source = json.loads(a.mision.read_text(encoding='utf-8-sig'))
        if source.get('analysis_uuid') != id_clean:
            parser.error('El analysis_uuid de la misión no coincide: podrías mezclar ortomosaicos de distintas áreas.')
    else:
        source = None

    with rio_open(a.ortomosaico) as src:
        crs = src.crs.to_string() if src.crs else None
        if not crs or not re.fullmatch(r'EPSG:(326|327)\d{2}', crs):
            parser.error(f'Se requiere GeoTIFF en UTM WGS84 (EPSG:326xx/327xx), recibido {crs}. Reproyecta primero.')
        if src.count < 3 or any(dt != 'uint8' for dt in src.dtypes[:3]):
            parser.error('Se requieren al menos tres bandas RGB uint8. No realizamos cambios radiométricos automáticos.')
        bounds = src.bounds
        if bounds.right <= bounds.left or bounds.top <= bounds.bottom:
            parser.error('Extensión espacial inválida.')
        if source:
            if source.get('crs') != crs:
                parser.error('El CRS del JSON de misión y el GeoTIFF no coinciden.')
            b = source.get('bounds')
            if b and len(b) == 4:
                overlap_w = max(0.0, min(float(b[2]), bounds.right) - max(float(b[0]), bounds.left))
                overlap_h = max(0.0, min(float(b[3]), bounds.top) - max(float(b[1]), bounds.bottom))
                coverage = overlap_w * overlap_h / max(1e-9, (float(b[2])-float(b[0]))*(float(b[3])-float(b[1])))
                if coverage < 0.9:
                    parser.error(f'El TIFF cubre solo {coverage*100:.1f}% del área de la misión; revisa que sea el ortomosaico correcto.')
        gsd = max(math.hypot(src.transform.a, src.transform.d), math.hypot(src.transform.b, src.transform.e))
        if not (0 < gsd < 10):
            parser.error('GSD anómalo: verifica resolución y unidades del TIFF.')
        width_m, height_m = bounds.right-bounds.left, bounds.top-bounds.bottom
        zmax = max(0, math.ceil(math.log2(max(width_m,height_m)/(256*gsd))))
        resolutions = [gsd*2**(zmax-z) for z in range(zmax+1)]
        grid = []
        for z, resolution in enumerate(resolutions):
            cols = math.ceil(width_m/(256*resolution))
            rows = math.ceil(height_m/(256*resolution))
            grid.append((z, cols, rows))
        count = sum(cols*rows for _,cols,rows in grid)
        print(f'Análisis: {id_clean}\nCRS: {crs}\nDimensiones del original: {src.width} x {src.height} px')
        print(f'Cobertura maestra: {width_m:.1f} x {height_m:.1f} m | GSD: {gsd*100:.2f} cm/px')
        print(f'Niveles: {zmax+1} (0..{zmax}) | Teselas totales: {count:,}')
        print('Nivel / resolución (m/px) / columnas / filas / total')
        for z,cols,rows in grid:
            print(f'  {z:2d} / {resolutions[z]:.4f} / {cols:4d} / {rows:4d} / {cols*rows:,}')
        print('NOTA: el mapa completo puede necesitar varios GB de espacio y subir muchas imágenes por SFTP.')
        if a.plan:
            print('SOLO PLANIFICACIÓN: no se generaron archivos.')
            return
        if count > a.max_tiles:
            parser.error(f'Excede el límite preventivo ({count}>{a.max_tiles}). Si tienes almacenamiento suficiente, sube --max-tiles. No se ha generado nada.')
        if a.salida.exists() and any(a.salida.iterdir()):
            if not a.overwrite:
                parser.error('La carpeta de salida no está vacía. Usa otra ruta o --overwrite.')
            shutil.rmtree(a.salida)
        a.salida.mkdir(parents=True, exist_ok=True)
        tiles_root = a.salida/'tiles'
        for z, cols, rows in grid:
            resolution = resolutions[z]
            vrt_width = math.ceil(width_m/resolution)
            vrt_height = math.ceil(height_m/resolution)
            out_transform = Affine(resolution,0,bounds.left,0,-resolution,bounds.top)
            with WarpedVRT(src, crs=src.crs, transform=out_transform,width=vrt_width,height=vrt_height,
                           resampling=Resampling.bilinear,nodata=src.nodata) as vrt:
                for x in range(cols):
                    output_folder = tiles_root/str(z)/str(x)
                    output_folder.mkdir(parents=True, exist_ok=True)
                    for y in range(rows):
                        tw=min(256,vrt_width-256*x)
                        th=min(256,vrt_height-256*y)
                        if tw <= 0 or th <= 0:
                            continue
                        arr=vrt.read(indexes=[1,2,3], window=Window(x*256,y*256,tw,th),masked=True)
                        rgb=np.zeros((256,256,3),dtype=np.uint8)
                        rgb[:th,:tw,:] = np.moveaxis(arr.filled(0),0,2)
                        alpha=np.zeros((256,256),dtype=np.uint8)
                        alpha[:th,:tw] = np.where(np.any(np.ma.getmaskarray(arr),axis=0),0,255).astype(np.uint8)
                        out = Image.fromarray(np.dstack((rgb,alpha)),mode='RGBA')
                        out.save(output_folder/f'{y}.webp',format='WEBP',quality=a.quality,method=3)
            print(f'Nivel {z}/{zmax} completo: {cols*rows} teselas')
        manifest={
            'schema':'sigmad-master-utm-v1','analysis_uuid':id_clean,
            'crs':crs,'extent':[bounds.left,bounds.bottom,bounds.right,bounds.top],
            'origin':[bounds.left,bounds.top],'resolutions':resolutions,
            'tile_size':256,'tile_count':count,'format':'webp','gsd_m':gsd,
            'original_pixels':[src.width,src.height],
            'generated_at':datetime.now(timezone.utc).isoformat(),
            'source_name':a.ortomosaico.name,
        }
        (a.salida/'manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
        print(f'HECHO: {a.salida}\nSube el CONTENIDO de esa carpeta a storage/app/sigmad_master_hd/{id_clean}/ en Hostinger.')

if __name__=='__main__':
    main()
