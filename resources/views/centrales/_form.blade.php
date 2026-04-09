@csrf

<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label">Código</label>
    <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $central->codigo) }}" required>
    @error('codigo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-8">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $central->nombre) }}" required>
    @error('nombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Tipo de central</label>
    <select name="tipo_central" class="form-select" required>
      @foreach($tipoOptions as $item)
        <option value="{{ $item->codigo }}" @selected(old('tipo_central', $central->tipo_central) === $item->codigo)>{{ $item->valor }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label">Capacidad MW</label>
    <input type="number" step="0.01" min="0" name="capacidad_mw" class="form-control" value="{{ old('capacidad_mw', $central->capacidad_mw) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Empresa operadora</label>
    <input type="text" name="empresa_operadora" class="form-control" value="{{ old('empresa_operadora', $central->empresa_operadora) }}">
  </div>

  <div class="col-12">
    <label class="form-label">Ubicación de referencia</label>
    <input type="text" name="ubicacion_referencia" class="form-control" value="{{ old('ubicacion_referencia', $central->ubicacion_referencia) }}">
  </div>

  <div class="col-12">
    <label class="form-label">Descripción</label>
    <textarea name="descripcion" class="form-control" rows="4">{{ old('descripcion', $central->descripcion) }}</textarea>
  </div>

  <div class="col-12">
    <div class="form-check">
      <input type="checkbox" name="activo" value="1" class="form-check-input" id="central_activo_check" @checked(old('activo', $central->activo ?? true))>
      <label class="form-check-label" for="central_activo_check">Central habilitada</label>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between mt-4">
  <a href="{{ route('workspaces.centrales.index', $workspace) }}" class="btn btn-outline-secondary">Cancelar</a>
  <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
