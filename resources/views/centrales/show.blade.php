@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
      <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
      <h1 class="h3 mb-1">{{ $central->nombre }}</h1>
      <div class="text-muted">{{ $central->codigo }} | {{ $central->tipo_central }}</div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('workspaces.ubicaciones.index', $workspace) }}" class="btn btn-outline-secondary">Ubicaciones</a>
      <a href="{{ route('workspaces.centrales.edit', [$workspace, $central]) }}" class="btn btn-primary">Editar</a>
      <a href="{{ route('workspaces.centrales.index', $workspace) }}" class="btn btn-outline-secondary">Volver</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card shadow-sm mb-4">
        <div class="card-header">Ficha de la central</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6"><strong>Tipo:</strong> {{ $central->tipo_central }}</div>
            <div class="col-md-6"><strong>Capacidad:</strong> {{ $central->capacidad_mw ?: '0' }} MW</div>
            <div class="col-md-6"><strong>Empresa operadora:</strong> {{ $central->empresa_operadora ?: 'N/D' }}</div>
            <div class="col-md-6"><strong>Estado:</strong> {{ $central->activo ? 'Habilitada' : 'Deshabilitada' }}</div>
            <div class="col-12"><strong>Ubicación de referencia:</strong> {{ $central->ubicacion_referencia ?: 'N/D' }}</div>
            <div class="col-12"><strong>Descripción:</strong><br>{{ $central->descripcion ?: 'Sin descripción' }}</div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Ubicaciones asociadas</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead class="table-light">
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @forelse($central->ubicaciones as $ubicacion)
                <tr>
                  <td>{{ $ubicacion->codigo }}</td>
                  <td>{{ $ubicacion->nombre }}</td>
                  <td>{{ $ubicacion->tipo_ubicacion }}</td>
                  <td class="text-end">
                    <a href="{{ route('workspaces.ubicaciones.show', [$workspace, $ubicacion]) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">No hay ubicaciones asociadas.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm mb-4">
        <div class="card-header">Activos recientes</div>
        <div class="card-body">
          @forelse($central->activos as $activo)
            <div class="mb-2">
              <a href="{{ route('workspaces.activos.show', [$workspace, $activo]) }}">{{ $activo->nombre }}</a>
            </div>
          @empty
            <div class="text-muted">No hay activos registrados.</div>
          @endforelse
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Acciones</div>
        <div class="card-body">
          <form method="POST" action="{{ route('workspaces.centrales.destroy', [$workspace, $central]) }}" onsubmit="return confirm('¿Eliminar esta central? Esto eliminará sus ubicaciones y activos asociados.');">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger w-100">Eliminar central</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
