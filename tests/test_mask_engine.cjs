const assert=require('node:assert/strict');
const engine=require('../public/sigmad-revision-assets/mask-engine.js');
const W=30,H=22;
let data=new Uint8Array(W*H);
for(let y=2;y<17;y++)for(let x=3;x<20;x++)data[y*W+x]=1;
// Hole interior
for(let y=7;y<12;y++)for(let x=8;x<14;x++)data[y*W+x]=0;
// Detached crown
for(let y=4;y<9;y++)for(let x=23;x<28;x++)data[y*W+x]=1;
const result=engine.polygonize(data,W,H);
assert.equal(result.polygons.length,2,'Dos componentes independientes');
const areas=result.polygons.map(poly=>poly.reduce((v,r)=>v+engine.signedArea(r),0)).sort((a,b)=>b-a);
assert.deepEqual(areas,[15*17-5*6,5*5],'Área exacta de píxeles, preservando agujero');
assert(result.polygons.some(p=>p.length===2),'Conserva hueco real');
assert(result.polygons.every(p=>engine.signedArea(p[0])>0));
const world=engine.geoMultiPolygon(result.polygons,380000,2128000,0.03);
assert(Math.abs(world[0][0][0][0]-380000)>0);
assert(world[0].every(r=>r[0][0]===r.at(-1)[0] && r[0][1]===r.at(-1)[1]));
// Diagonal contact should not turn into a self crossing contour.
const diag=new Uint8Array(25);diag[1*5+1]=1;diag[2*5+2]=1;
const d=engine.polygonize(diag,5,5);assert.equal(d.polygons.length,2);
// Brush can completely erase a small island, without inventing geometry.
const zero=new Uint8Array(24);assert.equal(engine.polygonize(zero,6,4).polygons.length,0);
console.log('OK: áreas, huecos, múltiples copas, diagonal, máscara vacía y coordenadas UTM');
