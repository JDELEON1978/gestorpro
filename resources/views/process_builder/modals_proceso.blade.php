{{-- Crear Proceso --}}
<div class="modal fade" id="modalProcesoCreate" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('builder.proceso.store') }}">
      @csrf
      <div class="modal-header"><h5 class="modal-title">Crear Proceso</h5></div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input class="form-control" name="nombre" required>
        </div>
        <div class="row g-2">
          <div class="col">
            <label class="form-label">Código</label>
            <input class="form-control" name="codigo">
          </div>
          <div class="col">
            <label class="form-label">Versión</label>
            <input class="form-control" name="version">
          </div>
        </div>
        <div class="mt-2">
          <label class="form-label">Estado</label>
          <input class="form-control" name="estado" value="activo">
        </div>
        <div class="mt-2">
          <label class="form-label">Descripción</label>
          <textarea class="form-control" name="descripcion" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- Editar Proceso --}}
<div class="modal fade" id="modalProcesoEdit" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="#">
      @csrf @method('PUT')
      <div class="modal-header"><h5 class="modal-title">Editar Proceso</h5></div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input class="form-control" name="nombre" required>
        </div>
        <div class="row g-2">
          <div class="col">
            <label class="form-label">Código</label>
            <input class="form-control" name="codigo">
          </div>
          <div class="col">
            <label class="form-label">Versión</label>
            <input class="form-control" name="version">
          </div>
        </div>
        <div class="mt-2">
          <label class="form-label">Estado</label>
          <input class="form-control" name="estado">
        </div>
        <div class="mt-2">
          <label class="form-label">Descripción</label>
          <textarea class="form-control" name="descripcion" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>
