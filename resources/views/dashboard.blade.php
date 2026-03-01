{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')

{{-- ======================================================================
  [A] ESTILOS LOCALES DEL DASHBOARD
  - Aquí solo van estilos específicos de esta vista.
  - Si algo se reutiliza, muévelo al CSS global (app.css).
====================================================================== --}}
<style>
  /* [A1] Tabs de vista: Lista / Tablero / Tabla / Cronograma */
  .gp-tabs { display:flex; gap:10px; flex-wrap:wrap; }
  .gp-tab{
    display:inline-flex; align-items:center; justify-content:center;
    width: 120px; height: 36px; border-radius: 10px;
    border: 1px solid rgba(0,0,0,.10);
    text-decoration:none;
    background: #fff;
    font-weight: 700;
    font-size: 13px;
  }
  .gp-tab i{ font-size:16px; }
  .gp-tab-active{
    background: rgba(0,90,156,.10);
    border-color: rgba(0,90,156,.30);
  }

  /* [A2] Sidebar mini */
  .gp-sidebar-mini-wrap{ width: 64px; }

  /* [A3] UX: elemento tarea clickeable */
  .task-clickable{ cursor:pointer; }
</style>

{{-- ======================================================================
  [B] LAYOUT PRINCIPAL (2 columnas)
  - Sidebar izquierda (workspaces/proyectos)
  - Main derecha (header + contenido del proyecto)
====================================================================== --}}
<div class="container-fluid py-3">
  <div class="row g-3 align-items-stretch">

    {{-- ===============================================================
      [B1] SIDEBAR: Workspaces + Proyectos
      - Expanded y Collapsed (mini)
      - Botones: Nuevo Workspace + Toggle Sidebar
    =============================================================== --}}
    <div class="col-12 col-lg-3 col-xxl-3" id="gpSidebarCol">
      <div class="gp-panel h-100 d-flex flex-column">

        {{-- [B1.1] Header Sidebar (acciones rápidas) --}}
        <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom" style="border-color: var(--border);">
          <div class="fw-bold">&nbsp;</div>

          <div class="d-flex align-items-center gap-2">
            {{-- Nuevo Workspace --}}
            <button class="btn btn-sm gp-btn"
                    type="button"
                    title="Nuevo Workspace"
                    data-bs-toggle="modal"
                    data-bs-target="#modalNewWorkspace">
              <i class="bi bi-plus-lg"></i>
            </button>

            {{-- Toggle mini/expanded --}}
            <button class="btn btn-sm gp-btn"
                    type="button"
                    id="btnSidebarCollapse"
                    onclick="toggleSidebarMini()">
              <<
            </button>
          </div>
        </div>

        {{-- [B1.2] EXPANDED Sidebar --}}
        <div id="gpSidebarExpanded" class="p-2" style="flex:1 1 auto;">
          <div class="list-group list-group-flush" style="font-size: 13px;">

            {{-- Lista de workspaces --}}
            @forelse($workspaces as $ws)
              @php
                $wsOpen = ($currentProject && $currentProject->workspace_id === $ws->id);
                $collapseId = "wsCollapse_" . $ws->id;

                $wsCount = 0;
                foreach($ws->projects as $p){
                  $wsCount += $p->tasks()->whereNull('archived_at')->count();
                }
              @endphp

              {{-- Workspace row (colapsable) --}}
              <button
                class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#{{ $collapseId }}"
                aria-expanded="{{ $wsOpen ? 'true' : 'false' }}"
                aria-controls="{{ $collapseId }}"
                style="border-left: 0; border-right: 0;"
              >
                <div class="d-flex align-items-center gap-2 min-w-0">
                  <i class="bi bi-collection"></i>
                  <span class="text-truncate" style="max-width: 170px;">{{ $ws->name }}</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                  <span class="badge rounded-pill text-bg-secondary">{{ $wsCount }}</span>
                  <i class="bi {{ $wsOpen ? 'bi-caret-down-fill' : 'bi-caret-right-fill' }}"></i>
                </div>
              </button>

              {{-- Proyectos dentro del workspace --}}
              <div class="collapse {{ $wsOpen ? 'show' : '' }}" id="{{ $collapseId }}">
                <div class="list-group list-group-flush">

                  @forelse($ws->projects as $pr)
                    @php
                      $active = $currentProject && $currentProject->id === $pr->id;
                      $count  = $pr->tasks()->whereNull('archived_at')->count();
                    @endphp

                    <a class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 ps-4 {{ $active ? 'active' : '' }}"
                       href="{{ route('dashboard', ['project_id' => $pr->id, 'view' => $viewMode]) }}"
                       style="{{ $active ? '' : 'border-left:0; border-right:0;' }}"
                    >
                      <div class="d-flex align-items-center gap-2 min-w-0">
                        <i class="bi bi-folder"></i>
                        <span class="text-truncate" style="max-width: 165px;">{{ $pr->name }}</span>
                      </div>
                      <span class="badge rounded-pill {{ $active ? 'text-bg-light' : 'text-bg-secondary' }}">{{ $count }}</span>
                    </a>

                  @empty
                    <div class="list-group-item py-2 ps-4 text-muted">Sin proyectos</div>
                  @endforelse

                  {{-- Crear Proyecto --}}
                  <button type="button"
                          class="list-group-item list-group-item-action py-2 ps-4"
                          data-bs-toggle="modal"
                          data-bs-target="#modalNewProject"
                          data-workspace-id="{{ $ws->id }}"
                          data-workspace-name="{{ $ws->name }}"
                          style="border-left:0; border-right:0; font-weight:800;">
                    + Nuevo Proyecto
                  </button>
                </div>
              </div>

            @empty
              <div class="text-sm gp-muted text-center py-4">No tienes workspaces aún.</div>
            @endforelse

          </div>
        </div>

        {{-- [B1.3] COLLAPSED Sidebar (mini) --}}
        <div id="gpSidebarCollapsed" class="p-2 gp-sidebar-mini-wrap" style="display:none;">
          <div class="d-flex flex-column align-items-stretch gap-2">
            <button class="btn btn-sm gp-btn" type="button" onclick="toggleSidebarMini()" title="Expandir">
              >>
            </button>
          </div>
        </div>

      </div>
    </div>

    {{-- ===============================================================
      [B2] MAIN: Header + Área del proyecto
      - Breadcrumb + Título proyecto
      - Botón: Nueva tarea
      - Tabs: lista/tablero/tabla/cronograma
      - Área AJAX: #gpProjectArea
    =============================================================== --}}
    <div class="col-12 col-lg-9 col-xxl-9" id="gpMainCol">
      <div class="gp-panel overflow-hidden">

        {{-- [B2.1] HEADER --}}
        <div class="gp-main-head p-3 border-bottom" style="border-color: var(--border);">
          <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">

            {{-- Breadcrumb + Título --}}
            <div class="min-w-0">
              <div class="gp-breadcrumb">
                <span>{{ $currentProject?->workspace?->name ?? '—' }}</span>
                <span>›</span>
                <span class="fw-semibold">{{ $currentProject?->name ?? 'Selecciona un proyecto' }}</span>
              </div>

              <div class="gp-h1 mt-1">
                {{ $currentProject?->name ?? 'Selecciona un proyecto' }}
              </div>
            </div>

            {{-- Acciones header --}}
            <div class="d-flex align-items-center gap-2">
              @if($currentProject)
                {{-- [B2.1.1] Nueva tarea (abre modalTask) --}}
                <button class="btn gp-btn-primary"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#modalTask"
                        onclick="openCreateTaskModal()">
                  <i class="bi bi-plus-lg me-1"></i>
                </button>
              @endif
            </div>

          </div>

          {{-- [B2.1.2] Tabs de vista --}}
          @if($currentProject)
            @php
              $tabs = [
                'lista'   => ['Lista',   'bi-list-task'],
                'tablero' => ['Tablero', 'bi-kanban'],
                'tabla'   => ['Tabla',   'bi-table'],
                'cronograma' => ['Cronograma', 'bi-calendar-week'],
              ];
            @endphp

            <div class="gp-tabs mt-3">
              @foreach($tabs as $key => $meta)
                @php [$label, $icon] = $meta; @endphp
                <a class="gp-tab {{ $viewMode === $key ? 'gp-tab-active' : '' }}"
                   href="{{ route('dashboard', ['project_id' => $currentProject->id, 'view' => $key]) }}">
                  <i class="bi {{ $icon }} me-2"></i>{{ $label }}
                </a>
              @endforeach
            </div>
          @endif
        </div>

        {{-- [B2.2] CONTENIDO --}}
        <div class="gp-content p-3">
          @if(!$currentProject)
            <div class="text-sm gp-muted text-center py-5">
              Selecciona un proyecto del panel izquierdo.
            </div>
          @else
            {{-- [B2.2.1] Área dinámica AJAX: se reemplaza con refreshProjectArea() --}}
            <div id="gpProjectArea">
              @if($viewMode === 'lista')
                @include('tasks.views.lista', ['project' => $currentProject, 'statuses' => $statuses, 'tasksByStatus' => $tasksByStatus])
              @elseif($viewMode === 'tabla')
                @include('tasks.views.tabla', ['project' => $currentProject, 'statuses' => $statuses, 'tasksByStatus' => $tasksByStatus])
              @elseif($viewMode === 'cronograma')
                @include('tasks.views.cronograma', ['project' => $currentProject, 'statuses' => $statuses, 'tasksByStatus' => $tasksByStatus])
              @else
                @include('tasks.views.tablero', ['project' => $currentProject, 'statuses' => $statuses, 'tasksByStatus' => $tasksByStatus])
              @endif
            </div>
          @endif
        </div>

      </div>
    </div>

  </div>
</div>

{{-- ======================================================================
  [C] MODALES
  Regla: aquí SOLO markup HTML de modales (sin JS).
====================================================================== --}}

{{-- ===============================================================
  [C1] MODAL: Nuevo Proyecto
  - action se define con JS usando workspace_id
=============================================================== --}}
<div class="modal fade" id="modalNewProject" tabindex="-1" aria-hidden="true">
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="modal-dialog">
    <form class="modal-content" method="POST" id="formNewProject" action="">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Nuevo Proyecto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="small text-muted mb-2">
          Workspace: <span id="npWorkspaceName" class="fw-semibold">—</span>
        </div>

        <div class="mb-3">
          <label class="form-label">Proceso</label>
          <select name="proceso_id" class="form-select" required>
            <option value="">-- Selecciona un proceso --</option>
            @foreach($procesos as $p)
              <option value="{{ $p->id }}" @selected(old('proceso_id') == $p->id)>
                {{ $p->nombre }} ({{ $p->codigo }})
              </option>
            @endforeach
          </select>

          @error('proceso_id')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <label class="form-label fw-semibold">Nombre</label>
        <input type="text" name="name" class="form-control" required placeholder="Ej: 2.- TDR-2026">

        <label class="form-label fw-semibold mt-3">Descripción (opcional)</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Breve descripción"></textarea>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn gp-btn" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn gp-btn-primary">Crear</button>
      </div>
    </form>
  </div>
</div>

{{-- ===============================================================
  [C2] MODAL: Nuevo Workspace
=============================================================== --}}
<div class="modal fade" id="modalNewWorkspace" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('workspaces.store') }}">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Nuevo Workspace</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <label class="form-label fw-semibold">Nombre</label>
        <input type="text" name="name" class="form-control" required placeholder="Ej: Administración">
        <div class="form-text">Crea un nuevo espacio de trabajo.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn gp-btn" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn gp-btn-primary">Crear</button>
      </div>
    </form>
  </div>
