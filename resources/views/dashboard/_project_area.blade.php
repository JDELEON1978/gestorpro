{{-- resources/views/dashboard/_project_area.blade.php --}}

@if(!empty($justCreated))
  <div class="alert alert-success py-2 mb-3">
    Tarea creada.
  </div>
@endif

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
