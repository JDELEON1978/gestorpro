@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
      <div class="text-muted small">Workspace</div>
      <h1 class="h3 mb-1">Categorías de activos</h1>
      <div class="text-muted">{{ $workspace->name }}</div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('workspaces.centrales.index', $workspace) }}" class="btn btn-outline-secondary">Centrales</a>
      <a href="{{ route('workspaces.ubicaciones.index', $workspace) }}" class="btn btn-outline-secondary">Ubicaciones</a>
      <a href="{{ route('workspaces.activos.index', $workspace) }}" class="btn btn-outline-secondary">Activos</a>
      <a href="{{ route('workspaces.categorias.create', $workspace) }}" class="btn btn-primary">Nueva categoría</a>
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
          <input type="text" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Código o nombre">
        </div>

        <div class="col-md-3">
          <label class="form-label">Clase</label>
          <select name="clase_activo" class="form-select">
            <option value="">Todas</option>
            @foreach($claseOptions as $key => $label)
              <option value="{{ $key }}" @selected($filters['clase_activo'] === $key)>{{ $label }}</option>
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
            <th>Clase</th>
            <th>Padre</th>
            <th>Hijos</th>
            <th>Activos</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($categorias as $categoria)
            <tr>
              <td class="fw-semibold">{{ $categoria->codigo }}</td>
              <td>
                <div>{{ $categoria->nombre }}</div>
                <div class="small text-muted">{{ $categoria->vida_util_anios ? $categoria->vida_util_anios.' años' : 'Sin vida útil definida' }}</div>
              </td>
              <td>{{ $claseOptions[$categoria->clase_activo] ?? $categoria->clase_activo }}</td>
              <td>{{ $categoria->parent?->nombre ?? 'Sin padre' }}</td>
              <td>{{ $categoria->children_count }}</td>
              <td>{{ $categoria->activos_count }}</td>
              <td class="text-end">
                <a href="{{ route('workspaces.categorias.show', [$workspace, $categoria]) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                <a href="{{ route('workspaces.categorias.edit', [$workspace, $categoria]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">No hay categorías registradas.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $categorias->links() }}
  </div>
</div>
@endsection
