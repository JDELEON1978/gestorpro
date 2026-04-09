@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
      <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
      <h1 class="h3 mb-1">{{ $categoria->nombre }}</h1>
      <div class="text-muted">{{ $categoria->codigo }} | {{ $claseOptions[$categoria->clase_activo] ?? $categoria->clase_activo }}</div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('workspaces.activos.index', $workspace) }}" class="btn btn-outline-secondary">Activos</a>
      <a href="{{ route('workspaces.categorias.edit', [$workspace, $categoria]) }}" class="btn btn-primary">Editar</a>
      <a href="{{ route('workspaces.categorias.index', $workspace) }}" class="btn btn-outline-secondary">Volver</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card shadow-sm mb-4">
        <div class="card-header">Ficha de la categoría</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6"><strong>Clase:</strong> {{ $claseOptions[$categoria->clase_activo] ?? $categoria->clase_activo }}</div>
            <div class="col-md-6"><strong>Padre:</strong> {{ $categoria->parent?->nombre ?? 'Sin padre' }}</div>
            <div class="col-md-6"><strong>Vida útil:</strong> {{ $categoria->vida_util_anios ? $categoria->vida_util_anios.' años' : 'N/D' }}</div>
            <div class="col-md-6"><strong>Requiere serie:</strong> {{ $categoria->requiere_serie ? 'Sí' : 'No' }}</div>
            <div class="col-md-6"><strong>Estado:</strong> {{ $categoria->activo ? 'Habilitada' : 'Deshabilitada' }}</div>
            <div class="col-12"><strong>Descripción:</strong><br>{{ $categoria->descripcion ?: 'Sin descripción' }}</div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Activos asociados</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead class="table-light">
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @forelse($categoria->activos as $activo)
                <tr>
                  <td>{{ $activo->codigo }}</td>
                  <td>{{ $activo->nombre }}</td>
                  <td>{{ $activo->estado_operativo }}</td>
                  <td class="text-end">
                    <a href="{{ route('workspaces.activos.show', [$workspace, $activo]) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">No hay activos en esta categoría.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm mb-4">
        <div class="card-header">Subcategorías</div>
        <div class="card-body">
          @forelse($categoria->children as $child)
            <div class="mb-2">
              <a href="{{ route('workspaces.categorias.show', [$workspace, $child]) }}">{{ $child->nombre }}</a>
            </div>
          @empty
            <div class="text-muted">No hay subcategorías.</div>
          @endforelse
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Acciones</div>
        <div class="card-body">
          <form method="POST" action="{{ route('workspaces.categorias.destroy', [$workspace, $categoria]) }}" onsubmit="return confirm('¿Eliminar esta categoría?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger w-100">Eliminar categoría</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
