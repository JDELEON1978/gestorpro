{{-- resources/views/tasks/views/tablero.blade.php --}}
@php
  // $statuses: collection
  // $tasksByStatus: array keyed by status_id
@endphp

<style>
  .gp-kanban { display:flex; gap:12px; overflow:auto; padding-bottom: 6px; }
  .gp-col { min-width: 320px; max-width: 320px; }
  .gp-col-head{
    display:flex; align-items:center; justify-content:space-between;
    padding: 10px 12px;
    border-bottom: 1px solid rgba(0,0,0,.06);
  }
  .gp-col-title{ font-weight: 800; font-size: 13px; }
  .gp-col-count{ font-size: 12px; opacity: .75; }
  .gp-dropzone{
    padding: 10px;
    min-height: 240px;
  }
  .gp-card{
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 10px;
    background: #fff;
    padding: 10px 10px;
    margin-bottom: 10px;
    cursor: grab;
  }
  .gp-card:active{ cursor: grabbing; }
  .gp-card-title{ font-weight: 800; font-size: 13px; margin-bottom: 6px; }
  .gp-card-meta{ font-size: 12px; opacity: .75; display:flex; gap:10px; flex-wrap:wrap; }
  .gp-dropzone.drag-over{
    outline: 2px dashed rgba(0,90,156,.35);
    outline-offset: 4px;
    border-radius: 12px;
  }
</style>

<div class="gp-kanban" id="gpKanban">

  @foreach($statuses as $st)
    @php
      $list = $tasksByStatus[$st->id] ?? collect();
      $count = is_countable($list) ? count($list) : $list->count();
    @endphp

    <div class="gp-panel gp-col">
      <div class="gp-col-head">
        <div class="gp-col-title">{{ $st->name }}</div>
        <div class="gp-col-count">{{ $count }}</div>
      </div>

      <div class="gp-dropzone"
           data-status-id="{{ $st->id }}">
        @foreach($list as $t)
          @php
            // Payload para modal editar (igual que ya usas)
            $payload = [
              'id' => $t->id,
              'title' => $t->title,
              'description' => $t->description,
              'status_id' => $t->status_id,
              'priority' => $t->priority,
              'start_at' => $t->start_at ? \Illuminate\Support\Carbon::parse($t->start_at)->format('Y-m-d') : null,
              'due_at' => $t->due_at ? \Illuminate\Support\Carbon::parse($t->due_at)->format('Y-m-d') : null,
            ];
            $payloadJson = json_encode(
              $payload,
              JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            );
          @endphp

          <div class="gp-card js-task"
               draggable="true"
               data-task-id="{{ $t->id }}"
               data-task="{{ $payloadJson }}">
            <div class="gp-card-title text-truncate">{{ $t->title }}</div>
            <div class="gp-card-meta">
              @if($t->priority)
                <span><i class="bi bi-flag"></i> P{{ $t->priority }}</span>
              @endif
              @if($t->start_at)
                <span><i class="bi bi-play"></i> {{ \Illuminate\Support\Carbon::parse($t->start_at)->format('Y-m-d') }}</span>
              @endif
              @if($t->due_at)
                <span><i class="bi bi-calendar2-check"></i> {{ \Illuminate\Support\Carbon::parse($t->due_at)->format('Y-m-d') }}</span>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach

</div>

<script>
(function(){
  // Evitar doble binding si se recarga el área por AJAX
  if (window.__gpKanbanBound) return;
  window.__gpKanbanBound = true;

  let draggingEl = null;
  let draggingTaskId = null;
  let draggedFromStatus = null;

  function getCsrfToken(){
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  function orderedIdsForZone(zone){
    return Array.from(zone.querySelectorAll('[data-task-id]'))
      .map(el => parseInt(el.getAttribute('data-task-id'), 10))
      .filter(n => Number.isFinite(n));
  }

  function closestCard(el){
    return el?.closest?.('.gp-card') || null;
  }

  // Drag start
  document.addEventListener('dragstart', (e) => {
    const card = closestCard(e.target);
    if (!card) return;

    draggingEl = card;
    draggingTaskId = card.getAttribute('data-task-id');
    draggedFromStatus = card.closest('.gp-dropzone')?.getAttribute('data-status-id') || null;

    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', draggingTaskId);

    // Para que no dispare el click de edición al soltar
    card.classList.add('dragging');
  });

  // Drag end
  document.addEventListener('dragend', () => {
    if (draggingEl) draggingEl.classList.remove('dragging');
    draggingEl = null;
    draggingTaskId = null;
    draggedFromStatus = null;
    document.querySelectorAll('.gp-dropzone.drag-over').forEach(z => z.classList.remove('drag-over'));
  });

  // Allow drop
  document.addEventListener('dragover', (e) => {
    const zone = e.target.closest('.gp-dropzone');
    if (!zone || !draggingEl) return;
    e.preventDefault();
    zone.classList.add('drag-over');

    // Insertar visualmente según posición del mouse
    const afterEl = getDragAfterElement(zone, e.clientY);
    if (afterEl == null) {
      zone.appendChild(draggingEl);
    } else {
      zone.insertBefore(draggingEl, afterEl);
    }
  });

  document.addEventListener('dragleave', (e) => {
    const zone = e.target.closest('.gp-dropzone');
    if (!zone) return;
    // si sales del contenedor, limpia estilo
    // (no siempre perfecto en HTML5 DnD, pero sirve)
    // zone.classList.remove('drag-over');
  });

  // Drop => guardar en servidor
  document.addEventListener('drop', async (e) => {
    const zone = e.target.closest('.gp-dropzone');
    if (!zone || !draggingEl) return;
    e.preventDefault();

    zone.classList.remove('drag-over');

    const newStatusId = parseInt(zone.getAttribute('data-status-id'), 10);
    const taskId = parseInt(draggingTaskId, 10);
    if (!Number.isFinite(newStatusId) || !Number.isFinite(taskId)) return;

    const ordered = orderedIdsForZone(zone);

    try{
      const res = await fetch(`/tasks/${taskId}/move`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({
          status_id: newStatusId,
          ordered_ids: ordered
        })
      });

      if(!res.ok){
        // Revert simple: recargar área del proyecto si existe tu función
        console.error('No se pudo mover la tarea');
        if (typeof refreshProjectArea === 'function') {
          await refreshProjectArea();
        } else {
          window.location.reload();
        }
        return;
      }

      // Si también cambió de columna, conviene actualizar contadores:
      // MVP: recargar solo el área, así no se desincroniza.
      if (typeof refreshProjectArea === 'function') {
        await refreshProjectArea();
      }

    }catch(err){
      console.error(err);
      if (typeof refreshProjectArea === 'function') {
        await refreshProjectArea();
      } else {
        window.location.reload();
      }
    }
  });

  // Helper: busca el elemento después del cual insertar según Y
  function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.gp-card:not(.dragging)')];

    return draggableElements.reduce((closest, child) => {
      const box = child.getBoundingClientRect();
      const offset = y - box.top - box.height / 2;
      if (offset < 0 && offset > closest.offset) {
        return { offset: offset, element: child };
      } else {
        return closest;
      }
    }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
  }
})();
</script>
