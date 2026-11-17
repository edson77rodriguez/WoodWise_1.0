"""Prueba sintética sin datos del usuario: GeoTIFF RGBA, CRS 32614 y geometrías WGS84."""
from pathlib import Path
import json, subprocess,sys,tempfile,uuid,math
from pyproj import Transformer
import numpy as np
from PIL import Image
import rasterio
from rasterio.transform import from_origin

script=Path(__file__).resolve().parents[1]/'local_tools/generar_teselas_mision.py'
with tempfile.TemporaryDirectory(prefix='sigmad_test_') as td:
    root=Path(td)
    left,top=450000.0,2150000.0
    w,h=1280,960
    res=0.125
    # Transparencia: 4.ª banda alpha, algunos espacios sin imagen.
    rng=np.random.default_rng(42)
    rgb=rng.integers(30,170,size=(3,h,w),dtype=np.uint8)
    alpha=np.full((1,h,w),255,dtype=np.uint8)
    alpha[:,:80,:]=0
    with rasterio.open(root/'ortho.tif','w',driver='GTiff',width=w,height=h,count=4,dtype='uint8',crs='EPSG:32614',transform=from_origin(left,top,res,res), photometric='RGB') as ds:
        ds.write(np.concatenate([rgb,alpha]));ds.colorinterp=(rasterio.enums.ColorInterp.red,rasterio.enums.ColorInterp.green,rasterio.enums.ColorInterp.blue,rasterio.enums.ColorInterp.alpha)
    tr=Transformer.from_crs('EPSG:32614','EPSG:4326',always_xy=True)
    pts=[(left+45,top-40),(left+68,top-40),(left+68,top-65),(left+45,top-65),(left+45,top-40)]
    poly=[list(tr.transform(*xy)) for xy in pts]
    mid=str(uuid.uuid4());aid=str(uuid.uuid4())
    mission={'uuid':mid,'analysis_uuid':aid,'crs':'EPSG:32614','bounds':[left,top-h*res,left+w*res,top],
             'geojson':{'type':'FeatureCollection','features':[{'type':'Feature','geometry':{'type':'Polygon','coordinates':[poly]},'properties':{'operational_id':'TEST1'}}]}}
    (root/'mission.json').write_text(json.dumps(mission))
    proc=subprocess.run([sys.executable,str(script),'--geotiff',str(root/'ortho.tif'),'--mission-json',str(root/'mission.json'),'--out',str(root/'out'),'--buffer-m','8'],capture_output=True,text=True)
    assert proc.returncode==0,(proc.stdout,proc.stderr)
    print(proc.stdout)
    cat=json.loads((root/'out'/mid/'manifest.json').read_text())
    assert cat['mission_uuid']==mid
    assert cat['bounds']==mission['bounds']
    assert cat['tile_count']>1
    assert len(cat['tiles'])==len(set(cat['tiles']))
    assert len(cat['resolutions'])>=2
    for name in cat['tiles']:
        z,x,y=name.split('/')
        p=root/'out'/mid/z/x/f'{y}.webp'
        assert p.exists()
        im=Image.open(p)
        assert im.size==(256,256),im.size
    # Verifica que rechaza archivo no correspondiente al análisis.
    bad=dict(mission);bad['bounds']=[10,11,12,13]
    (root/'bad.json').write_text(json.dumps(bad))
    proc=subprocess.run([sys.executable,str(script),'--geotiff',str(root/'ortho.tif'),'--mission-json',str(root/'bad.json'),'--out',str(root/'badout')],capture_output=True,text=True)
    assert proc.returncode!=0
    print('OK prueba sintética: RGB+Alpha, georreferenciación, WebP y validación de límites')
