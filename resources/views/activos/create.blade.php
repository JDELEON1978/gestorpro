@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="mb-4">
    <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
    <h1 class="h3 mb-1">Nuevo activo</h1>
    <div class="text-muted">Completa la información principal del equipo o componente.</div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('workspaces.activos.store', $workspace) }}">
        @include('activos._form', ['submitLabel' => 'Guardar activo'])
      </form>
    </div>
  </div>
</div>
@endsection
