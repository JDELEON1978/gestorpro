@csrf

<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label">Central</label>
    <select name="central_id" class="form-select" required>
      <option value="">Seleccione...</option>
      @foreach($centrales as $central)
        <option value="{{ $central->id }}" @selected((int) old('central_id', $ubicacion->central_id) === $central->id)>{{ $central->nombre }}</option>
      @endforeach
    </select>
    @error('central_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Código</label>
    <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $ubicacion->codigo) }}" required>
    @error('codigo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Tipo de ubicación</label>
    <select name="tipo_ubicacion" class="form-select" required>
      @foreach($tipoOptions as $item)
        <option value="{{ $item->codigo }}" @selected(old('tipo_ubicacion', $ubicacion->tipo_ubicacion) === $item->codigo)>{{ $item->valor }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-8">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $ubicacion->nombre) }}" required>
    @error('nombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Ubicación padre</label>
    <select name="parent_id" class="form-select">
      <option value="">Sin padre</option>
      @foreach($ubicacionesPadre as $padre)
        <option value="{{ $padre->id }}" @selected((int) old('parent_id', $ubicacion->parent_id) === $padre->id)>{{ $padre->nombre }}</option>
      @endforeach
    </select>
    @error('parent_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-12">
    <label class="form-label">Descripción</label>
    <textarea name="descripcion" class="form-control" rows="4">{{ old('descripcion', $ubicacion->descripcion) }}</textarea>
  </div>

  <div class="col-12">
    <div class="form-check">
      <input type="checkbox" name="activo" value="1" class="form-check-input" id="ubicacion_activa_check" @checked(old('activo', $ubicacion->activo ?? true))>
      <label class="form-check-label" for="ubicacion_activa_check">Ubicación habilitada</label>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between mt-4">
  <a href="{{ route('workspaces.ubicaciones.index', $workspace) }}" class="btn btn-outline-secondary">Cancelar</a>
  <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
