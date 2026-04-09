@csrf

<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label">Código</label>
    <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $activo->codigo) }}" required>
    @error('codigo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Tag</label>
    <input type="text" name="tag" class="form-control" value="{{ old('tag', $activo->tag) }}">
    @error('tag') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Estado operativo</label>
    <select name="estado_operativo" class="form-select" required>
      @foreach($estadoOptions as $item)
        <option value="{{ $item->codigo }}" @selected(old('estado_operativo', $activo->estado_operativo) === $item->codigo)>{{ $item->valor }}</option>
      @endforeach
    </select>
    @error('estado_operativo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-12">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $activo->nombre) }}" required>
    @error('nombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Central</label>
    <select name="central_id" class="form-select" required>
      <option value="">Seleccione...</option>
      @foreach($centrales as $central)
        <option value="{{ $central->id }}" @selected((int) old('central_id', $activo->central_id) === $central->id)>{{ $central->nombre }}</option>
      @endforeach
    </select>
    @error('central_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Categoría</label>
    <select name="categoria_id" class="form-select" required>
      <option value="">Seleccione...</option>
      @foreach($categorias as $categoria)
        <option value="{{ $categoria->id }}" @selected((int) old('categoria_id', $activo->categoria_id) === $categoria->id)>{{ $categoria->nombre }}</option>
      @endforeach
    </select>
    @error('categoria_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Ubicación</label>
    <select name="ubicacion_id" class="form-select">
      <option value="">Sin ubicación</option>
      @foreach($ubicaciones as $ubicacion)
        <option value="{{ $ubicacion->id }}" @selected((int) old('ubicacion_id', $activo->ubicacion_id) === $ubicacion->id)>{{ $ubicacion->nombre }}</option>
      @endforeach
    </select>
    @error('ubicacion_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Criticidad</label>
    <select name="criticidad" class="form-select" required>
      @foreach($criticidadOptions as $item)
        <option value="{{ $item->codigo }}" @selected(old('criticidad', $activo->criticidad) === $item->codigo)>{{ $item->valor }}</option>
      @endforeach
    </select>
    @error('criticidad') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Responsable</label>
    <select name="responsable_user_id" class="form-select">
      <option value="">Sin asignar</option>
      @foreach($responsables as $responsable)
        <option value="{{ $responsable->id }}" @selected((int) old('responsable_user_id', $activo->responsable_user_id) === $responsable->id)>{{ $responsable->name }}</option>
      @endforeach
    </select>
    @error('responsable_user_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Activo padre</label>
    <select name="parent_id" class="form-select">
      <option value="">Sin padre</option>
      @foreach($activosPadre as $padre)
        <option value="{{ $padre->id }}" @selected((int) old('parent_id', $activo->parent_id) === $padre->id)>{{ $padre->nombre }}</option>
      @endforeach
    </select>
    @error('parent_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Fabricante</label>
    <input type="text" name="fabricante" class="form-control" value="{{ old('fabricante', $activo->fabricante) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Modelo</label>
    <input type="text" name="modelo" class="form-control" value="{{ old('modelo', $activo->modelo) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Número de serie</label>
    <input type="text" name="numero_serie" class="form-control" value="{{ old('numero_serie', $activo->numero_serie) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Tipo de combustible</label>
    <input type="text" name="tipo_combustible" class="form-control" value="{{ old('tipo_combustible', $activo->tipo_combustible) }}">
  </div>

  <div class="col-md-8">
    <label class="form-label">Proveedor que instaló</label>
    <input type="text" name="proveedor_instalador" class="form-control" value="{{ old('proveedor_instalador', $activo->proveedor_instalador) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Fecha de fabricación</label>
    <input type="date" name="fecha_fabricacion" class="form-control" value="{{ old('fecha_fabricacion', optional($activo->fecha_fabricacion)->format('Y-m-d')) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Fecha de instalación</label>
    <input type="date" name="fecha_instalacion" class="form-control" value="{{ old('fecha_instalacion', optional($activo->fecha_instalacion)->format('Y-m-d')) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Fecha de puesta en servicio</label>
    <input type="date" name="fecha_puesta_servicio" class="form-control" value="{{ old('fecha_puesta_servicio', optional($activo->fecha_puesta_servicio)->format('Y-m-d')) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Potencia nominal kW</label>
    <input type="number" step="0.01" name="potencia_nominal_kw" class="form-control" value="{{ old('potencia_nominal_kw', $activo->potencia_nominal_kw) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Voltaje nominal V</label>
    <input type="number" step="0.01" name="voltaje_nominal_v" class="form-control" value="{{ old('voltaje_nominal_v', $activo->voltaje_nominal_v) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Corriente nominal A</label>
    <input type="number" step="0.01" name="corriente_nominal_a" class="form-control" value="{{ old('corriente_nominal_a', $activo->corriente_nominal_a) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Horas de operación</label>
    <input type="number" step="0.01" min="0" name="horas_operacion" class="form-control" value="{{ old('horas_operacion', $activo->horas_operacion ?? 0) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Costo de adquisición</label>
    <input type="number" step="0.01" min="0" name="costo_adquisicion" class="form-control" value="{{ old('costo_adquisicion', $activo->costo_adquisicion) }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Valor en libros</label>
    <input type="number" step="0.01" min="0" name="valor_libros" class="form-control" value="{{ old('valor_libros', $activo->valor_libros) }}">
  </div>

  <div class="col-12">
    <label class="form-label">Descripción</label>
    <textarea name="descripcion" class="form-control" rows="4">{{ old('descripcion', $activo->descripcion) }}</textarea>
  </div>

  <div class="col-12">
    <div class="form-check">
      <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo_check" @checked(old('activo', $activo->activo ?? true))>
      <label class="form-check-label" for="activo_check">Activo habilitado</label>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between mt-4">
  <a href="{{ route('workspaces.activos.index', $workspace) }}" class="btn btn-outline-secondary">Cancelar</a>
  <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
