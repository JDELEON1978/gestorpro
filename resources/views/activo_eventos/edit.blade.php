@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
      <h1 class="h3 mb-1">Editar evento de activo</h1>
      <div class="text-muted">{{ $activo->nombre }} | {{ $evento->tipo_evento }}</div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('workspaces.eventos.index', $workspace) }}" class="btn btn-outline-secondary">Historial</a>
      <a href="{{ route('workspaces.activos.show', [$workspace, $activo]) }}" class="btn btn-outline-secondary">Volver al activo</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('workspaces.activos.eventos.update', [$workspace, $activo, $evento]) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Tipo de evento</label>
            <select name="tipo_evento" class="form-select" required>
              @foreach($tipoEventoOptions as $item)
                <option value="{{ $item->codigo }}" @selected(old('tipo_evento', $evento->tipo_evento) === $item->codigo)>{{ $item->valor }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Fecha y hora</label>
            <input type="datetime-local" name="fecha_evento" class="form-control" value="{{ old('fecha_evento', optional($evento->fecha_evento)->format('Y-m-d\TH:i')) }}" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Resultado</label>
            <select name="resultado" class="form-select">
              <option value="">Sin resultado</option>
              @foreach($resultadoEventoOptions as $item)
                <option value="{{ $item->codigo }}" @selected(old('resultado', $evento->resultado) === $item->codigo)>{{ $item->valor }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Horas de operación</label>
            <input type="number" step="0.01" min="0" name="horas_operacion" class="form-control" value="{{ old('horas_operacion', $evento->horas_operacion) }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Valor medición</label>
            <input type="number" step="0.0001" name="valor_medicion" class="form-control" value="{{ old('valor_medicion', $evento->valor_medicion) }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Unidad</label>
            <input type="text" name="unidad_medicion" class="form-control" value="{{ old('unidad_medicion', $evento->unidad_medicion) }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Costo</label>
            <input type="number" step="0.01" min="0" name="costo" class="form-control" value="{{ old('costo', $evento->costo) }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Próximo evento programado</label>
            <input type="date" name="proximo_evento_programado" class="form-control" value="{{ old('proximo_evento_programado', optional($evento->proximo_evento_programado)->format('Y-m-d')) }}">
          </div>

          <div class="col-md-8">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $evento->descripcion) }}</textarea>
          </div>
        </div>

        @if($errors->any())
          <div class="alert alert-danger mt-3 mb-0">
            <ul class="mb-0">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="d-flex justify-content-between mt-4">
          <a href="{{ route('workspaces.activos.show', [$workspace, $activo]) }}" class="btn btn-outline-secondary">Cancelar</a>
          <button class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm mt-4">
    <div class="card-header">Evidencias del evento</div>
    <div class="card-body">
      <form method="POST" action="{{ route('workspaces.activos.eventos.evidencias.store', [$workspace, $activo, $evento]) }}" enctype="multipart/form-data" class="mb-4">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Archivo</label>
            <input type="file" name="archivo" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Descripción</label>
            <input type="text" name="descripcion" class="form-control" placeholder="Ej: foto de falla, orden de trabajo, medición">
          </div>
        </div>
        <div class="mt-3 d-flex justify-content-end">
          <button class="btn btn-primary">Cargar evidencia</button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Archivo</th>
              <th>Descripción</th>
              <th>Usuario</th>
              <th>Fecha</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($evento->evidencias->sortByDesc('id') as $evidencia)
              <tr>
                <td>{{ $evidencia->original_name }}</td>
                <td>{{ $evidencia->descripcion ?: 'Sin descripción' }}</td>
                <td>{{ $evidencia->user?->name ?? 'Sistema' }}</td>
                <td>{{ optional($evidencia->created_at)->format('Y-m-d H:i') }}</td>
                <td class="text-end">
                  <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('workspaces.activos.eventos.evidencias.download', [$workspace, $activo, $evento, $evidencia]) }}" class="btn btn-sm btn-outline-secondary">Descargar</a>
                    <form method="POST" action="{{ route('workspaces.activos.eventos.evidencias.destroy', [$workspace, $activo, $evento, $evidencia]) }}" onsubmit="return confirm('¿Eliminar esta evidencia?');">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-3">Sin evidencias cargadas.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
