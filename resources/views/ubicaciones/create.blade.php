@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="mb-4">
    <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
    <h1 class="h3 mb-1">Nueva ubicación</h1>
    <div class="text-muted">Define un área, sistema o posición donde se instalarán activos.</div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('workspaces.ubicaciones.store', $workspace) }}">
        @include('ubicaciones._form', ['submitLabel' => 'Guardar ubicación'])
      </form>
    </div>
  </div>
</div>
@endsection
