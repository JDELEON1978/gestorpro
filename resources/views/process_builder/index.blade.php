@extends('layouts.app')

@section('content')

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
          </div>
        </div>

        <div id="builderCanvas"
             data-proceso-id="{{ $proceso?->id }}"
             style="
               position:relative;
               height: calc(78vh - 44px);
               background:
                 radial-gradient(circle, rgba(15,23,42,.15) 1px, transparent 1px) 0 0 / 18px 18px;
             ">

          {{-- SVG para flechas --}}
          <svg id="linkLayer" style="position:absolute; inset:0; width:100%; height:100%; pointer-events:none;">
            <defs>
              <marker id="arrow" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto" markerUnits="strokeWidth">
                <path d="M0,0 L10,3 L0,6 Z"></path>
              </marker>
            </defs>
          </svg>

          {{-- NODOS movibles (IMPORTANTE: ya NO abren modal al click) --}}
          @foreach($nodos as $idx => $n)
            @php
              $x = $n->pos_x ?? 120;
              $y = $n->pos_y ?? (120 + ($idx * 110));
            @endphp

            <div class="node-card"
                 data-id="{{ $n->id }}"
                 style="
                    position:absolute;
                    left: {{ $x }}px;
                    top:  {{ $y }}px;
                    width: 260px;
                    cursor: grab;
                    background: #fff;
                    border: 1px solid rgba(15,23,42,.15);
                    border-radius: 10px;
                    padding: 12px 12px 10px 12px;
                    box-shadow: 0 6px 18px rgba(15,23,42,.08);
                 ">

              {{-- PORTS --}}
              <span class="port in"
                    title="Entrada"
                    style="position:absolute; left:-7px; top: 18px; width:14px; height:14px; border-radius:50%; background:#0d6efd;"></span>

              <span class="port out"
                    title="Salida"
                    style="position:absolute; right:-7px; top: 18px; width:14px; height:14px; border-radius:50%; background:#198754;"></span>

              {{-- ICONO EDITAR (solo con este abre modal) --}}
              <button type="button"
                      class="btn btn-sm btn-light node-edit"
                      style="position:absolute; right:8px; top:8px; padding:2px 6px;"
                      data-bs-toggle="modal" data-bs-target="#modalNodoEdit"
                      data-id="{{ $n->id }}"
                      data-nombre="{{ $n->nombre }}"
                      data-tipo="{{ $n->tipo_nodo }}"
                      data-orden="{{ $n->orden }}"
                      data-sla="{{ $n->sla_horas }}"
                      data-activo="{{ $n->activo ? 1 : 0 }}">
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
                            data-activo="{{ $it->activo ? 1 : 0 }}"
                    >Editar</button>
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

  // Nodo edit (se abre solo desde el ícono)
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
(function () {
  const canvas = document.getElementById('builderCanvas');
  if (!canvas) return;

  const procesoId = canvas.dataset.procesoId;
  const svg = document.getElementById('linkLayer');
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  const state = {
    links: [],
    nodes: new Map(), // id -> el
    dragging: null,
    dragOffsetX: 0,
    dragOffsetY: 0,
    moved: false,
  };

  // --- registrar nodos + drag ---
  document.querySelectorAll('.node-card').forEach(el => {
    state.nodes.set(String(el.dataset.id), el);

    el.addEventListener('mousedown', (e) => {
      // NO arrastrar si se clickeó un port o el botón de editar
      if (e.target.closest('.port') || e.target.closest('.node-edit')) return;

      state.dragging = el;
      state.moved = false;
      el.style.cursor = 'grabbing';

      const rect = el.getBoundingClientRect();
      state.dragOffsetX = e.clientX - rect.left;
      state.dragOffsetY = e.clientY - rect.top;

      e.preventDefault();
    });

    // Evitar selecciones raras
    el.addEventListener('dragstart', (e)=> e.preventDefault());
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

    state.moved = true;
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

    // Guardar posición
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

  // --- relación por puertos (click verde -> click azul) ---
  const linkMode = { from: null };

  document.querySelectorAll('.node-card .port.out').forEach(port => {
    port.style.cursor = 'crosshair';
    port.addEventListener('click', (e) => {
      e.stopPropagation();
      const nodeEl = e.target.closest('.node-card');
      linkMode.from = nodeEl.dataset.id;

      // feedback
      document.querySelectorAll('.node-card').forEach(n => n.style.outline = '');
      nodeEl.style.outline = '2px solid rgba(25,135,84,.6)';
    });
  });

  document.querySelectorAll('.node-card .port.in').forEach(port => {
    port.style.cursor = 'crosshair';
    port.addEventListener('click', async (e) => {
      e.stopPropagation();
      const toNodeEl = e.target.closest('.node-card');
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

        if (!res.ok) {
          console.error('No se pudo crear relación');
          return;
        }

        const json = await res.json();
        state.links.push(json.relacion);
        drawLinks();

      } catch (err) {
        console.error(err);
      } finally {
        linkMode.from = null;
        document.querySelectorAll('.node-card').forEach(n => n.style.outline = '');
      }
    });
  });

  // --- dibujo de flechas ---
  function portPoint(nodeEl, kind) {
    const nodeRect = nodeEl.getBoundingClientRect();
    const canvasRect = canvas.getBoundingClientRect();

    const x = (kind === 'out')
      ? (nodeRect.right - canvasRect.left)
      : (nodeRect.left - canvasRect.left);

    const y = (nodeRect.top - canvasRect.top) + 18 + 7;
    return { x, y };
  }

  function drawLinks() {
    if (!svg) return;

    [...svg.querySelectorAll('path.link')].forEach(p => p.remove());
    [...svg.querySelectorAll('text.linklabel')].forEach(t => t.remove());

    state.links.forEach(l => {
      const from = state.nodes.get(String(l.nodo_origen_id));
      const to   = state.nodes.get(String(l.nodo_destino_id));
      if (!from || !to) return;

      const p1 = portPoint(from, 'out');
      const p2 = portPoint(to, 'in');

      const dx = Math.max(80, Math.abs(p2.x - p1.x) * 0.5);
      const c1x = p1.x + dx;
      const c1y = p1.y;
      const c2x = p2.x - dx;
      const c2y = p2.y;

      const d = `M ${p1.x} ${p1.y} C ${c1x} ${c1y}, ${c2x} ${c2y}, ${p2.x} ${p2.y}`;

      const path = document.createElementNS('http://www.w3.org/2000/svg','path');
      path.setAttribute('d', d);
      path.setAttribute('fill', 'none');
      path.setAttribute('stroke', 'rgba(15,23,42,.55)');
      path.setAttribute('stroke-width', '2');
      path.setAttribute('marker-end', 'url(#arrow)');
      path.classList.add('link');
      svg.appendChild(path);

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
    });
  }

  async function loadGraph() {
    if (!procesoId) return;

    try {
      const res = await fetch(`/process-builder/${procesoId}/graph`, {
        headers: { 'Accept': 'application/json' }
      });
      if (!res.ok) return;

      const json = await res.json();
      state.links = json.relaciones || [];
      drawLinks();
    } catch (e) {
      console.error('No se pudo cargar el grafo', e);
    }
  }

  window.addEventListener('resize', drawLinks);
  loadGraph();
})();
</script>
@endpush