</div>

{{-- ===============================================================
  [C3] MODAL: Archivos de Tarea (TaskFiles)
  - inputs: #files_input, #btnUploadFiles
  - tabla:  #filesTableBody
=============================================================== --}}
<div class="modal fade" id="modalFiles" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          Archivos — <span id="filesTaskTitle" class="fw-semibold">—</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="files_task_id" value="">

        <div class="d-flex align-items-center gap-2 mb-3">
          <input class="form-control" type="file" id="files_input" multiple>
          <button class="btn gp-btn-primary" type="button" id="btnUploadFiles">
            <i class="bi bi-upload me-1"></i> Subir
          </button>
        </div>

        <div class="alert alert-danger d-none" id="filesError"></div>

        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Archivo</th>
                <th style="width:140px;">Tamaño</th>
                <th style="width:180px;">Subido por</th>
                <th style="width:170px;">Fecha</th>
                <th style="width:120px;"></th>
              </tr>
            </thead>
            <tbody id="filesTableBody">
              <tr><td colspan="5" class="text-muted">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn gp-btn" type="button" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

{{-- ===============================================================
  [C4] MODAL: Crear/Editar Tarea
  - Form: #formTask
  - Secciones: Info nodo / Campos / Evidencias / Transiciones
=============================================================== --}}
<div class="modal fade" id="modalTask" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" id="formTask">
      @csrf
      <input type="hidden" id="task_status_name" value="">

      <div class="modal-header">
        <h5 class="modal-title" id="taskModalTitle">Nueva tarea</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      {{-- hidden fields --}}
      <input type="hidden" id="task_id" value="">
      <input type="hidden" id="task_project_id" value="{{ $currentProject?->id ?? '' }}">
      <input type="hidden" id="task_nodo_id" value="">

      <div class="modal-body">

        {{-- [C4.1] Panel info del nodo (workflow) --}}
        <div class="alert alert-info py-2" id="taskProcessInfo" style="display:none;">
          <div class="fw-semibold">Nodo inicio: <span id="taskNodoNombre">—</span></div>
          <div class="small text-muted" id="taskNodoDesc"></div>
          <div class="small mt-2">
            <div class="fw-semibold">Ítems requeridos:</div>
            <ul class="mb-0" id="taskNodoItems"></ul>
          </div>
        </div>

        {{-- [C4.2] Campos principales de la tarea --}}
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold">Título</label>
            <input type="text" class="form-control" id="task_title" required>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold">Descripción</label>
            <textarea class="form-control" id="task_description" rows="3"></textarea>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">Columna</label>
            <select class="form-select" id="task_status_id" required>
              @foreach($statuses as $st)
                <option value="{{ $st->id }}">{{ $st->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">Prioridad (1-5)</label>
            <select class="form-select" id="task_priority">
              <option value="">—</option>
              @for($i=1;$i<=5;$i++)
                <option value="{{ $i }}">{{ $i }}</option>
              @endfor
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Fecha inicio</label>
            <input type="date" class="form-control" id="task_start_at">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Fecha fin (límite)</label>
            <input type="date" class="form-control" id="task_due_at">
          </div>
        </div>

        {{-- [C4.3] Errores --}}
        <div class="alert alert-danger mt-3 d-none" id="taskError"></div>

        {{-- [C4.4] Evidencias requeridas (solo UI) --}}
        <div id="taskEvidenciasWrap" class="mt-3" style="display:none;">
          <div class="fw-semibold mb-2">Evidencias requeridas</div>
          <div id="taskEvidenciasRows" class="d-grid gap-2"></div>
        </div>

      </div>

      {{-- [C4.5] Footer: transiciones + botones guardar --}}
      <div class="modal-footer d-flex align-items-center justify-content-between">
        <div id="taskTransWrap" style="display:none;">
          <div class="fw-semibold mb-2">Transiciones</div>
          <div id="taskTransButtons" class="d-flex flex-wrap gap-2"></div>
        </div>

        <button type="button"
              class="btn gp-btn js-open-files"
              id="btnOpenTaskFiles"
              data-task-id="">
        <i class="bi bi-paperclip me-1"></i> Archivos
      </button>

        <div class="d-flex gap-2">
          <button type="button" class="btn gp-btn" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn gp-btn-primary">Guardar</button>
        </div>
      </div>

    </form>
  </div>
</div>




@push('scripts')
<script>
/* ============================================================
 * SHARED HELPERS
 * - Utilidades reutilizables por todos los módulos
 * ============================================================ */
function escapeHtml(str){
  str = (str ?? '').toString();
  return str.replace(/[&<>"']/g, (m) => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
  }[m]));
}

function fmtBytes(bytes){
  bytes = parseInt(bytes || 0, 10);
  if (!bytes) return '—';
  const units = ['B','KB','MB','GB'];
  let i = 0;
  let v = bytes;
  while (v >= 1024 && i < units.length-1) { v /= 1024; i++; }
  return `${v.toFixed(i === 0 ? 0 : 1)} ${units[i]}`;
}

function csrfHeaders(extra = {}){
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const h = { 'X-Requested-With': 'XMLHttpRequest', ...extra };
  if (token) h['X-CSRF-TOKEN'] = token;
  return h;
}

/* ============================================================
 * MODULE A: SIDEBAR + NUEVO PROYECTO
 * - Colapsar/expandir sidebar
 * - Setear action del modal "Nuevo Proyecto" según workspace
 * ============================================================ */
(function(){
  window.toggleSidebarMini = function(){
    const sidebarCol = document.getElementById('gpSidebarCol');
    const mainCol    = document.getElementById('gpMainCol');
    const expanded   = document.getElementById('gpSidebarExpanded');
    const collapsed  = document.getElementById('gpSidebarCollapsed');
    const btn        = document.getElementById('btnSidebarCollapse');

    if(!sidebarCol || !mainCol || !expanded || !collapsed || !btn) return;

    const isMini = sidebarCol.classList.contains('col-auto');
    if(isMini){
      sidebarCol.className = 'col-12 col-lg-3 col-xxl-3';
      mainCol.className    = 'col-12 col-lg-9 col-xxl-9';
      expanded.style.display  = 'block';
      collapsed.style.display = 'none';
      btn.textContent = '<<';
    }else{
      sidebarCol.className = 'col-auto';
      mainCol.className    = 'col';
      expanded.style.display  = 'none';
      collapsed.style.display = 'block';
      btn.textContent = '>>';
    }
  };

  document.querySelectorAll('[data-bs-target="#modalNewProject"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const wsId = btn.getAttribute('data-workspace-id');
      const wsName = btn.getAttribute('data-workspace-name');
      const nameEl = document.getElementById('npWorkspaceName');
      if(nameEl) nameEl.textContent = wsName || '—';
      const form = document.getElementById('formNewProject');
      if(form) form.action = `/workspaces/${wsId}/projects`;
    });
  });
})();

