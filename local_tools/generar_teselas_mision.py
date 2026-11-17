#!/usr/bin/env python3
"""SIGMAD Campo V0.2.1: produce teselas UTM WebP en una región de misión.

Ejemplo:
 python generar_teselas_mision.py --geotiff vuelo_nublado.tif \
   --mission-json mision.json --out ./paquetes --buffer-m 12

Es deliberadamente conservador: exige GeoTIFF RGB uint8 north-up, CRS UTM,
coincidencia de CRS y extensión con la misión, y limita teselas y bytes.
"""
import argparse
import hashlib
import json
import math
from pathlib import Path
import sys
import uuid
import zipfile

import numpy as np
from PIL import Image
import rasterio
from rasterio.enums import Resampling
from rasterio.windows import from_bounds
from pyproj import Transformer

TILE_SIZE = 256
SCHEMA = 'sigmad-campo-utm-tiles-v1'


def bounds_of_features(geojson, source_crs):
    tr = Transformer.from_crs('EPSG:4326', source_crs, always_xy=True)
    boxes = []
    features = geojson.get('features', [])
    for ft in features:
        g = ft.get('geometry') or {}
        kind = g.get('type')
        if kind not in ('Polygon', 'MultiPolygon'):
            continue
        # Multipolygon/Polygon may contain holes; all points are safe for envelope.
        coords = g.get('coordinates') or []
        polys = [coords] if kind == 'Polygon' else coords
        coords_xy = []
        for rings in polys:
            for ring in rings:
                for pt in ring:
                    if len(pt) >= 2 and all(math.isfinite(float(t)) for t in pt[:2]):
                        coords_xy.append(tr.transform(float(pt[0]), float(pt[1])))
        if coords_xy:
            xs, ys = zip(*coords_xy)
            boxes.append((min(xs), min(ys), max(xs), max(ys)))
    return boxes


def make_tile(ds, extent, index_rgb, quality):
    left, bottom, right, top = extent
    win = from_bounds(left, bottom, right, top, transform=ds.transform)
    data = ds.read(index_rgb, window=win, boundless=True,
                   out_shape=(3, TILE_SIZE, TILE_SIZE),
                   resampling=Resampling.bilinear, fill_value=0)
    valid = ds.dataset_mask(window=win, boundless=True,
                            out_shape=(TILE_SIZE, TILE_SIZE),
                            resampling=Resampling.nearest)
    rgb = np.transpose(data, (1, 2, 0))
    rgba = np.dstack([rgb, valid])
    return Image.fromarray(rgba, mode='RGBA')


