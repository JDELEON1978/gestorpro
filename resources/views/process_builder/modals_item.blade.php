{{-- Crear Item --}}
<div class="modal fade" id="modalItemCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST"
          action="{{ $proceso ? route('builder.item.store', ['proceso' => $proceso->id]) : route('process.builder') }}">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Crear Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">

        @if(!$proceso)
          <div class="alert alert-warning">
            No hay proceso seleccionado. Selecciona un proceso antes de crear un item.
          </div>
        @endif

        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input class="form-control" name="nombre" required
                 placeholder="Ej: Orden de Compra (DOC02636)">
        </div>

        <div class="mb-2">
          <label class="form-label">Categoría</label>
          <select class="form-select" name="categoria" required>
            <option value="DOCUMENTO">DOCUMENTO</option>
            <option value="FORMULARIO">FORMULARIO</option>
            <option value="OPERACION">OPERACION</option>
          </select>
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="requiere_evidencia" id="item_evi_create" value="1" checked>
          <label class="form-check-label" for="item_evi_create">Requiere evidencia (scan)</label>
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="activo" id="item_activo_create" value="1" checked>
          <label class="form-check-label" for="item_activo_create">Activo</label>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit" {{ !$proceso ? 'disabled' : '' }}>Guardar</button>
      </div>
    </form>
  </div>
</div>


{{-- Editar Item --}}
<div class="modal fade" id="modalItemEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="#" id="formItemEdit">
      @csrf @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">Editar Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input class="form-control" name="nombre" required>
        </div>

        <div class="mb-2">
          <label class="form-label">Categoría</label>
          <select class="form-select" name="categoria" required>
            <option value="DOCUMENTO">DOCUMENTO</option>
            <option value="FORMULARIO">FORMULARIO</option>
            <option value="OPERACION">OPERACION</option>
          </select>
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="requiere_evidencia" id="item_evi_edit" value="1">
          <label class="form-check-label" for="item_evi_edit">Requiere evidencia (scan)</label>
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="activo" id="item_activo_edit" value="1">
          <label class="form-check-label" for="item_activo_edit">Activo</label>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>