/* ============================================================
 * MODULE B: TASK MODAL (crear/editar) + NODO META
 * - Carga meta del nodo (items/transiciones)
 * - Render panel informativo del nodo
 * - Render items (texto)
 * - Render evidencias UI (solo obligatorios) -> La lógica está en MODULE E
 * ============================================================ */
(function(){
  function resetNodoUI(){
    const nodoIdEl = document.getElementById('task_nodo_id');
    if(nodoIdEl) nodoIdEl.value = '';

    const info = document.getElementById('taskProcessInfo');
    if(info) info.style.display = 'none';

    const evidWrap = document.getElementById('taskEvidenciasWrap');
    if(evidWrap) evidWrap.style.display = 'none';

    const transWrap = document.getElementById('taskTransWrap');
    if(transWrap) transWrap.style.display = 'none';

    const nodoNombre = document.getElementById('taskNodoNombre');
    const nodoDesc   = document.getElementById('taskNodoDesc');
    const nodoItems  = document.getElementById('taskNodoItems');
    const evRows     = document.getElementById('taskEvidenciasRows');
    const transBtns  = document.getElementById('taskTransButtons');

    if(nodoNombre) nodoNombre.textContent = '—';
    if(nodoDesc)   nodoDesc.textContent = '';
    if(nodoItems)  nodoItems.innerHTML = '';
    if(evRows)     evRows.innerHTML = '';
    if(transBtns)  transBtns.innerHTML = '';
  }

  function renderEvidenceBadge(estado){
    const st = (estado || 'PENDIENTE').toString().toUpperCase();
    const map = {
      'PENDIENTE':  { cls:'text-bg-secondary', label:'PENDIENTE' },
      'SUBIDO':     { cls:'text-bg-info',      label:'SUBIDO' },
      'EN_REVISION':{ cls:'text-bg-warning',   label:'EN REVISIÓN' },
      'APROBADO':   { cls:'text-bg-success',   label:'APROBADO' },
      'RECHAZADO':  { cls:'text-bg-danger',    label:'RECHAZADO' },
    };
    const meta = map[st] || map['PENDIENTE'];
    return `<span class="badge ${meta.cls}">${meta.label}</span>`;
  }

  function renderNodoMeta(nodo){
    document.getElementById('task_nodo_id').value = nodo.id;

    // Panel info nodo
    const info = document.getElementById('taskProcessInfo');
    info.style.display = 'block';
    document.getElementById('taskNodoNombre').textContent = nodo.nombre || `Nodo #${nodo.id}`;
    document.getElementById('taskNodoDesc').textContent   = nodo.descripcion || '';

    // Lista de items (texto)
    const itemsUl = document.getElementById('taskNodoItems');
    const items = nodo.items || [];
    itemsUl.innerHTML = items.length
      ? items.map(it => {
          const opt = it.obligatorio ? '' : ' <span class="text-muted">(opcional)</span>';
          return `<li>${escapeHtml(it.nombre)} <span class="text-muted">(${escapeHtml(it.categoria)})</span>${opt}</li>`;
        }).join('')
      : `<li class="text-muted">Sin ítems configurados.</li>`;

    // Evidencias UI (solo obligatorios)
    const evidWrap = document.getElementById('taskEvidenciasWrap');
    const evRows   = document.getElementById('taskEvidenciasRows');
    const oblig = items.filter(x => x.obligatorio);

    if (oblig.length) {
      evidWrap.style.display = 'block';

      evRows.innerHTML = oblig.map((it) => {
        // IMPORTANTE: este it.id debe venir del backend (nodo_items.id)
        const itemId = it.id ?? it.nodo_item_id ?? null;
        const safeItemId = itemId ? String(itemId) : '';
        const inputId = safeItemId ? `evi_file_${safeItemId}` : '';

        const disabled = safeItemId ? '' : 'disabled';

        return `
          <div class="border rounded p-2" data-evi-row="${escapeHtml(safeItemId)}">
            <div class="d-flex align-items-start justify-content-between gap-2">
              <div class="small fw-semibold">
                ${escapeHtml(it.nombre)}
                <span class="text-muted">(${escapeHtml(it.categoria)})</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                ${renderEvidenceBadge('PENDIENTE')}
              </div>
            </div>

            <div class="small text-muted mt-1">
              1 archivo por ítem (Pendiente/Subido/En revisión/Aprobado/Rechazado).
            </div>

            <!-- INPUT REAL oculto para evitar problemas de click -->
            <input class="d-none js-evi-input"
                   type="file"
                   id="${escapeHtml(inputId)}"
                   data-item-id="${escapeHtml(safeItemId)}"
                   ${disabled}>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
              <button type="button"
                      class="btn btn-sm gp-btn js-evi-pick"
                      data-item-id="${escapeHtml(safeItemId)}"
                      ${disabled}>
                <i class="bi bi-paperclip me-1"></i> Seleccionar
              </button>

              <button type="button"
                      class="btn btn-sm gp-btn-primary js-upload-evidence"
                      data-item-id="${escapeHtml(safeItemId)}"
                      ${disabled}>
                <i class="bi bi-upload me-1"></i> Subir
              </button>

              <a class="btn btn-sm gp-btn js-evi-download d-none"
                 href="#"
                 target="_blank"
                 data-item-id="${escapeHtml(safeItemId)}">
                <i class="bi bi-download me-1"></i> Descargar
              </a>

              <span class="small text-muted js-evi-fileinfo"
                    data-item-id="${escapeHtml(safeItemId)}"></span>
            </div>

            <div class="alert alert-danger d-none mt-2 py-1 small js-evi-error"
                 data-item-id="${escapeHtml(safeItemId)}"></div>

            ${safeItemId ? '' : `<div class="text-danger small mt-2">Este ítem no trae ID desde el backend, no se puede controlar evidencia por ítem.</div>`}
          </div>
        `;
      }).join('');
    } else {
      evidWrap.style.display = 'none';
      evRows.innerHTML = '';
    }

    // Transiciones
    const trans = (nodo.transiciones || []).filter(t => (t.etiqueta ?? '').toString().trim() !== '');
    const transWrap = document.getElementById('taskTransWrap');
    const transBtns = document.getElementById('taskTransButtons');

    if (trans.length) {
      transWrap.style.display = 'block';
      transBtns.innerHTML = trans.map(t => `
        <button type="button"
                class="btn btn-outline-secondary btn-sm js-task-trans"
                data-next-nodo-id="${t.nodo_destino_id}"
                data-is-end="${t.is_end ? 1 : 0}"
                title="${escapeHtml(t.nodo_destino_nombre || '')}">
          ${escapeHtml(t.etiqueta || 'Continuar')}
        </button>
      `).join('');
    } else {
      transWrap.style.display = 'none';
      transBtns.innerHTML = '';
    }
  }

  async function loadNodoMeta(projectId, nodoIdOrNull){
    const qs = nodoIdOrNull ? `?nodo_id=${encodeURIComponent(nodoIdOrNull)}` : '';
    const res = await fetch(`/projects/${projectId}/start-node${qs}`, {
      headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }
    });
    if(!res.ok) return null;
    const data = await res.json().catch(() => null);
    if(!data || !data.ok) return null;
    return data.nodo || null;
  }

  window.openCreateTaskModal = async function(){
    document.getElementById('taskModalTitle').textContent = 'Nueva tarea';
    document.getElementById('task_id').value = '';
    document.getElementById('taskError').classList.add('d-none');

    resetNodoUI();

    document.getElementById('task_title').value = '';
    document.getElementById('task_description').value = '';
    document.getElementById('task_priority').value = '';
    document.getElementById('task_start_at').value = '';
    document.getElementById('task_due_at').value = '';

    const projectId = document.getElementById('task_project_id')?.value;
    if(!projectId) return;

    try{
      const nodo = await loadNodoMeta(projectId, null);
      if(nodo){
        renderNodoMeta(nodo);
        document.getElementById('task_title').value = nodo.nombre || 'Inicio';
        document.getElementById('task_description').value = nodo.descripcion || '';
      }
    }catch(e){
      console.error(e);
    }
  };

  window.openEditTaskModal = async function(task){
    document.getElementById('taskModalTitle').textContent = 'Editar tarea';
    document.getElementById('task_id').value = task.id || '';
    document.getElementById('task_title').value = task.title || '';
    document.getElementById('task_description').value = task.description || '';
    document.getElementById('task_status_id').value = task.status_id || '';
    document.getElementById('task_priority').value = (task.priority ?? '');
    document.getElementById('task_start_at').value = (task.start_at ?? '');
    document.getElementById('task_due_at').value = (task.due_at ?? '');
    document.getElementById('taskError').classList.add('d-none');
    document.getElementById('task_status_name').value = (task.status_name || task.status || '');

    resetNodoUI();

    const projectId = document.getElementById('task_project_id')?.value;
    const nodoId    = task.nodo_id || null;

    if(projectId && nodoId){
      try{
        const nodo = await loadNodoMeta(projectId, nodoId);
        if(nodo) renderNodoMeta(nodo);
      }catch(e){
        console.error(e);
      }
    }

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTask')).show();

    // Cargar estados/archivos por ítem
    if (typeof window.loadTaskEvidences === 'function') {
      window.loadTaskEvidences(task.id);
    }
  };

  // Click en tarea => abrir editar (respeta botones/inputs)
  document.addEventListener('click', (e) => {
    if (e.target.closest('.js-open-files')) return;
    if (e.target.closest('#modalFiles')) return;
    if (e.target.closest('button, a, input, select, textarea, label')) return;

    const el = e.target.closest('.js-task');
    if(!el) return;

    const raw = el.getAttribute('data-task');
    if(!raw) return;

    try{
      const task = JSON.parse(raw);
      window.openEditTaskModal(task);
    }catch(err){
      console.error('data-task inválido', err);
    }
  });

  async function refreshProjectArea(){
    const projectId = document.getElementById('task_project_id')?.value;
    const viewMode = @json($viewMode ?? 'tablero');
    if(!projectId) return;

    const url = `/dashboard?project_id=${projectId}&view=${encodeURIComponent(viewMode)}`;
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
    const html = await res.text();

    const tmp = document.createElement('div');
    tmp.innerHTML = html;

    const newArea = tmp.querySelector('#gpProjectArea');
    const curArea = document.querySelector('#gpProjectArea');

    if(newArea && curArea){
      curArea.innerHTML = newArea.innerHTML;
    }else{
      window.location.href = url;
    }
  }
  window.refreshProjectArea = refreshProjectArea;

  // Guardar tarea
  document.getElementById('formTask')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const projectId = document.getElementById('task_project_id').value;
    const id = document.getElementById('task_id').value;

    const payload = {
      title: document.getElementById('task_title').value,
      description: document.getElementById('task_description').value || null,
      status_id: document.getElementById('task_status_id').value,
      priority: document.getElementById('task_priority').value || null,
      start_at: document.getElementById('task_start_at').value || null,
      due_at: document.getElementById('task_due_at').value || null,
      nodo_id: document.getElementById('task_nodo_id').value || null,
      view: @json($viewMode ?? 'tablero')
    };

    let url, method;
    if(id){
      url = `/tasks/${id}`;
      method = 'PATCH';
    }else{
      url = `/projects/${projectId}/tasks`;
      method = 'POST';
    }

    try{
      const res = await fetch(url, {
        method,
        headers: csrfHeaders({ 'Content-Type': 'application/json', 'Accept':'application/json' }),
        body: JSON.stringify(payload)
      });

      if(!res.ok){
        const data = await res.json().catch(() => ({}));
        const msg = data.message || 'No se pudo guardar la tarea.';
        const errEl = document.getElementById('taskError');
        errEl.textContent = msg;
        errEl.classList.remove('d-none');
        return;
      }

      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTask')).hide();
      await refreshProjectArea();

    }catch(err){
      console.error(err);
      const errEl = document.getElementById('taskError');
      errEl.textContent = 'Error de red o servidor.';
      errEl.classList.remove('d-none');
    }
  });
})();

