{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
  /* Ajustes locales (para que no dependas del CSS externo al 100%) */
  .gp-tabs { display:flex; gap:10px; flex-wrap:wrap; }
  .gp-tab{
    display:inline-flex; align-items:center; justify-content:center;
    width: 120px; height: 38px; border-radius: 999px;
    border: 1px solid rgba(0,0,0,.10);
    text-decoration:none;
    background: #fff;
    font-weight: 700;
  }
  .gp-tab i{ font-size:16px; }
  .gp-tab-active{
    background: rgba(0,90,156,.10);
    border-color: rgba(0,90,156,.30);
  }

  /* Sidebar mini */
  .gp-sidebar-mini-wrap{
    width: 64px;
  }
</style>

<div class="container-fluid py-3">
  <div class="row g-3 align-items-stretch">

    {{-- SIDEBAR (25%) --}}
    <div class="col-12 col-lg-3 col-xxl-3" id="gpSidebarCol">
      <div class="gp-panel h-100 d-flex flex-column">

        {{-- Header sidebar --}}
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

            {{-- Colapsar --}}
            <button class="btn btn-sm gp-btn"
                    type="button"
                    id="btnSidebarCollapse"
                    onclick="toggleSidebarMini()">
              <<
            </button>
          </div>
        </div>

        {{-- EXPANDED --}}
        <div id="gpSidebarExpanded" class="p-2" style="flex:1 1 auto;">
          <div class="list-group list-group-flush" style="font-size: 13px;">
            @forelse($workspaces as $ws)
              @php
                $wsOpen = ($currentProject && $currentProject->workspace_id === $ws->id);
                $collapseId = "wsCollapse_" . $ws->id;

                $wsCount = 0;
                foreach($ws->projects as $p){
                  $wsCount += $p->tasks()->whereNull('archived_at')->count();
                }
              @endphp

              {{-- Workspace --}}
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

              {{-- Proyectos --}}
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

                  {{-- Nuevo Proyecto (modal) --}}
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

        {{-- COLLAPSED --}}
        <div id="gpSidebarCollapsed" class="p-2 gp-sidebar-mini-wrap" style="display:none;">
          <div class="d-flex flex-column align-items-stretch gap-2">
            <button class="btn btn-sm gp-btn" type="button" onclick="toggleSidebarMini()" title="Expandir">
              >>
            </button>
          </div>
        </div>

      </div>
    </div>

    {{-- MAIN (se expande cuando sidebar colapsa) --}}
    <div class="col-12 col-lg-9 col-xxl-9" id="gpMainCol">
      <div class="gp-panel overflow-hidden">

        {{-- Header tipo ClickUp --}}
        <div class="gp-main-head p-3 border-bottom" style="border-color: var(--border);">
          <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">

            <div class="min-w-0">
              <div class="gp-breadcrumb">
                <span>{{ $currentProject?->workspace?->name ?? '—' }}</span>
                <span>›</span>
                <span class="fw-semibold">{{ $currentProject?->name ?? 'Selecciona un proyecto' }}</span>
              </div>

              <div class="gp-h1 mt-1">
                {{ $currentProject?->name ?? 'Selecciona un proyecto' }}
              </div>

              <div class="text-sm gp-muted mt-1">
                Vistas tipo ClickUp
              </div>
            </div>

            {{-- Acciones derecha --}}
            <div class="d-flex align-items-center gap-2">
              @if($currentProject)
                <div class="input-group" style="max-width: 320px;">
                  <span class="input-group-text bg-white" style="border-color: var(--border);">
                    <i class="bi bi-search"></i>
                  </span>
                  <input class="form-control"
                         style="border-color: var(--border);"
                         placeholder="Buscar (MVP pronto)"
                         disabled>
                </div>

                <button class="btn gp-btn" type="button" disabled>
                  <i class="bi bi-funnel me-1"></i> Filtro
                </button>

                <a class="btn gp-btn-primary"
                   href="{{ route('projects.tasks.create', $currentProject) }}">
                  <i class="bi bi-plus-lg me-1"></i> Nueva tarea
                </a>
              @endif
            </div>

          </div>

          {{-- Tabs --}}
          @if($currentProject)
            @php
              $tabs = [
                'lista'   => ['Lista',   'bi-list-task'],
                'tablero' => ['Tablero', 'bi-kanban'],
                'tabla'   => ['Tabla',   'bi-table'],
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

        {{-- Content --}}
        <div class="gp-content p-3">
          @if(!$currentProject)
            <div class="text-sm gp-muted text-center py-5">
              Selecciona un proyecto del panel izquierdo.
            </div>
          @else
            @if($viewMode === 'lista')
              @include('tasks.views.lista', ['project' => $currentProject, 'statuses' => $statuses, 'tasksByStatus' => $tasksByStatus])
            @elseif($viewMode === 'tabla')
              @include('tasks.views.tabla', ['project' => $currentProject, 'statuses' => $statuses, 'tasksByStatus' => $tasksByStatus])
            @else
              @include('tasks.views.tablero', ['project' => $currentProject, 'statuses' => $statuses, 'tasksByStatus' => $tasksByStatus])
            @endif
          @endif
        </div>

      </div>
    </div>

  </div>
</div>

{{-- Modal: Nuevo Proyecto --}}
<div class="modal fade" id="modalNewProject" tabindex="-1" aria-hidden="true">
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

{{-- Modal: Nuevo Workspace --}}
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

@push('scripts')
<script>
  function toggleSidebarMini(){
    const sidebarCol = document.getElementById('gpSidebarCol');
    const mainCol    = document.getElementById('gpMainCol');
    const expanded   = document.getElementById('gpSidebarExpanded');
    const collapsed  = document.getElementById('gpSidebarCollapsed');
    const btn        = document.getElementById('btnSidebarCollapse');

    if(!sidebarCol || !mainCol || !expanded || !collapsed || !btn) return;

    const isMini = sidebarCol.classList.contains('col-auto');

    if(isMini){
      // Expandir
      sidebarCol.className = 'col-12 col-lg-3 col-xxl-3';
      mainCol.className    = 'col-12 col-lg-9 col-xxl-9';

      expanded.style.display  = 'block';
      collapsed.style.display = 'none';
      btn.textContent = '<<';
    }else{
      // Colapsar real (solo boton >>)
      sidebarCol.className = 'col-auto';
      mainCol.className    = 'col';

      expanded.style.display  = 'none';
      collapsed.style.display = 'block';
      btn.textContent = '>>';
    }
  }

  // Modal Nuevo Proyecto: set action dinámicamente
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
</script>
@endpush
@endsection
