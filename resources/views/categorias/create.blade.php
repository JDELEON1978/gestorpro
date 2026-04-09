@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="mb-4">
    <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
    <h1 class="h3 mb-1">Nueva categoría</h1>
    <div class="text-muted">Define un tipo de activo para clasificar equipos, componentes o sistemas.</div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('workspaces.categorias.store', $workspace) }}">
        @include('categorias._form', ['submitLabel' => 'Guardar categoría'])
      </form>
    </div>
  </div>
</div>
@endsection