/* ============================================================
 * MODULE C: WORKFLOW / TRANSICIONES (avance)
 * - Avanza tarea según transición seleccionada
 * ============================================================ */
(function(){
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-task-trans');
    if(!btn) return;

    const taskId = document.getElementById('task_id')?.value;
    const projectId = document.getElementById('task_project_id')?.value;
    const nextNodoId = btn.getAttribute('data-next-nodo-id');
    const isEnd = btn.getAttribute('data-is-end') === '1';

    if(!taskId || !projectId) return;

    const msg = isEnd
      ? '¿Seguro que deseas finalizar esta tarea? Se marcará como Done.'
      : '¿Seguro que deseas terminar esta tarea y abrir la siguiente?';

    if(!confirm(msg)) return;

    try{
      const res = await fetch(`/tasks/${taskId}/advance`, {
        method: 'POST',
        headers: csrfHeaders({ 'Accept':'application/json', 'Content-Type':'application/json' }),
        body: JSON.stringify({ next_nodo_id: nextNodoId })
      });

      const data = await res.json().catch(() => ({}));

      if(!res.ok || !data.ok){
        alert(data.message || 'No se pudo avanzar la tarea.');
        return;
      }

      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTask')).hide();

      if (typeof window.refreshProjectArea === 'function') await window.refreshProjectArea();

      if(data.next_task && typeof window.openEditTaskModal === 'function'){
        window.openEditTaskModal(data.next_task);
      }

    }catch(err){
      console.error(err);
      alert('Error de red o servidor.');
    }
  });
})();

