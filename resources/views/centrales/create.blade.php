@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="mb-4">
    <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
    <h1 class="h3 mb-1">Nueva central</h1>
    <div class="text-muted">Registra una planta, subestación o unidad de generación.</div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('workspaces.centrales.store', $workspace) }}">
        @include('centrales._form', ['submitLabel' => 'Guardar central'])
      </form>
    </div>
  </div>
</div>
@endsection
