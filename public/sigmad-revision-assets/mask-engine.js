/* SIGMAD · Motor de máscaras por celdas georreferenciadas v0.9.0
 * Núcleo puro, comprobable sin GPU. Entrada: Uint8Array binario (1=copa).
 * Salida: MultiPolygon en coordenadas de píxel (Y descendente).
 * Se conservan huecos y componentes; nunca se descartan deliberadamente islotes. */
(function(root, factory) {
  const api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (root) root.SigmadMaskEngine = api;
})(typeof window !== 'undefined' ? window : null, function() {
  'use strict';
  const key = (x,y,w) => y * (w+1) + x;
  const coords = (k,w) => [k % (w+1), Math.floor(k/(w+1))];
  function signedArea(ring) {
    let a=0;
    for(let i=0;i<ring.length-1;i++) a+=ring[i][0]*ring[i+1][1]-ring[i+1][0]*ring[i][1];
    return a/2;
  }
  function contains(pt, ring) {
    let inside=false;
    for(let i=0,j=ring.length-1;i<ring.length;j=i++) {
      const a=ring[i],b=ring[j];
      if((a[1]>pt[1]) !== (b[1]>pt[1]) &&
        pt[0]<(b[0]-a[0])*(pt[1]-a[1])/(b[1]-a[1])+a[0]) inside=!inside;
    }
    return inside;
  }
  function simplifyOpen(points,tolSq) {
    if(points.length<=2)return points.slice();
    const keep=new Uint8Array(points.length);keep[0]=keep[points.length-1]=1;
    const stack=[[0,points.length-1]];
    while(stack.length) {
      const [s,e]=stack.pop();let far=-1, max=tolSq;
      const a=points[s],b=points[e], dx=b[0]-a[0],dy=b[1]-a[1],len2=dx*dx+dy*dy;
      for(let i=s+1;i<e;i++) {
        const p=points[i];let dist;
        if(len2===0) dist=(p[0]-a[0])**2+(p[1]-a[1])**2;
        else {const t=Math.max(0,Math.min(1,((p[0]-a[0])*dx+(p[1]-a[1])*dy)/len2));
          dist=(p[0]-a[0]-t*dx)**2+(p[1]-a[1]-t*dy)**2;}
        if(dist>max){max=dist;far=i;}
      }
      if(far!==-1){keep[far]=1;stack.push([s,far],[far,e]);}
    }
    return points.filter((_,i)=>keep[i]===1);
  }
  function simplifyClosed(ring, tolerance) {
    if(!tolerance || ring.length<7)return ring;
    const open=ring.slice(0,-1), first=open[0];
    let far=1,dist=-1;
    for(let i=1;i<open.length;i++) {
      const d=(first[0]-open[i][0])**2+(first[1]-open[i][1])**2;
      if(d>dist){dist=d;far=i;}
    }
    const p1=simplifyOpen(open.slice(0,far+1),tolerance*tolerance);
    const p2=simplifyOpen(open.slice(far).concat([first]),tolerance*tolerance);
    const result=p1.slice(0,-1).concat(p2);
    if(result.length<4 || Math.abs(signedArea(result))<0.5)return ring;
    return result;
  }
  function polygonize(data,w,h,opts={}) {
    if(!(data instanceof Uint8Array) || !Number.isInteger(w) || !Number.isInteger(h) ||
       w<=0 || h<=0 || data.length!==w*h) throw new Error('Máscara binaria inválida.');
    const edges=new Map(); let edgeCount=0;
    function put(x,y,dir) {
      const from=key(x,y,w),nx=x+([1,0,-1,0][dir]),ny=y+([0,1,0,-1][dir]);
      let e=edges.get(from);if(!e){e=[];edges.set(from,e);}
      e.push({to:key(nx,ny,w),dir});edgeCount++;
    }
    for(let y=0;y<h;y++)for(let x=0;x<w;x++) {
      if(!data[y*w+x])continue;
      // Interior siempre a la derecha del borde; orientación exterior horaria (Y descendente).
      if(y===0 || !data[(y-1)*w+x]) put(x,y,0);
      if(x===w-1 || !data[y*w+x+1]) put(x+1,y,1);
      if(y===h-1 || !data[(y+1)*w+x]) put(x+1,y+1,2);
      if(x===0 || !data[y*w+x-1]) put(x,y+1,3);
    }
    // Un vértice donde confluyen dos contornos por la diagonal es topológicamente
    // ambiguo (contacto de punta). Lo biselamos 0.1 px hacia la dirección local
    // del recorrido para evitar anillos auto-tangentes e inválidos en SIG/GIS.
    const ambiguous=new Set();
    for(const [vertex,outgoing] of edges)if(outgoing.length>1)ambiguous.add(vertex);
    function bevelContacts(ring) {
      const pts=ring.slice(0,-1),n=pts.length;
      if(n<4)return ring;
      const corrected=pts.map((p,i)=>{
        if(!ambiguous.has(key(p[0],p[1],w)))return p;
        const a=pts[(i+n-1)%n],b=pts[(i+1)%n];
        return [p[0]+0.1*(Math.sign(a[0]-p[0])+Math.sign(b[0]-p[0])),
                p[1]+0.1*(Math.sign(a[1]-p[1])+Math.sign(b[1]-p[1]))];
      });
      corrected.push(corrected[0]);return corrected;
    }
    const rings=[];let used=0;
    while(edges.size) {
      const start=edges.keys().next().value;let current=start,prev=-1;
      const r=[coords(start,w)];let safety=0;
      do {
        const candidates=edges.get(current);
        if(!candidates || candidates.length===0)throw new Error('Borde raster abierto: máscara inconsistente.');
        let idx=0;
        if(prev>=0 && candidates.length>1) {
          const priority=[(prev+1)%4,prev,(prev+3)%4,(prev+2)%4];
          idx=candidates.findIndex(c=>c.dir===priority[0]);
          if(idx<0) idx=candidates.findIndex(c=>c.dir===priority[1]);
          if(idx<0) idx=candidates.findIndex(c=>c.dir===priority[2]);
          if(idx<0) idx=candidates.findIndex(c=>c.dir===priority[3]);
          if(idx<0)idx=0;
        }
        const edge=candidates.splice(idx,1)[0];if(!candidates.length)edges.delete(current);
        current=edge.to;prev=edge.dir;used++;safety++;
        r.push(coords(current,w));
        if(safety>edgeCount+1)throw new Error('Borde raster excede máximo de aristas.');
      }while(current!==start);
      if(r.length>=5 && Math.abs(signedArea(r))>=1)rings.push(simplifyClosed(bevelContacts(r),opts.tolerance??0.3));
    }
    const outers=[],holes=[];
    for(const ring of rings){const a=signedArea(ring);if(a>0)outers.push({ring,area:a,holes:[]});else holes.push(ring);}
    for(const ring of holes) {
      // Prueba de inclusión desde un punto del anillo; no está sobre el contorno exterior.
      let target=null;
      for(const outer of outers) {
        if(contains(ring[0],outer.ring) && (!target || outer.area<target.area))target=outer;
      }
      if(!target)throw new Error('Se detectó un hueco sin polígono contenedor.');
      target.holes.push(ring);
    }
    return {polygons:outers.map(o=>[o.ring,...o.holes]),loops:rings.length,edges:used};
  }
  function geoMultiPolygon(pixelPolygons,minX,maxY,gsd) {
    if(!Number.isFinite(gsd) || gsd<=0)throw new Error('GSD incorrecto.');
    const transform=p=>[minX+p[0]*gsd,maxY-p[1]*gsd];
    return pixelPolygons.map(p=>p.map(r=>r.map(transform)));
  }
  return Object.freeze({polygonize,geoMultiPolygon,signedArea,contains});
});
