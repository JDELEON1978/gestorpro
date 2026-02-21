{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
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
  .gp-sidebar-mini-wrap{ width: 64px; }
  .task-clickable{ cursor:pointer; }
</style>

<div class="container-fluid py-3">
  <div class="row g-3 align-items-stretch">

    {{-- SIDEBAR --}}
    <div class="col-12 col-lg-3 col-xxl-3" id="gpSidebarCol">
      <div class="gp-panel h-100 d-flex flex-column">

        <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom" style="border-color: var(--border);">
          <div class="fw-bold">&nbsp;</div>

          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm gp-btn"
                    type="button"
                    title="Nuevo Workspace"
                    data-bs-toggle="modal"
                    data-bs-target="#modalNewWorkspace">
              <i class="bi bi-plus-lg"></i>
            </button>

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

    {{-- MAIN --}}
    <div class="col-12 col-lg-9 col-xxl-9" id="gpMainCol">
      <div class="gp-panel overflow-hidden">

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
            </div>

            <div class="d-flex align-items-center gap-2">
              @if($currentProject)

                {{-- Nueva tarea: MODAL --}}
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

        <div class="gp-content p-3">
          @if(!$currentProject)
            <div class="text-sm gp-muted text-center py-5">
              Selecciona un proyecto del panel izquierdo.
            </div>
          @else
            {{-- SOLO ESTA ÁREA SE ACTUALIZA POR AJAX --}}
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

{{-- Modal: Archivos de Tarea --}}
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


{{-- Modal: Crear/Editar Tarea --}}
<div class="modal fade" id="modalTask" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" id="formTask">
      @csrf
      <input type="hidden" id="task_id" value="">
      <input type="hidden" id="task_project_id" value="{{ $currentProject?->id ?? '' }}">

      <div class="modal-header">
        <h5 class="modal-title" id="taskModalTitle">Nueva tarea</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
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

        <div class="alert alert-danger mt-3 d-none" id="taskError"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn gp-btn" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn gp-btn-primary">Guardar</button>
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
  }

  // Nuevo Proyecto: set action dinámicamente
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

  // ====== MODAL TAREA (crear/editar) ======
  function openCreateTaskModal(){
    document.getElementById('taskModalTitle').textContent = 'Nueva tarea';
    document.getElementById('task_id').value = '';
    document.getElementById('task_title').value = '';
    document.getElementById('task_description').value = '';
    document.getElementById('task_priority').value = '';
    document.getElementById('task_start_at').value = '';
    document.getElementById('task_due_at').value = '';
    document.getElementById('taskError').classList.add('d-none');

    const st = document.getElementById('task_status_id');
    if(st && st.options.length) st.value = st.options[0].value;
  }

  function openEditTaskModal(task){
    document.getElementById('taskModalTitle').textContent = 'Editar tarea';
    document.getElementById('task_id').value = task.id || '';
    document.getElementById('task_title').value = task.title || '';
    document.getElementById('task_description').value = task.description || '';
    document.getElementById('task_status_id').value = task.status_id || '';
    document.getElementById('task_priority').value = (task.priority ?? '');
    document.getElementById('task_start_at').value = (task.start_at ?? '');
    document.getElementById('task_due_at').value = (task.due_at ?? '');
    document.getElementById('taskError').classList.add('d-none');

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTask')).show();
  }

// Delegación: click en cualquier elemento con .js-task abre editar
document.addEventListener('click', (e) => {

  // ✅ Si el click fue en el botón de archivos (o dentro), NO abrir editar
  if (e.target.closest('.js-open-files')) return;

  // ✅ Si el click fue en el modal de archivos, tampoco
  if (e.target.closest('#modalFiles')) return;

  // ✅ Si el click fue en cualquier control interactivo, no abrir editar
  // (evita conflictos futuros con otros botones/links)
  if (e.target.closest('button, a, input, select, textarea, label')) return;

  const el = e.target.closest('.js-task');
  if(!el) return;

  const raw = el.getAttribute('data-task');
  if(!raw) return;

  try{
    const task = JSON.parse(raw);
    openEditTaskModal(task);
  }catch(err){
    console.error('data-task inválido', err);
  }
});


  async function refreshProjectArea(){
    const projectId = document.getElementById('task_project_id')?.value;
    const viewMode = @json($viewMode ?? 'tablero');

    if(!projectId){
      return;
    }

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
      view: @json($viewMode ?? 'tablero')
    };

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    };
    if(token) headers['X-CSRF-TOKEN'] = token;

    let url, method;
    if(id){
      url = `/tasks/${id}`;
      method = 'PATCH';
    }else{
      url = `/projects/${projectId}/tasks`;
      method = 'POST';
    }

    try{
      const res = await fetch(url, { method, headers, body: JSON.stringify(payload) });

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
</script>

<script>
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

  async function openFilesModal(taskId){
    const modalEl = document.getElementById('modalFiles');
    const bodyEl  = document.getElementById('filesTableBody');
    const errEl   = document.getElementById('filesError');
    const titleEl = document.getElementById('filesTaskTitle');
    const hidden  = document.getElementById('files_task_id');

    errEl.classList.add('d-none');
    errEl.textContent = '';
    hidden.value = taskId;

    bodyEl.innerHTML = `<tr><td colspan="5" class="text-muted">Cargando...</td></tr>`;

    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    const res = await fetch(`/tasks/${taskId}/files`, { headers: csrfHeaders({ 'Accept':'application/json' }) });
    const data = await res.json();

    titleEl.textContent = data?.task?.title || `Tarea #${taskId}`;

    renderFilesTable(data.files || []);
  }

  function renderFilesTable(files){
    const bodyEl = document.getElementById('filesTableBody');

    if (!files.length){
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
          <button class="btn btn-sm gp-btn js-del-file"
                  data-task-id="${document.getElementById('files_task_id').value}"
                  data-file-id="${f.id}"
                  title="Eliminar">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
  }

  function escapeHtml(str){
    str = (str ?? '').toString();
    return str.replace(/[&<>"']/g, (m) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  // Click icono 📎
    document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-files');
    if(!btn) return;

    e.preventDefault();
    e.stopPropagation(); // ✅ evita que se dispare el click de editar tarea

    const taskId = btn.getAttribute('data-task-id');
    if(taskId) openFilesModal(taskId);
    });

  // Subir archivos
  document.getElementById('btnUploadFiles')?.addEventListener('click', async () => {
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
      headers: csrfHeaders(), // NO pongas Content-Type aquí (FormData)
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

    // limpiar input y recargar lista
    input.value = '';
    await openFilesModal(taskId);

    // opcional: refrescar área proyecto para contadores/íconos
    if (typeof refreshProjectArea === 'function') await refreshProjectArea();
  });

  // Eliminar archivo
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-del-file');
    if(!btn) return;

    const taskId = btn.getAttribute('data-task-id');
    const fileId = btn.getAttribute('data-file-id');
    if(!taskId || !fileId) return;

    if(!confirm('¿Eliminar este archivo?')) return;

    const res = await fetch(`/tasks/${taskId}/files/${fileId}`, {
      method: 'DELETE',
      headers: csrfHeaders({ 'Accept':'application/json' }),
    });

    if(!res.ok){
      alert('No se pudo eliminar.');
      return;
    }

    await openFilesModal(taskId);
    if (typeof refreshProjectArea === 'function') await refreshProjectArea();
  });
</script>

@endpush
@endsection