/* ============================================================
 * MODULE D: TASK FILES MODAL (subir/ver)
 * - Adjuntos generales de la tarea (múltiples)
 * ============================================================ */
(function(){
  async function renderFilesTable(files){
    const bodyEl = document.getElementById('filesTableBody');

    if (!files || !files.length){
      bodyEl.innerHTML = `<tr><td colspan="5" class="text-muted">No hay archivos aún.</td></tr>`;
      return;
    }

    bodyEl.innerHTML = files.map(f => `
      <tr>
        <td class="text-truncate" style="max-width:420px;">
          <i class="bi bi-file-earmark me-1"></i>
          ${escapeHtml(f.original_name)}
        </td>
        <td>${fmtBytes(f.size_bytes)}</td>
        <td>${escapeHtml(f.user_name || '—')}</td>
        <td class="small text-muted">${escapeHtml(f.created_at || '—')}</td>
        <td class="text-end">
          <a class="btn btn-sm gp-btn" href="${f.download_url}" target="_blank" title="Descargar">
            <i class="bi bi-download"></i>
          </a>
          <button class="btn btn-sm gp-btn js-del-file" data-file-id="${f.id}" title="Eliminar">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
  }

  async function openFilesModal(taskId){
    const modalEl = document.getElementById('modalFiles');
    const errEl   = document.getElementById('filesError');
    const titleEl = document.getElementById('filesTaskTitle');
    const hidden  = document.getElementById('files_task_id');
    const bodyEl  = document.getElementById('filesTableBody');

    errEl.classList.add('d-none');
    errEl.textContent = '';
    hidden.value = taskId;

    bodyEl.innerHTML = `<tr><td colspan="5" class="text-muted">Cargando...</td></tr>`;
    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    const res = await fetch(`/tasks/${taskId}/files`, { headers: csrfHeaders({ 'Accept':'application/json' }) });
    const data = await res.json().catch(()=> ({}));

    titleEl.textContent = data?.task?.title || `Tarea #${taskId}`;
    await renderFilesTable(data.files || []);
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-files');
    if(!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const taskId = btn.getAttribute('data-task-id');
    if(taskId) openFilesModal(taskId);
  });

  document.getElementById('btnUploadFiles')?.addEventListener('click', async (e) => {
    e.preventDefault();

    const taskId = document.getElementById('files_task_id').value;
    const input  = document.getElementById('files_input');
    const errEl  = document.getElementById('filesError');

    errEl.classList.add('d-none');
    errEl.textContent = '';

    if(!taskId) return;

    if(!input.files || !input.files.length){
      errEl.textContent = 'Selecciona al menos un archivo.';
      errEl.classList.remove('d-none');
      return;
    }

    const fd = new FormData();
    Array.from(input.files).forEach(f => fd.append('files[]', f));

    const res = await fetch(`/tasks/${taskId}/files`, {
      method: 'POST',
      headers: csrfHeaders({ 'Accept':'application/json' }),
      body: fd
    });

    if(!res.ok){
      let msg = 'No se pudo subir el archivo.';
      try{
        const data = await res.json();
        msg = data?.message || msg;
      }catch(e){}
      errEl.textContent = msg;
      errEl.classList.remove('d-none');
      return;
    }

    input.value = '';
    await openFilesModal(taskId);

    if (typeof window.refreshProjectArea === 'function') await window.refreshProjectArea();
  });
})();

/* ============================================================
 * MODULE E: EVIDENCIAS POR ITEM (CONTROL DE ESTADOS)
 * - 1 archivo por item obligatorio
 * - botón "Seleccionar" por item (input.click())
 * - botón "Subir" por item
 * - badge + download + file info
 * ============================================================ */
(function(){
  const STATUS = {
    PENDIENTE: 'PENDIENTE',
    SUBIDO: 'SUBIDO',
    EN_REVISION: 'EN_REVISION',
    APROBADO: 'APROBADO',
    RECHAZADO: 'RECHAZADO',
  };

  function setRowEstado(itemId, estado, file){
    const row = document.querySelector(`[data-evi-row="${CSS.escape(String(itemId))}"]`);
    if(!row) return;

    const badgeWrap = row.querySelector('.badge')?.parentElement;
    if(badgeWrap){
      const st = (estado || STATUS.PENDIENTE).toString().toUpperCase();
      const map = {
        'PENDIENTE':   { cls:'text-bg-secondary', label:'PENDIENTE' },
        'SUBIDO':      { cls:'text-bg-info',      label:'SUBIDO' },
        'EN_REVISION': { cls:'text-bg-warning',   label:'EN REVISIÓN' },
        'APROBADO':    { cls:'text-bg-success',   label:'APROBADO' },
        'RECHAZADO':   { cls:'text-bg-danger',    label:'RECHAZADO' },
      };
      const meta = map[st] || map['PENDIENTE'];
      badgeWrap.innerHTML = `<span class="badge ${meta.cls}">${meta.label}</span>`;
    }

    const info = row.querySelector(`.js-evi-fileinfo[data-item-id="${CSS.escape(String(itemId))}"]`);
    const aDL  = row.querySelector(`.js-evi-download[data-item-id="${CSS.escape(String(itemId))}"]`);

    if(file && (file.original_name || file.download_url)){
      if(info){
        const size = file.size_bytes ? ` • ${fmtBytes(file.size_bytes)}` : '';
        const date = file.created_at ? ` • ${escapeHtml(file.created_at)}` : '';
        info.innerHTML = `<span class="text-muted">${escapeHtml(file.original_name || 'archivo')}${size}${date}</span>`;
      }
      if(aDL && file.download_url){
        aDL.href = file.download_url;
        aDL.classList.remove('d-none');
      }
    }else{
      if(info) info.innerHTML = '';
      if(aDL){
        aDL.href = '#';
        aDL.classList.add('d-none');
      }
    }
  }

  function setRowError(itemId, msg){
    const row = document.querySelector(`[data-evi-row="${CSS.escape(String(itemId))}"]`);
    if(!row) return;
    const err = row.querySelector(`.js-evi-error[data-item-id="${CSS.escape(String(itemId))}"]`);
    if(!err) return;

    if(msg){
      err.textContent = msg;
      err.classList.remove('d-none');
    }else{
      err.textContent = '';
      err.classList.add('d-none');
    }
  }

  // Cargar estados actuales por item
  window.loadTaskEvidences = async function(taskId){
    if(!taskId) return;

    try{
      const res = await fetch(`/tasks/${taskId}/evidences`, {
        headers: csrfHeaders({ 'Accept':'application/json' })
      });
      const data = await res.json().catch(() => ({}));
      if(!res.ok || !data.ok) return;

      const items = data.items || [];
      items.forEach(x => {
        const itemId = x.item_id ?? x.id;
        if(!itemId) return;
        setRowEstado(itemId, x.estado || STATUS.PENDIENTE, x.file || null);
        setRowError(itemId, null);
      });
    }catch(err){
      console.error(err);
    }
  };

  // Botón "Seleccionar" (abre file picker)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-evi-pick');
    if(!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const itemId = btn.getAttribute('data-item-id');
    if(!itemId) return;

    const input = document.querySelector(`.js-evi-input[data-item-id="${CSS.escape(String(itemId))}"]`);
    if(!input) return;

    // abre el explorador SIEMPRE (más confiable que click directo al input en modales)
    input.click();
  });

  // Cuando seleccionan archivo, mostramos el nombre (sin subir)
  document.addEventListener('change', (e) => {
    const input = e.target.closest('.js-evi-input');
    if(!input) return;

    const itemId = input.getAttribute('data-item-id');
    if(!itemId) return;

    const row = document.querySelector(`[data-evi-row="${CSS.escape(String(itemId))}"]`);
    if(!row) return;

    const info = row.querySelector(`.js-evi-fileinfo[data-item-id="${CSS.escape(String(itemId))}"]`);
    if(!info) return;

    if(input.files && input.files.length){
      const f = input.files[0];
      info.innerHTML = `<span class="text-muted">${escapeHtml(f.name)} • ${fmtBytes(f.size)}</span>`;
    }else{
      info.innerHTML = '';
    }
  });

  // Subir evidencia (1 archivo por item)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-upload-evidence');
    if(!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const itemId = btn.getAttribute('data-item-id');
    const taskId = document.getElementById('task_id')?.value;

    if(!taskId){
      alert('Primero guarda la tarea para poder subir evidencias.');
      return;
    }
    if(!itemId){
      alert('Este ítem no tiene ID (nodo_item_id).');
      return;
    }

    setRowError(itemId, null);

    const input = document.querySelector(`.js-evi-input[data-item-id="${CSS.escape(String(itemId))}"]`);
    if(!input || !input.files || !input.files.length){
      setRowError(itemId, 'Selecciona un archivo.');
      return;
    }

    const file = input.files[0];

    btn.disabled = true;
    try{
      const fd = new FormData();
      fd.append('file', file);

      const res = await fetch(`/tasks/${taskId}/evidences/${itemId}`, {
        method: 'POST',
        headers: csrfHeaders({ 'Accept':'application/json' }),
        body: fd
      });

      const data = await res.json().catch(() => ({}));

      if(!res.ok || !data.ok){
        setRowError(itemId, data.message || 'No se pudo subir el archivo.');
        btn.disabled = false;
        return;
      }

      input.value = '';

      if(data.item){
        setRowEstado(itemId, data.item.estado || STATUS.SUBIDO, data.item.file || null);
      }else{
        setRowEstado(itemId, STATUS.SUBIDO, null);
      }

      btn.disabled = false;

      if (typeof window.refreshProjectArea === 'function') {
        await window.refreshProjectArea();
      }

    }catch(err){
      console.error(err);
      setRowError(itemId, 'Error de red o servidor.');
      btn.disabled = false;
    }
  });
})();
</script>



@endpush
@endsection
