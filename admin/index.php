<?php
// /admin/index.php
require_once __DIR__ . '/guard.php'; // obliga sesión iniciada
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Catálogo de Revistas CAPMEX (Admin)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root{ --bg:#0f0f10; --panel:#161617; --txt:#eaeaea; --muted:#a0a0a0; --accent:#ff9f43; --maxw:1200px; }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;background:var(--bg);color:var(--txt);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    a{color:inherit;text-decoration:none}
    .wrap{max-width:var(--maxw);margin:0 auto;padding:18px 16px}

    header{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:14px}
    h1{margin:0;font-size:clamp(20px,2.6vw,28px)}
    .bar{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto}
    .bar input,.bar select{background:#1f1f21;color:var(--txt);border:1px solid #2a2a2c;border-radius:10px;padding:10px 12px;min-width:160px}
    .btn{background:#232325;border:1px solid #2e2e31;color:var(--txt);border-radius:10px;padding:10px 12px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
    .btn:hover{background:#2b2b2f}

    .muted{color:var(--muted);font-size:13px}
    .grid{display:grid;gap:14px;grid-template-columns:repeat(auto-fill,minmax(220px,1fr))}

    /* Tarjeta contenedora */
    .card{position:relative;background:var(--panel);border:1px solid #252528;border-radius:14px;overflow:hidden;display:flex;flex-direction:column}
    /* Enlace que cubre portada + meta (evita que tape el botón) */
    .card .cover{display:block;color:inherit;text-decoration:none}
    /* Botón Eliminar arriba siempre visible */
    .card .del{
      position:absolute;z-index:10;top:8px;right:8px;
      background:#b91c1c;color:#fff;border:0;border-radius:10px;
      padding:6px 10px;cursor:pointer;font-size:12px;opacity:.95
    }
    .card .del:hover{opacity:1}

    .thumb{aspect-ratio:2/3;background:#0b0b0b;display:block;position:relative;overflow:hidden}
    .thumb img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .25s ease}
    .card:hover .thumb img{transform:scale(1.03)}
    .meta{padding:10px 12px;display:flex;flex-direction:column;gap:4px}
    .title{font-weight:600;line-height:1.25}
    .sub{font-size:13px;color:var(--muted)}
    .tags{display:flex;gap:6px;flex-wrap:wrap;margin-top:6px}
    .tag{font-size:12px;color:#cfcfcf;border:1px solid #303033;background:#202023;border-radius:999px;padding:2px 8px}
    footer{margin-top:16px;display:flex;justify-content:center}
    .empty{padding:24px;text-align:center;color:var(--muted)}

    /* diálogo */
    dialog{border:1px solid #303033;background:#171718;color:var(--txt);border-radius:14px;padding:0;max-width:min(760px,96vw)}
    dialog header{padding:12px 14px;border-bottom:1px solid #2a2a2c}
    dialog .content{padding:14px}
    dialog .row{display:flex;gap:8px;flex-wrap:wrap}
    dialog input{width:100%;background:#1f1f21;color:var(--txt);border:1px solid #2a2a2c;border-radius:10px;padding:10px 12px}
    dialog .actions{display:flex;gap:8px;justify-content:flex-end;padding:12px 14px;border-top:1px solid #2a2a2c}
    .code{background:#0e0e0f;border:1px solid #2a2a2c;border-radius:10px;padding:10px;overflow:auto;font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;font-size:12px;color:#e6e6e6}
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <h1>Catálogo de Revistas CAPMEX</h1>
      <div class="bar">
        <input id="q" type="search" placeholder="Buscar título o etiqueta…" />
        <select id="year"></select>
        <select id="sort">
          <option value="date-desc">Más recientes</option>
          <option value="date-asc">Más antiguas</option>
          <option value="title-asc">Título A–Z</option>
          <option value="title-desc">Título Z–A</option>
        </select>
        <button id="btnUpload" class="btn">Subir revista</button>
        <a class="btn" href="logout.php" title="Cerrar sesión">Cerrar sesión</a>
      </div>
    </header>

    <div id="count" class="muted" style="margin-bottom:8px"></div>

    <section id="grid" class="grid" aria-live="polite"></section>
    <div id="empty" class="empty" hidden>No hay resultados con los filtros actuales.</div>

    <footer class="muted">Tip: Clic en una portada abre el visor con la revista seleccionada.</footer>
  </div>

  <!-- Diálogo: Subir revista -->
  <dialog id="dlgUpload">
    <header><strong>Subir nueva revista</strong></header>
    <div class="content">
      <div class="row">
        <input id="uTitle" placeholder="Título (ej. INTERCo.MX No. 2)" />
        <input id="uMonth" type="month" />
      </div>
      <div class="row">
        <input id="uTags" placeholder="Etiquetas (separadas por coma, ej. salud, turismo)" />
      </div>
      <div class="row" style="gap:12px;align-items:flex-start;margin-top:8px">
        <div style="flex:1 1 260px">
          <input id="uPdf" type="file" accept="application/pdf" />
          <div class="muted" style="margin-top:6px">Selecciona el PDF (máx 25 MB)</div>
          <canvas id="uCanvas" width="400" height="600" style="margin-top:10px;background:#000;border-radius:10px;border:1px solid #2a2a2c"></canvas>
          <div class="muted" style="margin-top:6px">Miniatura 400×600 (generada)</div>
        </div>
        <div style="flex:1 1 320px">
          <div class="muted" style="margin-bottom:6px">Ficha generada</div>
          <pre id="uJson" class="code" style="height:260px;overflow:auto">{ }</pre>
        </div>
      </div>
    </div>
    <div class="actions">
      <button id="uCancel" class="btn">Cancelar</button>
      <button id="uSend" class="btn" style="background:#ff9f43;color:#121212;border-color:#cd7e2f;font-weight:600">Guardar en servidor</button>
    </div>
  </dialog>

  <!-- PDF.js local (para generar miniatura) -->
  <script src="../assets/revistas/portadas/libs/pdfjs/pdf.min.js"></script>
  <script>
    if (window.pdfjsLib) {
      pdfjsLib.GlobalWorkerOptions.workerSrc = "../assets/revistas/portadas/libs/pdfjs/pdf.worker.min.js";
    }
  </script>

  <script>
    // ===== CSRF desde PHP =====
    window.CSRF = <?= json_encode($_SESSION['csrf'] ?? '') ?>;
    // ===== Config =====
    const DATA_URL   = '../revistas.json';
    const VIEWER_URL = '../flipbook2.html';
    const MAX_MB     = 25;

    // Estado y refs
    let data = [], filtered = [];
    const $grid = document.getElementById('grid');
    const $empty = document.getElementById('empty');
    const $count = document.getElementById('count');
    const $q = document.getElementById('q');
    const $year = document.getElementById('year');
    const $sort = document.getElementById('sort');

    // Subida
    const dlgU = document.getElementById('dlgUpload');
    const uTitle = document.getElementById('uTitle');
    const uMonth = document.getElementById('uMonth');
    const uTags  = document.getElementById('uTags');
    const uPdf   = document.getElementById('uPdf');
    const uCanvas= document.getElementById('uCanvas');
    const uJson  = document.getElementById('uJson');
    const uCancel= document.getElementById('uCancel');
    const uSend  = document.getElementById('uSend');

    (async function init(){
      await fetchAndRender();
      $q.addEventListener('input', apply);
      $year.addEventListener('change', apply);
      $sort.addEventListener('change', apply);

      document.getElementById('btnUpload').addEventListener('click', ()=>{
        uTitle.value=''; uMonth.value=''; uTags.value=''; uPdf.value=''; uJson.textContent='{ }';
        const ctx=uCanvas.getContext('2d'); ctx.fillStyle='#000'; ctx.fillRect(0,0,uCanvas.width,uCanvas.height);
        dlgU.showModal();
      });
      uCancel.onclick = ()=> dlgU.close();
      uPdf.addEventListener('change', onPdfChosen);
      uSend.addEventListener('click', saveToServer);
    })();

    async function fetchAndRender(){
      try{
        const res = await fetch(DATA_URL, {cache:'no-store'});
        data = await res.json();
      }catch(e){ data = []; }
      const years = [...new Set(data.map(d => new Date(d.fecha).getFullYear()))]
        .filter(y=>!isNaN(y)).sort((a,b)=>b-a);
      document.getElementById('year').innerHTML =
        `<option value="">Todos los años</option>` + years.map(y=>`<option>${y}</option>`).join('');
      apply();
    }

    function apply(){
      const q = $q.value.trim().toLowerCase();
      const y = $year.value;
      const s = $sort.value;

      filtered = data.filter(r=>{
        const matchesQ = !q || r.titulo.toLowerCase().includes(q) || (r.tags||[]).some(t=>t.toLowerCase().includes(q));
        const matchesY = !y || new Date(r.fecha).getFullYear().toString() === y;
        return matchesQ && matchesY;
      });

      filtered.sort((a,b)=>{
        if (s==="date-desc") return new Date(b.fecha)-new Date(a.fecha);
        if (s==="date-asc")  return new Date(a.fecha)-new Date(b.fecha);
        if (s==="title-asc") return a.titulo.localeCompare(b.titulo,'es');
        if (s==="title-desc")return b.titulo.localeCompare(a.titulo,'es');
        return 0;
      });

      render();
    }

    // ----- Render con botón Eliminar y enlace que no lo tapa
    function render(){
      $grid.innerHTML = '';
      if (!filtered.length){ $empty.hidden=false; $count.textContent='0 resultados'; return; }
      $empty.hidden=true; $count.textContent = `${filtered.length} resultado${filtered.length>1?'s':''}`;

      for (const r of filtered){
        const card = document.createElement('div');
        card.className = 'card';

        // Botón eliminar (admin)
        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'del';
        del.textContent = 'Eliminar';
        del.addEventListener('click', (ev)=>{
          ev.preventDefault(); ev.stopPropagation();
          onDelete(r);
        });
        card.appendChild(del);

        // Enlace que cubre portada + meta
        const link = document.createElement('a');
        link.className = 'cover';
        link.href = `${VIEWER_URL}?doc=${encodeURIComponent(r.pdf)}&title=${encodeURIComponent(r.titulo)}`;
        link.target = '_blank'; link.rel='noopener';

        const th = document.createElement('div'); th.className='thumb';
        const img = document.createElement('img'); img.loading='lazy'; img.alt=r.titulo; img.src=r.cover;
        th.appendChild(img);

        const meta = document.createElement('div'); meta.className='meta';
        const h3 = document.createElement('div'); h3.className='title'; h3.textContent=r.titulo;
        const sub = document.createElement('div'); sub.className='sub';
        const d = new Date(r.fecha); sub.textContent = d.toLocaleDateString('es-MX',{year:'numeric', month:'short'});
        const tags = document.createElement('div'); tags.className='tags';
        (r.tags||[]).forEach(t=>{ const s=document.createElement('span'); s.className='tag'; s.textContent=t; tags.appendChild(s); });

        meta.appendChild(h3); meta.appendChild(sub); if((r.tags||[]).length) meta.appendChild(tags);
        link.appendChild(th); link.appendChild(meta);
        card.appendChild(link);
        $grid.appendChild(card);
      }
    }

    // ===== Utilidades =====
    function slugify(s){ return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,''); }
    function parseTags(value){ return value.split(',').map(s => s.trim()).filter(Boolean).slice(0, 12); }
    function fitIntoCanvas(srcCanvas, targetW=400, targetH=600, bg='#000'){
      const out = document.createElement('canvas');
      out.width = targetW; out.height = targetH;
      const ctx = out.getContext('2d');
      ctx.fillStyle = bg; ctx.fillRect(0,0,targetW,targetH);
      const scale = Math.min(targetW / srcCanvas.width, targetH / srcCanvas.height);
      const w = Math.round(srcCanvas.width  * scale);
      const h = Math.round(srcCanvas.height * scale);
      const x = Math.floor((targetW - w) / 2);
      const y = Math.floor((targetH - h) / 2);
      ctx.imageSmoothingQuality = 'high';
      ctx.drawImage(srcCanvas, x, y, w, h);
      return out;
    }

    // ===== Subida =====
    async function onPdfChosen(e){
      const file = e.target.files?.[0];
      if (!file) return;
      if (!/\.pdf$/i.test(file.name)){ alert('Selecciona un archivo PDF'); uPdf.value=''; return; }

      if (!uTitle.value) uTitle.value = file.name.replace(/\.pdf$/i,'').replace(/[-_]+/g,' ').trim();
      if (!uMonth.value) uMonth.value = new Date().toISOString().slice(0,7);

      const bytes = await file.arrayBuffer();
      const pdf = await pdfjsLib.getDocument({data:bytes}).promise;
      const page = await pdf.getPage(1);
      const vp = page.getViewport({ scale: 1.6 });
      const tmp = document.createElement('canvas');
      tmp.width = Math.round(vp.width); tmp.height = Math.round(vp.height);
      await page.render({ canvasContext: tmp.getContext('2d'), viewport: vp }).promise;

      const thumb = fitIntoCanvas(tmp, 400, 600, '#000');
      uCanvas.width = thumb.width; uCanvas.height = thumb.height;
      uCanvas.getContext('2d').drawImage(thumb, 0, 0);

      const slug = slugify(uTitle.value || file.name.replace(/\.pdf$/i,''));
      const yyyyMM = uMonth.value || new Date().toISOString().slice(0,7);
      const ficha = {
        id: slug,
        titulo: uTitle.value || file.name,
        fecha: `${yyyyMM}-01`,
        pdf: `assets/revistas/${slug}.pdf`,
        cover: `assets/revistas/portadas/${slug}.jpg`,
        tags: parseTags(uTags.value || '')
      };
      uJson.textContent = JSON.stringify(ficha, null, 2);
    }

    async function saveToServer(){
      const file = uPdf.files?.[0];
      if (!file){ alert('Selecciona un PDF.'); return; }
      if (!uTitle.value || !uMonth.value){ alert('Completa Título y Mes.'); return; }
      if (file.size > MAX_MB * 1024 * 1024){
        alert(`El PDF pesa ${(file.size/1024/1024).toFixed(1)} MB. Máximo permitido: ${MAX_MB} MB.`);
        return;
      }
      const blobThumb = await new Promise(res => uCanvas.toBlob(res, 'image/jpeg', 0.85));
      if (!blobThumb){ alert('No se pudo generar la miniatura'); return; }

      const slug = slugify(uTitle.value || file.name.replace(/\.pdf$/i,''));
      const ficha = {
        id: slug,
        titulo: uTitle.value,
        fecha: `${uMonth.value}-01`,
        pdf: `assets/revistas/${slug}.pdf`,
        cover: `assets/revistas/portadas/${slug}.jpg`,
        tags: parseTags(uTags.value || '')
      };

      const fd = new FormData();
      fd.append('pdf', file, `${slug}.pdf`);
      fd.append('thumb', blobThumb, `${slug}.jpg`);
      fd.append('meta', JSON.stringify(ficha));

      try{
        const resp = await fetch('api/upload.php', {
          method:'POST',
          body: fd,
          headers: { 'X-CSRF': window.CSRF } // <<< CSRF
        });
        const text = await resp.text();
        let result; try { result = JSON.parse(text); } catch { result = null; }
        if (!resp.ok) throw new Error(`HTTP ${resp.status}: ${text}`);
        if (!result || !result.ok) throw new Error(result?.error || text || 'Error servidor');

        data.unshift(result.item);
        apply();
        dlgU.close();
      }catch(err){
        console.error('Upload error:', err);
        alert('No se pudo guardar en el servidor.\n' + err.message + '\nRevisa permisos de carpetas y PHP.');
      }
    }

    // ===== Eliminar =====
    async function onDelete(item){
      const { id, titulo } = item;
      const sure = confirm(`¿Eliminar la revista "${titulo}"?\nSe borrará el PDF, la miniatura y se quitará del listado.`);
      if (!sure) return;

      try{
        const resp = await fetch('api/delete.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF': window.CSRF }, // <<< CSRF
          body: JSON.stringify({ id })
        });
        const text = await resp.text();
        let result; try { result = JSON.parse(text); } catch { result = null; }
        if (!resp.ok) throw new Error(`HTTP ${resp.status}: ${text}`);
        if (!result || !result.ok) throw new Error(result?.error || 'Error servidor');

        data = data.filter(r => r.id !== id);
        apply();
      }catch(err){
        console.error('Delete error:', err);
        alert('No se pudo eliminar.\n' + err.message);
      }
    }
  </script>
</body>
</html>
