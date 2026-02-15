@extends('layouts.app')

@section('content')
<div class="container py-4">

  <div class="gp-panel p-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <div class="gp-h1 mb-0">Nueva tarea</div>
        <div class="gp-muted text-sm">{{ $project->name }}</div>
      </div>

      <a href="{{ route('dashboard', ['project_id' => $project->id, 'view' => 'tablero']) }}"
         class="btn gp-btn">
        Volver
      </a>
    </div>

    <form method="POST" action="{{ route('projects.tasks.store', $project) }}">
      @csrf

      <div class="mb-3">
        <label class="form-label fw-semibold">Título</label>
        <input name="title" class="form-control" value="{{ old('title') }}" required>
        @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Descripción</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Columna</label>
          <select name="status_id" class="form-select" required>
            @foreach($statuses as $st)
              <option value="{{ $st->id }}"
                @selected((int)old('status_id', $preStatusId) === (int)$st->id)>
                {{ $st->name }}
              </option>
            @endforeach
          </select>
          @error('status_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Prioridad</label>
          <select name="priority" class="form-select">
            <option value="">—</option>
            @for($i=1; $i<=5; $i++)
              <option value="{{ $i }}" @selected((int)old('priority') === $i)>P{{ $i }}</option>
            @endfor
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Vence</label>
          <input type="date" name="due_at" class="form-control" value="{{ old('due_at') }}">
        </div>
      </div>

      <div class="mt-4 d-flex gap-2">
        <button class="btn gp-btn-primary" type="submit">
          Guardar
        </button>
        <a class="btn gp-btn"
           href="{{ route('dashboard', ['project_id' => $project->id, 'view' => 'tablero']) }}">
          Cancelar
        </a>
      </div>
    </form>
  </div>

</div>
@endsection
