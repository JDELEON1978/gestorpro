@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
      <h1 class="h3 mb-1">Editar contacto del activo</h1>
      <div class="text-muted">{{ $activo->nombre }} | {{ $contacto->nombre }}</div>
    </div>

    <a href="{{ route('workspaces.activos.show', [$workspace, $activo]) }}" class="btn btn-outline-secondary">Volver al activo</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('workspaces.activos.contactos.update', [$workspace, $activo, $contacto]) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Tipo de contacto</label>
            <select name="tipo_contacto" class="form-select" required>
              @foreach(['GENERAL' => 'General', 'PROVEEDOR' => 'Proveedor', 'INSTALACION' => 'Instalación', 'MANTENIMIENTO' => 'Mantenimiento', 'OPERACION' => 'Operación', 'EMERGENCIA' => 'Emergencia'] as $key => $label)
                <option value="{{ $key }}" @selected(old('tipo_contacto', $contacto->tipo_contacto) === $key)>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-8">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $contacto->nombre) }}" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Cargo</label>
            <input type="text" name="cargo" class="form-control" value="{{ old('cargo', $contacto->cargo) }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Empresa</label>
            <input type="text" name="empresa" class="form-control" value="{{ old('empresa', $contacto->empresa) }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $contacto->telefono) }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $contacto->email) }}">
          </div>

          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check">
              <input type="checkbox" name="principal" value="1" class="form-check-input" id="contacto_principal_edit" @checked(old('principal', $contacto->principal))>
              <label class="form-check-label" for="contacto_principal_edit">Contacto principal</label>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Notas</label>
            <textarea name="notas" class="form-control" rows="3">{{ old('notas', $contacto->notas) }}</textarea>
          </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
          <a href="{{ route('workspaces.activos.show', [$workspace, $activo]) }}" class="btn btn-outline-secondary">Cancelar</a>
          <button class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
