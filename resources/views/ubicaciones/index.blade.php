@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
      <div class="text-muted small">Workspace</div>
      <h1 class="h3 mb-1">Ubicaciones de activos</h1>
      <div class="text-muted">{{ $workspace->name }}</div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('workspaces.centrales.index', $workspace) }}" class="btn btn-outline-secondary">Centrales</a>
      <a href="{{ route('workspaces.categorias.index', $workspace) }}" class="btn btn-outline-secondary">Categorías</a>
      <a href="{{ route('workspaces.activos.index', $workspace) }}" class="btn btn-outline-secondary">Activos</a>
      <a href="{{ route('workspaces.ubicaciones.create', $workspace) }}" class="btn btn-primary">Nueva ubicación</a>
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
          <input type="text" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Código o nombre">
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

        <div class="col-md-3">
          <label class="form-label">Tipo</label>
          <select name="tipo_ubicacion" class="form-select">
            <option value="">Todos</option>
            @foreach($tipoOptions as $item)
              <option value="{{ $item->codigo }}" @selected($filters['tipo_ubicacion'] === $item->codigo)>{{ $item->valor }}</option>
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
            <th>Tipo</th>
            <th>Padre</th>
            <th>Activos</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($ubicaciones as $ubicacion)
            <tr>
              <td class="fw-semibold">{{ $ubicacion->codigo }}</td>
              <td>{{ $ubicacion->nombre }}</td>
              <td>{{ $ubicacion->central?->nombre ?? 'Sin central' }}</td>
              <td>{{ $ubicacion->tipo_ubicacion }}</td>
              <td>{{ $ubicacion->parent?->nombre ?? 'Sin padre' }}</td>
              <td>{{ $ubicacion->activos_count }}</td>
              <td class="text-end">
                <a href="{{ route('workspaces.ubicaciones.show', [$workspace, $ubicacion]) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                <a href="{{ route('workspaces.ubicaciones.edit', [$workspace, $ubicacion]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">No hay ubicaciones registradas.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $ubicaciones->links() }}
  </div>
</div>
@endsection
