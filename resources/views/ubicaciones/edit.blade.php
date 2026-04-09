@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
      <h1 class="h3 mb-1">Editar ubicación</h1>
      <div class="text-muted">{{ $ubicacion->nombre }} ({{ $ubicacion->codigo }})</div>
    </div>

    <a href="{{ route('workspaces.ubicaciones.show', [$workspace, $ubicacion]) }}" class="btn btn-outline-secondary">Ver detalle</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('workspaces.ubicaciones.update', [$workspace, $ubicacion]) }}">
        @method('PUT')
        @include('ubicaciones._form', ['submitLabel' => 'Actualizar ubicación'])
      </form>
    </div>
  </div>
</div>
@endsection
