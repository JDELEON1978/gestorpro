@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
      <h1 class="h3 mb-1">Editar activo</h1>
      <div class="text-muted">{{ $activo->nombre }} ({{ $activo->codigo }})</div>
    </div>

    <a href="{{ route('workspaces.activos.show', [$workspace, $activo]) }}" class="btn btn-outline-secondary">Ver detalle</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('workspaces.activos.update', [$workspace, $activo]) }}">
        @method('PUT')
        @include('activos._form', ['submitLabel' => 'Actualizar activo'])
      </form>
    </div>
  </div>
</div>
@endsection
