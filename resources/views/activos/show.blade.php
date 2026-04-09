@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
      <div class="text-muted small">Workspace: {{ $workspace->name }}</div>
      <h1 class="h3 mb-1">{{ $activo->nombre }}</h1>
      <div class="text-muted">{{ $activo->codigo }} @if($activo->tag) | {{ $activo->tag }} @endif</div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('workspaces.eventos.index', $workspace) }}" class="btn btn-outline-secondary">Historial</a>
      <a href="{{ route('workspaces.activos.edit', [$workspace, $activo]) }}" class="btn btn-primary">Editar</a>
      <a href="{{ route('workspaces.activos.index', $workspace) }}" class="btn btn-outline-secondary">Volver</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card shadow-sm mb-4">
        <div class="card-header">Ficha del activo</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6"><strong>Central:</strong> {{ $activo->central?->nombre ?? 'Sin central' }}</div>
            <div class="col-md-6"><strong>Ubicación:</strong> {{ $activo->ubicacion?->nombre ?? 'Sin ubicación' }}</div>
            <div class="col-md-6"><strong>Categoría:</strong> {{ $activo->categoria?->nombre ?? 'Sin categoría' }}</div>
            <div class="col-md-6"><strong>Activo padre:</strong> {{ $activo->parent?->nombre ?? 'Sin padre' }}</div>
            <div class="col-md-6"><strong>Estado operativo:</strong> {{ $activo->estado_operativo }}</div>
            <div class="col-md-6"><strong>Criticidad:</strong> {{ $activo->criticidad }}</div>
            <div class="col-md-6"><strong>Responsable:</strong> {{ $activo->responsable?->name ?? 'Sin asignar' }}</div>
            <div class="col-md-6"><strong>Serie:</strong> {{ $activo->numero_serie ?: 'N/D' }}</div>
            <div class="col-md-6"><strong>Proveedor instalador:</strong> {{ $activo->proveedor_instalador ?: 'N/D' }}</div>
            <div class="col-md-6"><strong>Fabricante:</strong> {{ $activo->fabricante ?: 'N/D' }}</div>
            <div class="col-md-6"><strong>Modelo:</strong> {{ $activo->modelo ?: 'N/D' }}</div>
            <div class="col-md-4"><strong>Potencia:</strong> {{ $activo->potencia_nominal_kw ?: '0' }} kW</div>
            <div class="col-md-4"><strong>Voltaje:</strong> {{ $activo->voltaje_nominal_v ?: '0' }} V</div>
            <div class="col-md-4"><strong>Corriente:</strong> {{ $activo->corriente_nominal_a ?: '0' }} A</div>
            <div class="col-md-4"><strong>Horas operación:</strong> {{ $activo->horas_operacion }}</div>
            <div class="col-md-4"><strong>Costo adquisición:</strong> {{ $activo->costo_adquisicion ?: '0' }}</div>
            <div class="col-md-4"><strong>Valor libros:</strong> {{ $activo->valor_libros ?: '0' }}</div>
            <div class="col-md-4"><strong>Fabricación:</strong> {{ optional($activo->fecha_fabricacion)->format('Y-m-d') ?: 'N/D' }}</div>
            <div class="col-md-4"><strong>Instalación:</strong> {{ optional($activo->fecha_instalacion)->format('Y-m-d') ?: 'N/D' }}</div>
            <div class="col-md-4"><strong>Puesta en servicio:</strong> {{ optional($activo->fecha_puesta_servicio)->format('Y-m-d') ?: 'N/D' }}</div>
            <div class="col-12"><strong>Descripción:</strong><br>{{ $activo->descripcion ?: 'Sin descripción' }}</div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Eventos registrados</span>
          <span class="small text-muted">{{ $activo->eventos->count() }} registros</span>
        </div>

        <div class="card-body border-bottom">
          <form method="POST" action="{{ route('workspaces.activos.eventos.store', [$workspace, $activo]) }}">
            @csrf
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Tipo de evento</label>
                <select name="tipo_evento" class="form-select" required>
                  <option value="">Seleccione...</option>
                  @foreach($tipoEventoOptions as $item)
                    <option value="{{ $item->codigo }}" @selected(old('tipo_evento') === $item->codigo)>{{ $item->valor }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Fecha y hora</label>
                <input type="datetime-local" name="fecha_evento" class="form-control" value="{{ old('fecha_evento') ? \Illuminate\Support\Carbon::parse(old('fecha_evento'))->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Resultado</label>
                <select name="resultado" class="form-select">
                  <option value="">Sin resultado</option>
                  @foreach($resultadoEventoOptions as $item)
                    <option value="{{ $item->codigo }}" @selected(old('resultado') === $item->codigo)>{{ $item->valor }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label">Horas de operación</label>
                <input type="number" step="0.01" min="0" name="horas_operacion" class="form-control" value="{{ old('horas_operacion', $activo->horas_operacion) }}">
              </div>

              <div class="col-md-3">
                <label class="form-label">Valor medición</label>
                <input type="number" step="0.0001" name="valor_medicion" class="form-control" value="{{ old('valor_medicion') }}">
              </div>

              <div class="col-md-3">
                <label class="form-label">Unidad</label>
                <input type="text" name="unidad_medicion" class="form-control" value="{{ old('unidad_medicion') }}" placeholder="psi, °C, V">
              </div>

              <div class="col-md-3">
                <label class="form-label">Costo</label>
                <input type="number" step="0.01" min="0" name="costo" class="form-control" value="{{ old('costo') }}">
              </div>

              <div class="col-md-4">
                <label class="form-label">Próximo evento programado</label>
                <input type="date" name="proximo_evento_programado" class="form-control" value="{{ old('proximo_evento_programado') }}">
              </div>

              <div class="col-md-8">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="2" placeholder="Detalle del hallazgo, mantenimiento o lectura">{{ old('descripcion') }}</textarea>
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

            <div class="mt-3 d-flex justify-content-end">
              <button class="btn btn-primary">Registrar evento</button>
            </div>
          </form>
        </div>

        <div class="table-responsive">
          <table class="table mb-0">
            <thead class="table-light">
              <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Resultado</th>
                <th>Lectura</th>
                <th>Detalle</th>
                <th>Usuario</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @forelse($activo->eventos->sortByDesc('fecha_evento') as $evento)
                <tr>
                  <td>{{ optional($evento->fecha_evento)->format('Y-m-d H:i') }}</td>
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
                  <td>{{ $evento->descripcion ?: 'Sin detalle' }}</td>
                  <td>{{ $evento->user?->name ?? 'Sistema' }}</td>
                  <td class="text-end">
                    <div class="d-flex justify-content-end gap-2">
                      <a href="{{ route('workspaces.activos.eventos.edit', [$workspace, $activo, $evento]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                      <form method="POST" action="{{ route('workspaces.activos.eventos.destroy', [$workspace, $activo, $evento]) }}" onsubmit="return confirm('¿Eliminar este evento?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">Aún no hay eventos registrados para este activo.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm mb-4">
        <div class="card-header">Documentación técnica</div>
        <div class="card-body">
          <form method="POST" action="{{ route('workspaces.activos.documentos.store', [$workspace, $activo]) }}" enctype="multipart/form-data" class="mb-4">
            @csrf
            <div class="mb-2">
              <label class="form-label">Tipo de documento</label>
              <select name="tipo_documento" class="form-select" required>
                <option value="">Seleccione...</option>
                <option value="IMAGEN">Imagen</option>
                <option value="ESQUEMA">Esquema</option>
                <option value="MAPA_VARIABLES">Mapa de variables</option>
                <option value="DATASHEET">Datasheet</option>
                <option value="INSTRUCCIONES">Instrucciones</option>
                <option value="OTRO">Otro</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Archivo</label>
              <input type="file" name="archivo" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Descripción</label>
              <textarea name="descripcion" class="form-control" rows="2"></textarea>
            </div>
            <button class="btn btn-primary w-100">Cargar documento</button>
          </form>

          <div class="small fw-semibold mb-2">Archivos cargados</div>
          @forelse($activo->documentos->sortByDesc('id') as $documento)
            <div class="border rounded p-2 mb-2">
              <div class="fw-semibold">{{ $documento->tipo_documento }}</div>
              <div class="small text-muted">{{ $documento->original_name }}</div>
              @if($documento->descripcion)
                <div class="small">{{ $documento->descripcion }}</div>
              @endif
              <div class="d-flex gap-2 mt-2">
                <a href="{{ route('workspaces.activos.documentos.download', [$workspace, $activo, $documento]) }}" class="btn btn-sm btn-outline-secondary">Descargar</a>
                <form method="POST" action="{{ route('workspaces.activos.documentos.destroy', [$workspace, $activo, $documento]) }}" onsubmit="return confirm('¿Eliminar este documento?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
              </div>
            </div>
          @empty
            <div class="text-muted">Sin documentos cargados.</div>
          @endforelse
        </div>
      </div>

      <div class="card shadow-sm mb-4">
        <div class="card-header">Contactos</div>
        <div class="card-body">
          <form method="POST" action="{{ route('workspaces.activos.contactos.store', [$workspace, $activo]) }}" class="mb-4">
            @csrf
            <div class="mb-2">
              <label class="form-label">Tipo de contacto</label>
              <select name="tipo_contacto" class="form-select" required>
                <option value="GENERAL">General</option>
                <option value="PROVEEDOR">Proveedor</option>
                <option value="INSTALACION">Instalación</option>
                <option value="MANTENIMIENTO">Mantenimiento</option>
                <option value="OPERACION">Operación</option>
                <option value="EMERGENCIA">Emergencia</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Cargo</label>
              <input type="text" name="cargo" class="form-control">
            </div>
            <div class="mb-2">
              <label class="form-label">Empresa</label>
              <input type="text" name="empresa" class="form-control">
            </div>
            <div class="mb-2">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control">
            </div>
            <div class="mb-2">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control">
            </div>
            <div class="mb-2">
              <label class="form-label">Notas</label>
              <textarea name="notas" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-check mb-3">
              <input type="checkbox" name="principal" value="1" class="form-check-input" id="contacto_principal">
              <label class="form-check-label" for="contacto_principal">Contacto principal</label>
            </div>
            <button class="btn btn-primary w-100">Agregar contacto</button>
          </form>

          @forelse($activo->contactos->sortByDesc('principal')->sortBy('nombre') as $contacto)
            <div class="border rounded p-2 mb-2">
              <div class="d-flex justify-content-between gap-2">
                <div>
                  <div class="fw-semibold">{{ $contacto->nombre }} @if($contacto->principal)<span class="badge text-bg-primary">Principal</span>@endif</div>
                  <div class="small text-muted">{{ $contacto->tipo_contacto }}{{ $contacto->cargo ? ' | '.$contacto->cargo : '' }}</div>
                  <div class="small">{{ $contacto->empresa ?: 'Sin empresa' }}</div>
                  <div class="small">{{ $contacto->telefono ?: 'Sin teléfono' }}{{ $contacto->email ? ' | '.$contacto->email : '' }}</div>
                </div>
              </div>
              @if($contacto->notas)
                <div class="small mt-1">{{ $contacto->notas }}</div>
              @endif
              <div class="d-flex gap-2 mt-2">
                <a href="{{ route('workspaces.activos.contactos.edit', [$workspace, $activo, $contacto]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                <form method="POST" action="{{ route('workspaces.activos.contactos.destroy', [$workspace, $activo, $contacto]) }}" onsubmit="return confirm('¿Eliminar este contacto?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
              </div>
            </div>
          @empty
            <div class="text-muted">Sin contactos registrados.</div>
          @endforelse
        </div>
      </div>

      <div class="card shadow-sm mb-4">
        <div class="card-header">Dependencias</div>
        <div class="card-body">
          <div class="mb-3">
            <div class="fw-semibold">Activos hijos</div>
            @forelse($activo->children as $child)
              <div>
                <a href="{{ route('workspaces.activos.show', [$workspace, $child]) }}">{{ $child->nombre }}</a>
              </div>
            @empty
              <div class="text-muted">Sin activos hijos.</div>
            @endforelse
          </div>

          <div>
            <div class="fw-semibold">Estado del registro</div>
            <div>{{ $activo->activo ? 'Habilitado' : 'Deshabilitado' }}</div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Acciones</div>
        <div class="card-body">
          <form method="POST" action="{{ route('workspaces.activos.destroy', [$workspace, $activo]) }}" onsubmit="return confirm('¿Eliminar este activo?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger w-100">Eliminar activo</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
