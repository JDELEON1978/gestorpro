@csrf

<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label">Código</label>
    <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $categoria->codigo) }}" required>
    @error('codigo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-8">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $categoria->nombre) }}" required>
    @error('nombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Clase de activo</label>
    <select name="clase_activo" class="form-select" required>
      @foreach($claseOptions as $key => $label)
        <option value="{{ $key }}" @selected(old('clase_activo', $categoria->clase_activo) === $key)>{{ $label }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label">Vida útil estimada (años)</label>
    <input type="number" min="1" max="100" name="vida_util_anios" class="form-control" value="{{ old('vida_util_anios', $categoria->vida_util_anios) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Categoría padre</label>
    <select name="parent_id" class="form-select">
      <option value="">Sin padre</option>
      @foreach($categoriasPadre as $padre)
        <option value="{{ $padre->id }}" @selected((int) old('parent_id', $categoria->parent_id) === $padre->id)>{{ $padre->nombre }}</option>
      @endforeach
    </select>
    @error('parent_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-12">
    <label class="form-label">Descripción</label>
    <textarea name="descripcion" class="form-control" rows="4">{{ old('descripcion', $categoria->descripcion) }}</textarea>
  </div>

  <div class="col-md-6">
    <div class="form-check">
      <input type="checkbox" name="requiere_serie" value="1" class="form-check-input" id="categoria_requiere_serie" @checked(old('requiere_serie', $categoria->requiere_serie ?? false))>
      <label class="form-check-label" for="categoria_requiere_serie">Requiere número de serie</label>
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-check">
      <input type="checkbox" name="activo" value="1" class="form-check-input" id="categoria_activa_check" @checked(old('activo', $categoria->activo ?? true))>
      <label class="form-check-label" for="categoria_activa_check">Categoría habilitada</label>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between mt-4">
  <a href="{{ route('workspaces.categorias.index', $workspace) }}" class="btn btn-outline-secondary">Cancelar</a>
  <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
