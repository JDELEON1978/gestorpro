@extends('layouts.app')

@section('content')

<style>
  /* ============================================================
   * UI: Look sutil por tipo de nodo (profesional)
   * ============================================================ */
  .node-card{
    background:#fff;
    border:1px solid rgba(15,23,42,.14);
    border-radius:12px;
    box-shadow: 0 6px 18px rgba(15,23,42,.08);
  }
  .node-card[data-tipo="inicio"]    { border-left: 6px solid rgba(13,110,253,.55); }
  .node-card[data-tipo="actividad"] { border-left: 6px solid rgba(25,135,84,.45); }
  .node-card[data-tipo="decision"]  { border-left: 6px solid rgba(255,193,7,.55); }
  .node-card[data-tipo="fin"]       { border-left: 6px solid rgba(220,53,69,.45); }
  .node-card[data-tipo="conector"]  { border-left: 6px solid rgba(108,117,125,.45); }

  .port{
    width:14px;height:14px;border-radius:50%;
    box-shadow: 0 2px 6px rgba(15,23,42,.18);
    cursor: crosshair;
    user-select: none;
  }
  .port.in{ background:#0d6efd; }
  .port.out{ background:#198754; }

  .node-edit{ border:1px solid rgba(15,23,42,.12); }
</style>

<div class="container-fluid">

  <div class="d-flex align-items-center justify-content-between mb-2">
    <div class="d-flex gap-2 align-items-center">
      <h4 class="m-0">Builder de Procesos</h4>

      <select class="form-select form-select-sm" style="width: 320px"
              onchange="if(this.value){ window.location='{{ url('/process-builder') }}/'+this.value }">
        @foreach($procesos as $p)
          <option value="{{ $p->id }}" @selected($proceso && $proceso->id==$p->id)>
            {{ $p->nombre }} {{ $p->version ? 'v'.$p->version : '' }}
          </option>
        @endforeach
      </select>

      <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalProcesoCreate">
        + Crear Proceso
      </button>

      @if($proceso)
      <button class="btn btn-sm btn-outline-primary"
              data-bs-toggle="modal" data-bs-target="#modalProcesoEdit"
              data-id="{{ $proceso->id }}"
              data-nombre="{{ $proceso->nombre }}"
              data-codigo="{{ $proceso->codigo }}"
              data-version="{{ $proceso->version }}"
              data-estado="{{ $proceso->estado }}"
              data-descripcion="{{ $proceso->descripcion }}">
        Editar Proceso
      </button>
      @endif
    </div>
  </div>

  <div class="row g-0">
    {{-- Canvas --}}
    <div class="col-9 pe-3">
      <div class="border bg-white" style="min-height: 78vh; position: relative; overflow:hidden;">
        <div class="p-2 d-flex justify-content-between align-items-center">
          <div>
            @if($proceso)
              <button class="btn btn-sm btn-link"
                      data-bs-toggle="modal" data-bs-target="#modalNodoCreate">
                + Añadir NODO ..
              </button>
            @endif
          </div>

          <div class="small text-muted">
            Tip: click en <span class="badge bg-success">verde</span> (salida) y luego en <span class="badge bg-primary">azul</span> (entrada) para crear relación.
            <span class="ms-2">Extra: arrastra los puntos (ports) para despejar cruces.</span>
          </div>
        </div>

        <div id="builderCanvas"
             data-proceso-id="{{ $proceso?->id }}"
             style="
               position:relative;
               height: calc(78vh - 44px);
               background:
                 radial-gradient(circle, rgba(15,23,42,.12) 1px, transparent 1px) 0 0 / 18px 18px;
             ">

          {{-- SVG para flechas --}}
          <svg id="linkLayer" style="position:absolute; inset:0; width:100%; height:100%; pointer-events:none;">
            <defs>
              <marker id="arrow" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto" markerUnits="strokeWidth">
                <path d="M0,0 L10,3 L0,6 Z"></path>
              </marker>
            </defs>
          </svg>

          {{-- NODOS --}}
          @foreach($nodos as $idx => $n)
            @php
              $x = $n->pos_x ?? 120;
              $y = $n->pos_y ?? (120 + ($idx * 110));
            @endphp

            <div class="node-card"
                 data-id="{{ $n->id }}"
                 data-tipo="{{ $n->tipo_nodo }}"
                 style="
                    position:absolute;
                    left: {{ $x }}px;
                    top:  {{ $y }}px;
                    width: 260px;
                    cursor: grab;
                    padding: 12px 12px 10px 12px;
                 ">

              {{-- PORT IN base (1) --}}
              <span class="port in"
                    title="Entrada"
                    style="position:absolute; left:-7px; top: 22px;"></span>

              {{-- PORT OUT base (1) - JS lo reemplaza si es decision --}}
              <span class="port out"
                    title="Salida"
                    style="position:absolute; right:-7px; top: 22px;"></span>

              {{-- ICONO EDITAR --}}
              <button type="button"
                      class="btn btn-sm btn-light node-edit"
                      style="position:absolute; right:8px; top:8px; padding:2px 6px;"
                      data-bs-toggle="modal" data-bs-target="#modalNodoEdit"
                      data-id="{{ $n->id }}"
                      data-nombre="{{ $n->nombre }}"
                      data-tipo="{{ $n->tipo_nodo }}"
                      data-orden="{{ $n->orden }}"
                      data-sla="{{ $n->sla_horas }}"
                      data-activo="{{ $n->activo ? 1 : 0 }}"
                      data-responsable_rol_id="{{ $n->responsable_rol_id }}"
                      data-descripcion="{{ e($n->descripcion) }}">
                <i class="bi bi-pencil"></i>
              </button>

              <div class="fw-semibold">{{ $n->nombre }}</div>
              <div class="text-muted small">{{ $n->tipo_nodo }} · orden {{ $n->orden }}</div>
            </div>
          @endforeach

        </div>
      </div>
    </div>

    {{-- Sidebar Items --}}
    <div class="col-3">
      <div class="border bg-white" style="min-height: 78vh;">
        <div class="p-3 border-bottom">
          <div class="d-flex justify-content-between align-items-center">
            <div class="fw-bold">Items</div>
            @if($proceso)
            <button class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal" data-bs-target="#modalItemCreate">
              + Añadir un item
            </button>
            @endif
          </div>
        </div>

        <div class="p-3">
          @foreach(['DOCUMENTO'=>'Documentos','FORMULARIO'=>'Formularios','OPERACION'=>'Operación'] as $cat=>$label)
            <div class="mb-3">
              <div class="fw-bold">{{ $label }}</div>
              <div class="small">
                @foreach(($itemsByCategoria[$cat] ?? collect()) as $it)
                  <div class="d-flex justify-content-between align-items-start py-1">
                    <div>
                      {{ $it->nombre }}
                      @if($it->requiere_evidencia)
                        <span class="badge bg-secondary">scan</span>
                      @endif
                    </div>
                    <button class="btn btn-sm btn-link"
                            data-bs-toggle="modal" data-bs-target="#modalItemEdit"
                            data-id="{{ $it->id }}"
                            data-nombre="{{ $it->nombre }}"
                            data-categoria="{{ $it->categoria }}"
                            data-evidencia="{{ $it->requiere_evidencia ? 1 : 0 }}"
                            data-activo="{{ $it->activo ? 1 : 0 }}">
                      Editar
                    </button>
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>

      </div>
    </div>
  </div>
</div>

@include('process_builder.modals_proceso')
@include('process_builder.modals_nodo')
@include('process_builder.modals_item')

@endsection

@push('scripts')
<script>
/**
 * ============================================================
 * Modales: set action + rellenar campos
 * ============================================================
 */
(function(){
  const setAction = (form, url) => form.setAttribute('action', url);

  // Proceso edit
  const mProceso = document.getElementById('modalProcesoEdit');
  if(mProceso){
    mProceso.addEventListener('show.bs.modal', (ev)=>{
      const b = ev.relatedTarget;
      const form = mProceso.querySelector('form');
      setAction(form, `{{ url('/process-builder/proceso') }}/${b.dataset.id}`);

      form.querySelector('[name=nombre]').value = b.dataset.nombre || '';
      form.querySelector('[name=codigo]').value = b.dataset.codigo || '';
      form.querySelector('[name=version]').value = b.dataset.version || '';
      form.querySelector('[name=estado]').value = b.dataset.estado || '';
      form.querySelector('[name=descripcion]').value = b.dataset.descripcion || '';
    });
  }

  // Nodo edit
  const mNodo = document.getElementById('modalNodoEdit');
  if(mNodo){
    mNodo.addEventListener('show.bs.modal', (ev)=>{
      const b = ev.relatedTarget;
      const form = mNodo.querySelector('form');
      setAction(form, `{{ url('/process-builder/nodo') }}/${b.dataset.id}`);

      form.querySelector('[name=nombre]').value = b.dataset.nombre || '';
      form.querySelector('[name=tipo_nodo]').value = b.dataset.tipo || 'actividad';
      form.querySelector('[name=orden]').value = b.dataset.orden || '';
      form.querySelector('[name=sla_horas]').value = b.dataset.sla || '';
      form.querySelector('[name=activo]').checked = (b.dataset.activo == '1');
      form.querySelector('[name=responsable_rol_id]').value = b.dataset.responsable_rol_id || '';
      form.querySelector('[name=descripcion]').value = b.dataset.descripcion || '';
    });
  }

  // Item edit
  const mItem = document.getElementById('modalItemEdit');
  if(mItem){
    mItem.addEventListener('show.bs.modal', (ev)=>{
      const b = ev.relatedTarget;
      const form = mItem.querySelector('form');
      setAction(form, `{{ url('/process-builder/item') }}/${b.dataset.id}`);

      form.querySelector('[name=nombre]').value = b.dataset.nombre || '';
      form.querySelector('[name=categoria]').value = b.dataset.categoria || 'DOCUMENTO';
      form.querySelector('[name=requiere_evidencia]').checked = (b.dataset.evidencia == '1');
      form.querySelector('[name=activo]').checked = (b.dataset.activo == '1');
    });
  }
})();
</script>

<script>
/**
 * ============================================================
 * Modal nodo: transiciones/relaciones (decision)
 * - Siempre sincroniza relaciones ANTES de submit
 * ============================================================
 */
(function(){
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  function esc(s){
    return (s ?? '').toString().replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  const nodosOptionsHtml = `{!! collect($nodos ?? collect())->map(fn($n)=>'<option value="'.$n->id.'">'.e($n->nombre).'</option>')->join('') !!}`;

  const mNodo = document.getElementById('modalNodoEdit');
  if(!mNodo) return;

  const form = document.getElementById('formNodoEdit');
  const decisionRows = document.getElementById('decisionRows');
  const btnAddSalida = document.getElementById('btnAddSalida');

  function rowTpl(rel){
    const id = rel?.id ?? '';
    const condicion = esc(rel?.condicion ?? '');
    const prioridad = rel?.prioridad ?? 1;

    return `
      <div class="d-flex gap-2 align-items-center decision-row" data-id="${id}">
        <input class="form-control form-control-sm"
               placeholder="Etiqueta (ej: Importación / Local)"
               value="${condicion}" data-k="condicion">

        <select class="form-select form-select-sm" data-k="nodo_destino_id" style="width:320px">
          <option value="">-- Destino --</option>
          ${nodosOptionsHtml}
        </select>

        <input class="form-control form-control-sm" style="width:90px"
               type="number" min="1" value="${prioridad}" data-k="prioridad">

        <button type="button" class="btn btn-sm btn-outline-danger btnDelSalida">X</button>
      </div>
    `;
  }

  function readRows(){
    const out = [];
    decisionRows.querySelectorAll('.decision-row').forEach(r=>{
      const id = r.dataset.id ? parseInt(r.dataset.id,10) : null;
      const condicion = r.querySelector('[data-k=condicion]').value.trim();
      const destVal = r.querySelector('[data-k=nodo_destino_id]').value;
      const nodo_destino_id = destVal ? parseInt(destVal,10) : null;
      const prioridad = parseInt(r.querySelector('[data-k=prioridad]').value,10) || 1;

      if(!condicion || !nodo_destino_id) return;
      out.push({ id, condicion, nodo_destino_id, prioridad });
    });
    return out;
  }

  btnAddSalida?.addEventListener('click', ()=>{
    decisionRows.insertAdjacentHTML('beforeend', rowTpl({}));
  });

  decisionRows?.addEventListener('click', (e)=>{
    if(e.target.classList.contains('btnDelSalida')){
      e.target.closest('.decision-row')?.remove();
    }
  });

  mNodo.addEventListener('show.bs.modal', async (ev)=>{
    const b = ev.relatedTarget;
    const nodoId = b.dataset.id;

    decisionRows.innerHTML = '';

    try{
      const res = await fetch(`/process-builder/nodo/${nodoId}/relaciones`, {
        headers:{'Accept':'application/json'}
      });
      const json = await res.json();
      const rels = json.relaciones || [];

      if(rels.length === 0){
        const d1 = { condicion:'Carta de crédito de importación', prioridad:1 };
        const d2 = { condicion:'Carta de crédito local', prioridad:2 };
        decisionRows.insertAdjacentHTML('beforeend', rowTpl(d1));
        decisionRows.insertAdjacentHTML('beforeend', rowTpl(d2));
      }else{
        rels.forEach(rel=>{
          decisionRows.insertAdjacentHTML('beforeend', rowTpl(rel));
          const last = decisionRows.lastElementChild;
          last.querySelector('[data-k=nodo_destino_id]').value = rel.nodo_destino_id;
        });
      }
    }catch(e){
      console.error('No se pudieron cargar relaciones', e);
    }
  });

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();

    const action = form.getAttribute('action') || '';
    const nodoId = action.split('/').pop();
    const relaciones = readRows(); // puede ser []

    try{
      const res = await fetch(`/process-builder/nodo/${nodoId}/relaciones`, {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': CSRF,
          'Accept':'application/json',
        },
        body: JSON.stringify({ relaciones: relaciones })
      });

      if(!res.ok){
        const t = await res.text();
        console.error('Error guardando relaciones:', t);
        alert('No se pudieron guardar las transiciones. Revisa consola.');
        return;
      }

      form.submit();
    }catch(err){
      console.error(err);
      alert('Error de red guardando transiciones. Revisa consola.');
    }
  });

})();
</script>

