@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
      <div class="text-muted small">Workspace</div>
      <h1 class="h3 mb-1">Historial de eventos de activos</h1>
      <div class="text-muted">{{ $workspace->name }}</div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('workspaces.activos.index', $workspace) }}" class="btn btn-outline-secondary">Activos</a>
      <a href="{{ route('workspaces.centrales.index', $workspace) }}" class="btn btn-outline-secondary">Centrales</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Activo</label>
          <select name="activo_id" class="form-select">
            <option value="">Todos</option>
            @foreach($activos as $activo)
              <option value="{{ $activo->id }}" @selected((string) $filters['activo_id'] === (string) $activo->id)>{{ $activo->nombre }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Tipo</label>
          <select name="tipo_evento" class="form-select">
            <option value="">Todos</option>
            @foreach($tipoEventoOptions as $item)
              <option value="{{ $item->codigo }}" @selected($filters['tipo_evento'] === $item->codigo)>{{ $item->valor }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Resultado</label>
          <select name="resultado" class="form-select">
            <option value="">Todos</option>
            @foreach($resultadoEventoOptions as $item)
              <option value="{{ $item->codigo }}" @selected($filters['resultado'] === $item->codigo)>{{ $item->valor }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Fecha desde</label>
          <input type="date" name="fecha_desde" class="form-control" value="{{ $filters['fecha_desde'] }}">
        </div>

        <div class="col-md-2">
          <label class="form-label">Fecha hasta</label>
          <input type="date" name="fecha_hasta" class="form-control" value="{{ $filters['fecha_hasta'] }}">
        </div>

        <div class="col-md-1">
          <label class="form-label">Solo</label>
          <select name="solo" class="form-select">
            <option value="">Todos</option>
            <option value="mantenimiento" @selected($filters['solo'] === 'mantenimiento')>Mant.</option>
            <option value="fallas" @selected($filters['solo'] === 'fallas')>Fallas</option>
          </select>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('workspaces.eventos.index', $workspace) }}" class="btn btn-outline-secondary">Limpiar</a>
          <button class="btn btn-primary">Filtrar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Fecha</th>
            <th>Activo</th>
            <th>Central</th>
            <th>Tipo</th>
            <th>Resultado</th>
            <th>Lectura</th>
            <th>Costo</th>
            <th>Usuario</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($eventos as $evento)
            <tr>
              <td>{{ optional($evento->fecha_evento)->format('Y-m-d H:i') }}</td>
              <td>{{ $evento->activo?->nombre ?? 'N/D' }}</td>
              <td>{{ $evento->activo?->central?->nombre ?? 'N/D' }}</td>
              <td>{{ $evento->tipo_evento }}</td>
              <td>{{ $evento->resultado ?: 'N/D' }}</td>
              <td>
                @if(!is_null($evento->valor_medicion))
                  {{ $evento->valor_medicion }} {{ $evento->unidad_medicion }}
                @elseif(!is_null($evento->horas_operacion))
                  {{ $evento->horas_operacion }} h
                @else
                  N/D
                @endif
              </td>
              <td>{{ $evento->costo ?: '0' }}</td>
              <td>{{ $evento->user?->name ?? 'Sistema' }}</td>
              <td class="text-end">
                <a href="{{ route('workspaces.activos.show', [$workspace, $evento->activo]) }}" class="btn btn-sm btn-outline-secondary">Activo</a>
                <a href="{{ route('workspaces.activos.eventos.edit', [$workspace, $evento->activo, $evento]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-4">No hay eventos que coincidan con los filtros.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $eventos->links() }}
  </div>
</div>
@endsection
