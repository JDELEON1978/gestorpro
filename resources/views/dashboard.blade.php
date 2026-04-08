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
                <div class="d-flex align-items-center gap-2">

                  {{-- Combo: tareas de INICIO --}}
                  <select id="cmbStartTasks"
                          class="form-select form-select-sm"
                          style="min-width:260px; max-width:360px;"
                          title="Abrir tarea de inicio">
                    <option value="">— Tareas de inicio —</option>
                  </select>

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
{{-- Modal: Recorrido de tareas (chain por parent_task_id) --}}
<div class="modal fade" id="modalTaskChain" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          Recorrido — <span id="chainTitle" class="fw-semibold">—</span>
        </h5>

        <button class="btn gp-btn-primary js-open-chain-audit"
                type="button">
          <i class="bi bi-shield-check me-1"></i>
          Revisión auditoría
        </button>

        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="chain_root_task_id" value="">

        <div class="alert alert-danger d-none" id="chainError"></div>

        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Tarea</th>
                <th>Estado</th>
                <th>Prioridad</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Duración plan</th>
                <th>Duración real</th>
              </tr>
            </thead>
            <tbody id="chainTableBody">
              <tr>
                <td colspan="8" class="text-muted">Selecciona una tarea de inicio…</td>
              </tr>
            </tbody>
          </table>

          <div class="d-flex justify-content-end gap-4 mt-2 small">
            <div><strong>Total plan:</strong> <span id="chainTotalPlanned">—</span></div>
            <div><strong>Total real:</strong> <span id="chainTotalActual">—</span></div>
          </div>
        </div>

        <div class="form-text">
          Este listado sigue la relación <code>parent_task_id</code> desde la tarea seleccionada.
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn gp-btn" type="button" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

{{-- Modal: Actividades de tarea --}}
<div class="modal fade" id="modalTaskActivities" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          Actividades — <span id="activitiesTaskTitle" class="fw-semibold">—</span>
        </h5>
        <button class="btn gp-btn-primary" type="button" id="btnAuditReview">
          Revisión auditoría
        </button>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="activities_task_id" value="">

        <div class="alert alert-danger d-none" id="activitiesError"></div>

        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th style="width: 170px;">Fecha</th>
                <th style="width: 160px;">Usuario</th>
                <th style="width: 160px;">Evento</th>
                <th>Detalle</th>
              </tr>
            </thead>
            <tbody id="activitiesTableBody">
              <tr>
                <td colspan="4" class="text-muted">Cargando...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer d-flex justify-content-between">
        <button class="btn gp-btn-primary" type="button" id="btnAuditReview">
          <i class="bi bi-shield-check me-1"></i>
          Revisión auditoría
        </button>

        <button class="btn gp-btn" type="button" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

{{-- Modal: Revisión de auditoría --}}
<div class="modal fade" id="modalAuditReview" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          Revisión auditoría — <span id="auditTaskTitle" class="fw-semibold">—</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="alert alert-danger d-none" id="auditError"></div>

        <div class="border rounded p-3 bg-light">
          <pre id="auditReportBody" style="white-space: pre-wrap; margin:0; font-family: inherit;">Cargando...</pre>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn gp-btn" type="button" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>


@push('scripts')

<script>
  // ⚠️ Esto es lo ÚNICO inline que dejamos: config que antes venía de Blade dentro del JS.
  window.GP_DASHBOARD = {
    viewMode: @json($viewMode ?? 'tablero'),
  };
</script>

@vite('resources/js/dashboard/index.js')
@endpush


@endsection
