@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
      <div class="text-muted small">Workspace</div>
      <h1 class="h3 mb-1">Centrales de generación</h1>
      <div class="text-muted">{{ $workspace->name }}</div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('workspaces.categorias.index', $workspace) }}" class="btn btn-outline-secondary">Categorías</a>
      <a href="{{ route('workspaces.activos.index', $workspace) }}" class="btn btn-outline-secondary">Activos</a>
      <a href="{{ route('workspaces.ubicaciones.index', $workspace) }}" class="btn btn-outline-secondary">Ubicaciones</a>
      <a href="{{ route('workspaces.centrales.create', $workspace) }}" class="btn btn-primary">Nueva central</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Buscar</label>
          <input type="text" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Código, nombre o empresa operadora">
        </div>

        <div class="col-md-3">
          <label class="form-label">Tipo</label>
          <select name="tipo_central" class="form-select">
            <option value="">Todos</option>
            @foreach($tipoOptions as $item)
              <option value="{{ $item->codigo }}" @selected($filters['tipo_central'] === $item->codigo)>{{ $item->valor }}</option>
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
            <th>Tipo</th>
            <th>Capacidad</th>
            <th>Ubicaciones</th>
            <th>Activos</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($centrales as $central)
            <tr>
              <td class="fw-semibold">{{ $central->codigo }}</td>
              <td>
                <div>{{ $central->nombre }}</div>
                <div class="small text-muted">{{ $central->empresa_operadora ?: 'Sin operadora' }}</div>
              </td>
              <td>{{ $central->tipo_central }}</td>
              <td>{{ $central->capacidad_mw ?: '0' }} MW</td>
              <td>{{ $central->ubicaciones_count }}</td>
              <td>{{ $central->activos_count }}</td>
              <td class="text-end">
                <a href="{{ route('workspaces.centrales.show', [$workspace, $central]) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                <a href="{{ route('workspaces.centrales.edit', [$workspace, $central]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">No hay centrales registradas.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $centrales->links() }}
  </div>
</div>
@endsection
