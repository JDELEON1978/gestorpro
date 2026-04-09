@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
      <h1 class="h3 mb-1">Editar central</h1>
      <div class="text-muted">{{ $central->nombre }} ({{ $central->codigo }})</div>
    </div>

    <a href="{{ route('workspaces.centrales.show', [$workspace, $central]) }}" class="btn btn-outline-secondary">Ver detalle</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('workspaces.centrales.update', [$workspace, $central]) }}">
        @method('PUT')
        @include('centrales._form', ['submitLabel' => 'Actualizar central'])
      </form>
    </div>
  </div>
</div>
@endsection
