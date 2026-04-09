@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
      <h1 class="h3 mb-1">Editar categoría</h1>
      <div class="text-muted">{{ $categoria->nombre }} ({{ $categoria->codigo }})</div>
    </div>

    <a href="{{ route('workspaces.categorias.show', [$workspace, $categoria]) }}" class="btn btn-outline-secondary">Ver detalle</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('workspaces.categorias.update', [$workspace, $categoria]) }}">
        @method('PUT')
        @include('categorias._form', ['submitLabel' => 'Actualizar categoría'])
      </form>
    </div>
  </div>
</div>
@endsection
