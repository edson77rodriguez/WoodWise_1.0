/* SIGMAD Campo V0.6.0 — validación y recuperación segura de imágenes offline.
   Solo elimina UN 0x0A inicial cuando a continuación existe una firma JPEG/PNG/WebP
   válida Y el navegador puede decodificar el resultado. No modifica fuentes IA. */
(() => {
  'use strict';
  function signature(bytes, i) {
    const eq = (...values) => values.every((value, n) => bytes[i+n] === value);
    if (eq(0xff, 0xd8, 0xff)) return 'image/jpeg';
    if (eq(0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a)) return 'image/png';
    if (eq(0x52, 0x49, 0x46, 0x46) &&
        bytes[i+8] === 0x57 && bytes[i+9] === 0x45 &&
        bytes[i+10] === 0x42 && bytes[i+11] === 0x50) return 'image/webp';
    return null;
  }
  async function decodable(blob) {
    if (typeof createImageBitmap === 'function') {
      try {
        const image = await createImageBitmap(blob);
        if (!image.width || !image.height) { image.close(); return false; }
        image.close();
        return true;
      } catch (_) { return false; }
    }
    // Compatibilidad con navegadores móviles sin createImageBitmap.
    return new Promise(resolve => {
      const url = URL.createObjectURL(blob);
      const img = new Image();
      const finish = ok => { img.onload = null; img.onerror = null; URL.revokeObjectURL(url); resolve(ok); };
      img.onload = () => finish(Boolean(img.naturalWidth && img.naturalHeight));
      img.onerror = () => finish(false);
      img.src = url;
    });
  }
  async function normalize(blob, kind='preview') {
    const maxBytes = kind === 'tile' ? 500000 : 20*1024*1024;
    if (!(blob instanceof Blob) || !blob.size || blob.size > maxBytes + 1) return null;
    const head = new Uint8Array(await blob.slice(0, 16).arrayBuffer());
    const originalMime = signature(head, 0);
    const extraNewline = !originalMime && head[0] === 0x0a && Boolean(signature(head, 1));
    const mime = originalMime || (extraNewline ? signature(head, 1) : null);
    if (!mime || (kind === 'tile' && mime !== 'image/webp')) return null;
    const corrected = extraNewline
      ? new Blob([blob.slice(1)], {type:mime})
      : (blob.type === mime ? blob : new Blob([blob], {type:mime}));
    if (corrected.size > maxBytes || !(await decodable(corrected))) return null;
    return {blob:corrected, repaired:extraNewline, format:mime};
  }
  window.SIGMAD_RASTER = Object.freeze({normalize});
})();
