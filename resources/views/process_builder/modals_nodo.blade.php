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
  <div class="modal-dialog">
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
            <select class="form-select" name="tipo_nodo" required>
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
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>
