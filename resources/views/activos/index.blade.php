@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
      <div class="text-muted small">Workspace</div>
      <h1 class="h3 mb-1">Activos empresariales</h1>
      <div class="text-muted">{{ $workspace->name }}</div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('workspaces.centrales.index', $workspace) }}" class="btn btn-outline-secondary">Centrales</a>
      <a href="{{ route('workspaces.ubicaciones.index', $workspace) }}" class="btn btn-outline-secondary">Ubicaciones</a>
      <a href="{{ route('workspaces.categorias.index', $workspace) }}" class="btn btn-outline-secondary">Categorías</a>
      <a href="{{ route('workspaces.activos.create', $workspace) }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nuevo activo
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-5">
          <label class="form-label">Buscar</label>
          <input type="text" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Código, nombre, tag o serie">
        </div>

        <div class="col-md-3">
          <label class="form-label">Estado</label>
          <select name="estado" class="form-select">
            <option value="">Todos</option>
            @foreach($estadoOptions as $item)
              <option value="{{ $item->codigo }}" @selected($filters['estado'] === $item->codigo)>{{ $item->valor }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Central</label>
          <select name="central_id" class="form-select">
            <option value="">Todas</option>
            @foreach($centrales as $central)
              <option value="{{ $central->id }}" @selected((string) $filters['central_id'] === (string) $central->id)>{{ $central->nombre }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-1 d-flex align-items-end">
          <button class="btn btn-outline-primary w-100">Filtrar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Central</th>
            <th>Categoría</th>
            <th>Estado</th>
            <th>Criticidad</th>
            <th>Responsable</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($activos as $activo)
            <tr>
              <td class="fw-semibold">{{ $activo->codigo }}</td>
              <td>
                <div>{{ $activo->nombre }}</div>
                <div class="small text-muted">{{ $activo->tag ?: 'Sin tag' }}</div>
              </td>
              <td>{{ $activo->central?->nombre ?? 'Sin central' }}</td>
              <td>{{ $activo->categoria?->nombre ?? 'Sin categoría' }}</td>
              <td>{{ $activo->estado_operativo }}</td>
              <td>{{ $activo->criticidad }}</td>
              <td>{{ $activo->responsable?->name ?? 'Sin asignar' }}</td>
              <td class="text-end">
                <a href="{{ route('workspaces.activos.show', [$workspace, $activo]) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                <a href="{{ route('workspaces.activos.edit', [$workspace, $activo]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">No hay activos registrados para este workspace.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $activos->links() }}
  </div>
</div>
@endsection
