{{-- Crear Nodo --}}
<div class="modal fade" id="modalNodoCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST"
          action="{{ $proceso ? route('builder.nodo.store', ['proceso' => $proceso->id]) : route('process.builder') }}">
      @csrf

      <div class="modal-header">
        <h5 class="modal-title">Crear Nodo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        @if(!$proceso)
          <div class="alert alert-warning">
            No hay proceso seleccionado. Selecciona un proceso antes de crear un nodo.
          </div>
        @endif

        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input class="form-control" name="nombre" required placeholder="Ej: Revisión Técnica">
        </div>

        <div class="row g-2">
          <div class="col">
            <label class="form-label">Tipo</label>
            <select class="form-select" name="tipo_nodo" required>
              <option value="inicio">inicio</option>
              <option value="actividad" selected>actividad</option>
              <option value="decision">decision</option>
              <option value="fin">fin</option>
              <option value="conector">conector</option>
            </select>
          </div>
          <div class="col">
            <label class="form-label">Orden</label>
            <input class="form-control" name="orden" type="number" min="1" placeholder="auto">
          </div>
        </div>

        <div class="mt-2">
          <label class="form-label">SLA horas</label>
          <input class="form-control" name="sla_horas" type="number" min="0" placeholder="opcional">
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="activo" id="nodo_activo_create" value="1" checked>
          <label class="form-check-label" for="nodo_activo_create">Activo</label>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit" {{ !$proceso ? 'disabled' : '' }}>Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- Editar Nodo --}}
<div class="modal fade" id="modalNodoEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" action="#" id="formNodoEdit">
      @csrf @method('PUT')

      <div class="modal-header">
        <h5 class="modal-title">Editar Nodo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input class="form-control" name="nombre" required>
        </div>

        <div class="row g-2">
          <div class="col">
            <label class="form-label">Tipo</label>
            <select class="form-select" name="tipo_nodo" id="tipoNodoEdit" required>
              <option value="inicio">inicio</option>
              <option value="actividad">actividad</option>
              <option value="decision">decision</option>
              <option value="fin">fin</option>
              <option value="conector">conector</option>
            </select>
          </div>
          <div class="col">
            <label class="form-label">Orden</label>
            <input class="form-control" name="orden" type="number" min="1">
          </div>
        </div>

        <div class="mt-2">
          <label class="form-label">SLA horas</label>
          <input class="form-control" name="sla_horas" type="number" min="0">
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="activo" id="nodo_activo_edit" value="1">
          <label class="form-check-label" for="nodo_activo_edit">Activo</label>
        </div>

        <div class="mb-2 mt-2">
          <label class="form-label">Responsable (Rol)</label>
          <select class="form-select" name="responsable_rol_id">
            <option value="">-- Sin asignar --</option>
            @foreach($roles as $r)
              <option value="{{ $r->id }}">{{ $r->nombre }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">Descripción</label>
          <textarea class="form-control" name="descripcion" rows="4"></textarea>
        </div>

        {{-- =========================
            TRANSICIONES (relaciones)
           ========================= --}}
        <div class="mt-3">
          <div class="border rounded p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-bold">Transiciones (salidas)</div>
              <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddSalida">
                + Agregar transición
              </button>
            </div>

            <div class="small text-muted mb-2">
              Una transición = <strong>Etiqueta</strong> (texto que se verá en la flecha) + <strong>Nodo destino</strong>.
              Si borras una fila y guardas, esa relación debe eliminarse.
            </div>

            <div id="decisionRows" class="d-flex flex-column gap-2"></div>

            <div class="small text-muted mt-2">
              Si una fila no tiene etiqueta o destino, se ignora al guardar.
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>