def main():
    p = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawTextHelpFormatter)
    p.add_argument('--geotiff', type=Path, required=True)
    p.add_argument('--mission-json', type=Path, required=True,
                   help='JSON de /campo/misiones/{uuid}/datos, guardado desde el navegador')
    p.add_argument('--out', type=Path, required=True)
    p.add_argument('--buffer-m', type=float, default=12,
                   help='Margen alrededor de cada copa, en metros (por defecto 12)')
    p.add_argument('--quality', type=int, default=86,
                   help='Calidad WebP: 1..100')
    p.add_argument('--max-tiles', type=int, default=850,
                   help='Límite preventivo del número de teselas')
    p.add_argument('--max-mb', type=int, default=120,
                   help='Límite preventivo del peso del paquete')
    args = p.parse_args()
    if args.buffer_m < 0 or args.buffer_m > 50:
        p.error('--buffer-m debe estar entre 0 y 50')
    if not 1 <= args.quality <= 100:
        p.error('--quality debe estar entre 1 y 100')
    if not 1 <= args.max_tiles <= 2000:
        p.error('--max-tiles debe estar entre 1 y 2000')

    mission = json.loads(args.mission_json.read_text(encoding='utf-8'))
    mission_uuid = str(mission.get('uuid',''))
    try: uuid.UUID(mission_uuid)
    except (ValueError,AttributeError): p.error('El JSON no tiene UUID de misión válido')
    if not mission.get('analysis_uuid') or not (mission.get('geojson') or {}).get('features'):
        p.error('JSON de misión incompleto (analysis_uuid, geojson.features)')
    mbounds = mission.get('bounds')
    if not isinstance(mbounds, list) or len(mbounds) != 4:
        p.error('JSON de misión no tiene bounds')
    if not args.geotiff.exists(): p.error('No existe GeoTIFF')

    with rasterio.open(args.geotiff) as ds:
        crs = str(ds.crs)
        if crs != mission.get('crs'):
            p.error(f'CRS diferente: TIFF {crs}, misión {mission.get("crs")}')
        if not (crs.startswith('EPSG:326') or crs.startswith('EPSG:327')):
            p.error('El piloto requiere un CRS UTM EPSG:326xx/327xx')
        if ds.count < 3 or ds.dtypes[0] != 'uint8':
            p.error('Se requiere GeoTIFF RGB uint8 (tres bandas + alpha opcional)')
        transform = ds.transform
        if abs(transform.b)>1e-9 or abs(transform.d)>1e-9 or transform.a<=0 or transform.e>=0:
            p.error('GeoTIFF rotado/cizallado; primero crear copia north-up con rasterio/GDAL')
        bounds = [float(ds.bounds.left), float(ds.bounds.bottom),
                  float(ds.bounds.right), float(ds.bounds.top)]
        tol = max(0.5, max(transform.a, -transform.e)*3)
        if max(abs(a-float(b)) for a,b in zip(bounds, mbounds))>tol:
            p.error('La extensión del GeoTIFF no coincide con la misión. Revisa el vuelo seleccionado.')
        envelopes=bounds_of_features(mission['geojson'], ds.crs)
        if not envelopes:
            p.error('No hay copas con geometría poligonal WGS84 válida')
        pixel_res = max(transform.a, -transform.e)
        max_zoom = max(0, math.ceil(math.log2(max(ds.width,ds.height)/512)))
        if max_zoom > 12:
            p.error('Demasiados niveles; el piloto admite máximo 12')
        resolutions = [pixel_res*2**(max_zoom-z) for z in range(max_zoom+1)]
        boxes = []
        for xa,ya,xb,yb in envelopes:
            xa, xb = max(bounds[0],xa-args.buffer_m), min(bounds[2],xb+args.buffer_m)
            ya, yb = max(bounds[1],ya-args.buffer_m), min(bounds[3],yb+args.buffer_m)
            if xb>xa and yb>ya: boxes.append((xa,ya,xb,yb))
        if not boxes:
            p.error('Las copas seleccionadas están fuera del GeoTIFF')

        tiles=set()
        for z, resolution in enumerate(resolutions):
            size=resolution*TILE_SIZE
            max_x = math.ceil((bounds[2]-bounds[0])/size)-1
            max_y = math.ceil((bounds[3]-bounds[1])/size)-1
            for xa,ya,xb,yb in boxes:
                lo_x=max(0, int(math.floor((xa-bounds[0])/size)))
                hi_x=min(max_x, int(math.floor((xb-bounds[0]-1e-9)/size)))
                lo_y=max(0, int(math.floor((bounds[3]-yb)/size)))
                hi_y=min(max_y, int(math.floor((bounds[3]-ya-1e-9)/size)))
                for x in range(lo_x,hi_x+1):
                    for y in range(lo_y,hi_y+1):
                        tiles.add((z,x,y))
        if not tiles: p.error('Ninguna tesela seleccionada')
        if len(tiles)>args.max_tiles:
            p.error(f'Se requieren {len(tiles)} teselas (límite {args.max_tiles}). Prueba --buffer-m 6 o divide la misión.')

        # SHA256 completo: evita reutilizar teselas antiguas si se sustituye el
        # GeoTIFF sin cambiar dimensiones, nombre o resolución.
        sha=hashlib.sha256()
        with args.geotiff.open('rb') as raw:
            for chunk in iter(lambda:raw.read(8*1024*1024),b''):
                sha.update(chunk)
        source_hash=sha.hexdigest()

        target=args.out/mission_uuid
        if target.exists():
            p.error(f'La carpeta {target} ya existe; no sobrescribimos paquetes existentes')
        target.mkdir(parents=True)
        size_bytes=0; written=[]
        try:
            for z,x,y in sorted(tiles):
                res=resolutions[z]; span=TILE_SIZE*res
                left=bounds[0]+x*span; right=left+span
                top=bounds[3]-y*span; bottom=top-span
                img=make_tile(ds, (left,bottom,right,top), [1,2,3],args.quality)
                path=target/str(z)/str(x)/f'{y}.webp'
                path.parent.mkdir(parents=True,exist_ok=True)
                img.save(path,'WEBP',quality=args.quality,method=4)
                size_bytes+=path.stat().st_size
                if size_bytes>args.max_mb*1024*1024:
                    raise RuntimeError(f'Más de {args.max_mb} MB; reduce --buffer-m o divide la misión')
                written.append(f'{z}/{x}/{y}')
            fingerprint=hashlib.sha256((mission_uuid+source_hash+crs+json.dumps(written)+str(args.quality)+str(args.buffer_m)).encode()).hexdigest()[:16]
            catalog={
                'schema':SCHEMA, 'mission_uuid':mission_uuid,
                'analysis_uuid':str(mission['analysis_uuid']),
                'crs':crs, 'bounds':bounds,
                'origin':[bounds[0],bounds[3]],
                'resolutions':resolutions,'tile_size':TILE_SIZE,'format':'webp',
                'tiles':written,'tile_count':len(written),'tile_bytes':size_bytes,
                'tile_set_id':fingerprint,
                'buffer_m':args.buffer_m,'source_width':ds.width,'source_height':ds.height,
                'native_resolution_m':pixel_res,'geotiff_sha256':source_hash,
                'producer':'SIGMAD Campo V0.2.1, UTM native grid, RGB WebP',
            }
            (target/'manifest.json').write_text(json.dumps(catalog,indent=2,ensure_ascii=False),encoding='utf-8')
        except Exception:
            import shutil
            shutil.rmtree(target,ignore_errors=True)
            raise
        bundle = args.out / f'SIGMAD_HD_{mission_uuid}.zip'
        with zipfile.ZipFile(bundle, 'w', compression=zipfile.ZIP_STORED, allowZip64=True) as zf:
            for fp in sorted(target.rglob('*')):
                if fp.is_file():
                    zf.write(fp, fp.relative_to(args.out))
        print(f'ZIP para Hostinger: {bundle}')
        print(f'LISTO: {target}')
        print(f'{len(written)} teselas, {size_bytes/1024/1024:.2f} MB')
        print(f'CRS {crs}; resolución nativa {pixel_res:.6f} m/px; niveles {len(resolutions)}')
        print('Sube el ZIP a Laravel/storage/app/private/sigmad-campo-tiles y descomprímelo allí.')

if __name__=='__main__':
    try: main()
    except Exception as e:
        print('ERROR:',str(e),file=sys.stderr);sys.exit(2)