<script>
/**
 * ============================================================
 * Canvas:
 * - Drag de nodos (persistencia en BD ya existente)
 * - Crear relación: click out -> click in
 * - Puertos múltiples para decision
 * - Dibujo de links por el puerto real
 *
 * ✅ ADICIÓN #8:
 * - Permite MOVER puertos (verdes y azul) dentro del rectángulo del nodo
 * - Persiste posiciones en localStorage para evitar que se bloqueen uniones
 *
 * ✅ ADICIÓN #9 (NUEVO en este mensaje):
 * - Permite “rutar” la línea manualmente moviendo sus puntos de control (Bezier)
 * - Dibuja 2 handles (c1 y c2) por relación (relacionId)
 * - Persistencia en localStorage por proceso + relación
 * ============================================================
 */
(function () {
  const canvas = document.getElementById('builderCanvas');
  if (!canvas) return;

  const procesoId = canvas.dataset.procesoId;
  const svg = document.getElementById('linkLayer');
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  const state = {
    links: [],
    nodes: new Map(),
    dragging: null,
    dragOffsetX: 0,
    dragOffsetY: 0,
  };

  // ============================================================
  // Registro nodos
  // ============================================================
  document.querySelectorAll('.node-card').forEach(el => {
    state.nodes.set(String(el.dataset.id), el);
    el.addEventListener('dragstart', (e)=> e.preventDefault());
  });

  // ============================================================
  // Drag de nodo (mueve el rectángulo)
  // ============================================================
  document.querySelectorAll('.node-card').forEach(el => {
    el.addEventListener('mousedown', (e) => {
      if (e.target.closest('.port') || e.target.closest('.node-edit')) return;

      state.dragging = el;
      el.style.cursor = 'grabbing';

      const rect = el.getBoundingClientRect();
      state.dragOffsetX = e.clientX - rect.left;
      state.dragOffsetY = e.clientY - rect.top;

      e.preventDefault();
    });
  });

  document.addEventListener('mousemove', (e) => {
    if (!state.dragging) return;

    const cRect = canvas.getBoundingClientRect();
    let x = e.clientX - cRect.left - state.dragOffsetX;
    let y = e.clientY - cRect.top - state.dragOffsetY;

    x = Math.max(0, x);
    y = Math.max(0, y);

    state.dragging.style.left = `${x}px`;
    state.dragging.style.top  = `${y}px`;

    drawLinks();
  });

  document.addEventListener('mouseup', async () => {
    if (!state.dragging) return;

    const el = state.dragging;
    el.style.cursor = 'grab';

    const x = parseInt(el.style.left, 10) || 0;
    const y = parseInt(el.style.top, 10) || 0;
    const id = el.dataset.id;

    state.dragging = null;

    try {
      await fetch(`/process-builder/nodo/${id}/position`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ pos_x: x, pos_y: y })
      });
    } catch (err) {
      console.error('No se pudo guardar posición', err);
    }
  });

  // ============================================================
  // Crear relación: click verde (out) -> click azul (in)
  // ============================================================
  const linkMode = { from: null };

  canvas.addEventListener('click', async (e)=>{
    const out = e.target.closest('.port.out');
    if(out){
      e.stopPropagation();
      const nodeEl = out.closest('.node-card');
      linkMode.from = nodeEl.dataset.id;

      document.querySelectorAll('.node-card').forEach(n => n.style.outline = '');
      nodeEl.style.outline = '2px solid rgba(25,135,84,.35)';
      return;
    }

    const inn = e.target.closest('.port.in');
    if(inn){
      e.stopPropagation();
      const toNodeEl = inn.closest('.node-card');
      const toId = toNodeEl.dataset.id;

      if (!linkMode.from) return;
      if (linkMode.from === toId) return;

      try {
        const res = await fetch(`/process-builder/${procesoId}/relacion`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            nodo_origen_id: parseInt(linkMode.from, 10),
            nodo_destino_id: parseInt(toId, 10),
            condicion: null,
            prioridad: 1
          })
        });

        if (!res.ok) return;

        // Si tu backend devuelve {relacion: {...}}, loadGraph lo refresca todo bien
        await loadGraph();
      } catch (err) {
        console.error(err);
      } finally {
        linkMode.from = null;
        document.querySelectorAll('.node-card').forEach(n => n.style.outline = '');
      }
    }
  });

  // ============================================================
  // Puertos múltiples solo en "decision"
  // ============================================================
  function ensureDecisionPorts(){
    state.nodes.forEach((nodeEl, nodeId)=>{
      const tipo = nodeEl.dataset.tipo;

      // Limpia puertos out generados (con relId)
      nodeEl.querySelectorAll('.port.out[data-rel-id]').forEach(p => p.remove());

      if(tipo !== 'decision') return;

      // Para decision: quitamos el out base y lo reemplazamos por N outs
      nodeEl.querySelectorAll('.port.out').forEach(p => p.remove());

      const rels = state.links
        .filter(l => String(l.nodo_origen_id) === String(nodeId))
        .sort((a,b)=> (a.prioridad||1) - (b.prioridad||1));

      const baseTop = 22;
      const step = 22;

      if(rels.length === 0){
        const port = document.createElement('span');
        port.className = 'port out';
        port.title = 'Salida';
        port.style.cssText = `position:absolute; right:-7px; top:${baseTop}px;`;
        nodeEl.appendChild(port);
        return;
      }

      rels.forEach((l, idx)=>{
        const port = document.createElement('span');
        port.className = 'port out';
        port.title = l.condicion ? l.condicion : 'Salida';
        port.dataset.relId = l.id ?? '';
        port.style.cssText = `position:absolute; right:-7px; top:${baseTop + (idx*step)}px;`;
        nodeEl.appendChild(port);
      });
    });

    // ✅ ADICIÓN #8: luego de recrear puertos, re-aplicamos posiciones guardadas
    applySavedPortPositionsAll();
  }

  // ============================================================
  // ✅ ADICIÓN #8: Drag de ports + persistencia localStorage
  // ============================================================
  const portDrag = {
    active: false,
    portEl: null,
    nodeEl: null,
    startX: 0,
    startY: 0,
    startLeft: 0,
    startTop: 0,
  };

  function clamp(v, min, max){ return Math.max(min, Math.min(max, v)); }

  function portKey(portEl){
    if(portEl.classList.contains('in')) return 'in';
    const relId = portEl.dataset.relId;
    if(relId) return `out:${relId}`;
    return 'out:base';
  }

  function storageKey(nodeId, pKey){
    return `gp:pb:${procesoId}:node:${nodeId}:port:${pKey}`;
  }

  function getPortPos(nodeId, pKey){
    try{
      const raw = localStorage.getItem(storageKey(nodeId, pKey));
      return raw ? JSON.parse(raw) : null;
    }catch(e){
      return null;
    }
  }

  function setPortPos(nodeId, pKey, pos){
    try{
      localStorage.setItem(storageKey(nodeId, pKey), JSON.stringify(pos));
    }catch(e){}
  }

  function setPortAbsolute(portEl, leftPx, topPx){
    portEl.style.right = 'auto';
    portEl.style.bottom = 'auto';
    portEl.style.left = `${leftPx}px`;
    portEl.style.top  = `${topPx}px`;
  }

  function applySavedPortPositionsAll(){
    state.nodes.forEach((nodeEl, nodeId)=>{
      nodeEl.querySelectorAll('.port').forEach(portEl=>{
        const pKey = portKey(portEl);
        const pos = getPortPos(nodeId, pKey);
        if(!pos) return;
        setPortAbsolute(portEl, pos.left, pos.top);
      });
    });
    drawLinks();
  }

  // Delegación: mousedown en cualquier port (in/out)
  canvas.addEventListener('mousedown', (e)=>{
    const portEl = e.target.closest('.port');
    if(!portEl) return;

    const nodeEl = portEl.closest('.node-card');
    if(!nodeEl) return;

    // Evita drag de nodo
    e.stopPropagation();

    portDrag.active = true;
    portDrag.portEl = portEl;
    portDrag.nodeEl = nodeEl;

    portDrag.startX = e.clientX;
    portDrag.startY = e.clientY;

    const currentLeft = portEl.offsetLeft;
    const currentTop  = portEl.offsetTop;

    setPortAbsolute(portEl, currentLeft, currentTop);

    portDrag.startLeft = currentLeft;
    portDrag.startTop  = currentTop;

    document.body.style.cursor = 'grabbing';
    e.preventDefault();
  });

  document.addEventListener('mousemove', (e)=>{
    if(!portDrag.active) return;

    const portEl = portDrag.portEl;
    const nodeEl = portDrag.nodeEl;

    const dx = e.clientX - portDrag.startX;
    const dy = e.clientY - portDrag.startY;

    const nodeW = nodeEl.clientWidth;
    const nodeH = nodeEl.clientHeight;

    // Port 14px: dejamos que “salga” 7px para centrarlo en el borde
    const minX = -7;
    const minY = -7;
    const maxX = nodeW - 7;
    const maxY = nodeH - 7;

    const newLeft = clamp(portDrag.startLeft + dx, minX, maxX);
    const newTop  = clamp(portDrag.startTop  + dy, minY, maxY);

    setPortAbsolute(portEl, newLeft, newTop);
    drawLinks();
  });

  document.addEventListener('mouseup', ()=>{
    if(!portDrag.active) return;

    const portEl = portDrag.portEl;
    const nodeEl = portDrag.nodeEl;

    const nodeId = nodeEl.dataset.id;
    const pKey = portKey(portEl);

    // Guardar posición (relativa al nodo)
    setPortPos(nodeId, pKey, {
      left: portEl.offsetLeft,
      top:  portEl.offsetTop
    });

    portDrag.active = false;
    portDrag.portEl = null;
    portDrag.nodeEl = null;
    document.body.style.cursor = '';
  });

  // ============================================================
  // Punto exacto de conexión: centro del puerto REAL
  // (sirve para ports movibles)
  // ============================================================
  function portPointByEl(nodeEl, portSelector) {
    const nodeRect = nodeEl.getBoundingClientRect();
    const canvasRect = canvas.getBoundingClientRect();
    const portEl = nodeEl.querySelector(portSelector);

    const offL = portEl ? portEl.offsetLeft : 0;
    const offT = portEl ? portEl.offsetTop  : 22;

    const x = (nodeRect.left - canvasRect.left) + offL + 7;
    const y = (nodeRect.top  - canvasRect.top ) + offT + 7;

    return { x, y };
  }

  // ============================================================
  // ✅ ADICIÓN #9: “Ruteo” manual de líneas (Bezier control points)
  // ============================================================
  const linkDrag = {
    active: false,
    relId: null,
    which: null, // 'c1' o 'c2'
    startX: 0,
    startY: 0,
    startCx: 0,
    startCy: 0,
  };

  function linkStorageKey(relId){
    // relId es el id de nodo_relaciones (o lo que devuelva tu graph)
    return `gp:pb:${procesoId}:rel:${relId}:bezier`;
  }

  function getBezier(relId){
    if(!relId) return null;
    try{
      const raw = localStorage.getItem(linkStorageKey(relId));
      return raw ? JSON.parse(raw) : null;
    }catch(e){
      return null;
    }
  }

  function setBezier(relId, bez){
    if(!relId) return;
    try{
      localStorage.setItem(linkStorageKey(relId), JSON.stringify(bez));
    }catch(e){}
  }

  /**
   * Calcula puntos Bezier por defecto si no hay guardados.
   * Usa una curva “suave” basada en dx, pero que luego tú editas moviendo handles.
   */
  function defaultBezier(p1, p2){
    const dx = Math.max(80, Math.abs(p2.x - p1.x) * 0.5);
    return {
      c1x: p1.x + dx, c1y: p1.y,
      c2x: p2.x - dx, c2y: p2.y
    };
  }

  /**
   * Crea/actualiza un handle (circle) en el SVG:
   * - pointer-events habilitado para arrastrar
   * - data-rel y data-which para saber qué estás moviendo
   */
  function upsertHandle(relId, which, x, y){
    // class: handle handle-c1 / handle-c2
    const cls = `handle handle-${which}`;
    let el = svg.querySelector(`circle.${cls.replace(' ', '.') }[data-rel-id="${relId}"]`);
    if(!el){
      el = document.createElementNS('http://www.w3.org/2000/svg','circle');
      el.classList.add('handle', `handle-${which}`);
      el.setAttribute('r', '6');
      el.setAttribute('data-rel-id', relId);
      el.setAttribute('data-which', which);

      // styling sutil (no grita)
      el.setAttribute('fill', which === 'c1' ? 'rgba(13,110,253,.25)' : 'rgba(25,135,84,.25)');
      el.setAttribute('stroke', 'rgba(15,23,42,.35)');
      el.setAttribute('stroke-width', '1');

      // IMPORTANTE: el SVG padre tiene pointer-events:none en tu HTML.
      // Por eso al handle le activamos eventos explícitamente:
      el.style.pointerEvents = 'all';
      el.style.cursor = 'move';

      svg.appendChild(el);
    }
    el.setAttribute('cx', x);
    el.setAttribute('cy', y);
  }

  // Eventos de drag sobre handles (delegación en el SVG)
  svg.addEventListener('mousedown', (e)=>{
    const h = e.target.closest('circle.handle');
    if(!h) return;

    e.preventDefault();
    e.stopPropagation();

    const relId = h.getAttribute('data-rel-id');
    const which = h.getAttribute('data-which');

    // Lee lo guardado o usa lo que ya está en el DOM
    const cx = parseFloat(h.getAttribute('cx')) || 0;
    const cy = parseFloat(h.getAttribute('cy')) || 0;

    linkDrag.active = true;
    linkDrag.relId = relId;
    linkDrag.which = which;
    linkDrag.startX = e.clientX;
    linkDrag.startY = e.clientY;
    linkDrag.startCx = cx;
    linkDrag.startCy = cy;

    document.body.style.cursor = 'grabbing';
  });

  document.addEventListener('mousemove', (e)=>{
    if(!linkDrag.active) return;

    const dx = e.clientX - linkDrag.startX;
    const dy = e.clientY - linkDrag.startY;

    const newCx = linkDrag.startCx + dx;
    const newCy = linkDrag.startCy + dy;

    // Actualiza el handle en el SVG
    upsertHandle(linkDrag.relId, linkDrag.which, newCx, newCy);

    // Actualiza storage (solo el punto movido)
    const bez = getBezier(linkDrag.relId) || {};
    if(linkDrag.which === 'c1'){
      bez.c1x = newCx; bez.c1y = newCy;
    }else{
      bez.c2x = newCx; bez.c2y = newCy;
    }
    setBezier(linkDrag.relId, bez);

    // Redibuja paths usando los puntos nuevos
    drawLinks();
  });

  document.addEventListener('mouseup', ()=>{
    if(!linkDrag.active) return;
    linkDrag.active = false;
    linkDrag.relId = null;
    linkDrag.which = null;
    document.body.style.cursor = '';
  });

  // ============================================================
  // Dibujo de flechas (con Bezier editable + handles)
  // ============================================================
  function drawLinks() {
    if (!svg) return;

    // Limpieza: paths + labels + handles (los reconstruimos por estado actual)
    [...svg.querySelectorAll('path.link')].forEach(p => p.remove());
    [...svg.querySelectorAll('text.linklabel')].forEach(t => t.remove());
    [...svg.querySelectorAll('circle.handle')].forEach(h => h.remove());

    state.links.forEach(l => {
      const from = state.nodes.get(String(l.nodo_origen_id));
      const to   = state.nodes.get(String(l.nodo_destino_id));
      if (!from || !to) return;

      const fromTipo = from.dataset.tipo;
      let fromSelector = '.port.out';

      // Si es decision y tenemos rel-id, apuntamos al puerto específico
      if(fromTipo === 'decision' && l.id){
        fromSelector = `.port.out[data-rel-id="${l.id}"]`;
        if(!from.querySelector(fromSelector)){
          fromSelector = '.port.out';
        }
      }

      const p1 = portPointByEl(from, fromSelector);
      const p2 = portPointByEl(to, '.port.in');

      // ✅ ADICIÓN #9: usar control points guardados por relación
      const relId = l.id ? String(l.id) : null; // idealmente siempre existe
      const saved = relId ? getBezier(relId) : null;
      const base  = defaultBezier(p1, p2);

      const c1x = (saved?.c1x ?? base.c1x);
      const c1y = (saved?.c1y ?? base.c1y);
      const c2x = (saved?.c2x ?? base.c2x);
      const c2y = (saved?.c2y ?? base.c2y);

      const d = `M ${p1.x} ${p1.y} C ${c1x} ${c1y}, ${c2x} ${c2y}, ${p2.x} ${p2.y}`;

      const path = document.createElementNS('http://www.w3.org/2000/svg','path');
      path.setAttribute('d', d);
      path.setAttribute('fill', 'none');
      path.setAttribute('stroke', 'rgba(15,23,42,.55)');
      path.setAttribute('stroke-width', '2');
      path.setAttribute('marker-end', 'url(#arrow)');
      path.classList.add('link');
      svg.appendChild(path);

      // Label (si existe)
      if (l.condicion) {
        const text = document.createElementNS('http://www.w3.org/2000/svg','text');
        text.textContent = l.condicion;
        text.setAttribute('x', (p1.x + p2.x) / 2);
        text.setAttribute('y', (p1.y + p2.y) / 2 - 6);
        text.setAttribute('fill', 'rgba(15,23,42,.75)');
        text.setAttribute('font-size', '12');
        text.classList.add('linklabel');
        svg.appendChild(text);
      }

      // ✅ ADICIÓN #9: dibujar handles solo si hay relId (si no, no hay dónde guardar)
      if(relId){
        upsertHandle(relId, 'c1', c1x, c1y);
        upsertHandle(relId, 'c2', c2x, c2y);
      }
    });
  }

  // ============================================================
  // Carga grafo
  // ============================================================
  async function loadGraph() {
    if (!procesoId) return;

    try {
      const res = await fetch(`/process-builder/${procesoId}/graph`, {
        headers: { 'Accept': 'application/json' }
      });
      if (!res.ok) return;

      const json = await res.json();
      state.links = json.relaciones || [];

      ensureDecisionPorts();
      drawLinks();
    } catch (e) {
      console.error('No se pudo cargar el grafo', e);
    }
  }

  window.addEventListener('resize', ()=>{ ensureDecisionPorts(); drawLinks(); });

  // ✅ ADICIÓN #8: aplicar posiciones guardadas apenas cargue UI
  applySavedPortPositionsAll();

  loadGraph();
})();
</script>
@endpush