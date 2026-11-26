// Pruebas unitarias del validador usando un decodificador simulado.
// No sustituyen las pruebas en navegadores reales.
const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const sandbox = {window:{}, Blob, Uint8Array, URL, Image: class {}};
sandbox.createImageBitmap=async blob=>{
  const h=new Uint8Array(await blob.slice(0,16).arrayBuffer());
  const jpg=h[0]===255&&h[1]===216&&h[2]===255;
  const png=h[0]===137&&h[1]===80&&h[2]===78;
  const webp=h[0]===82&&h[1]===73&&h[2]===70&&h[3]===70&&h[8]===87&&h[9]===69&&h[10]===66&&h[11]===80;
  if(!(jpg||png||webp))throw Error('invalid');
  return {width:256,height:256,close(){}};
};
vm.runInNewContext(fs.readFileSync(__dirname+'/../public/sigmad-campo-assets/campo-raster-integridad.js','utf8'), sandbox);
(async()=>{
 const {normalize}=sandbox.window.SIGMAD_RASTER;
 const jpg=new Blob([Uint8Array.from([0xff,0xd8,0xff,0xe0,1,2,3])],{type:'image/jpeg'});
 const webp=new Blob([Uint8Array.from([82,73,70,70,9,0,0,0,87,69,66,80,86,80,56,88])],{type:'image/webp'});
 for(const [kind,blob] of [['preview',jpg],['tile',webp]]){
   const original=await normalize(blob,kind);
   assert(original && !original.repaired);
   const broken=new Blob([Uint8Array.from([10]),blob],{type:blob.type});
   const repaired=await normalize(broken,kind);
   assert(repaired && repaired.repaired && repaired.blob.size===blob.size);
 }
 assert.strictEqual(await normalize(new Blob(['not an image']), 'preview'),null);
 assert.strictEqual(await normalize(jpg,'tile'),null);
 console.log('OK: JPEG y WebP válidos, prefijo 0A recuperable, datos inválidos rechazados.');
})().catch(e=>{console.error(e);process.exit(1)});
