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
  .gp-card.dragging{ opacity: .6; } /* ✅ visible feedback */
  .gp-dropzone.drag-over{
    outline: 2px dashed rgba(0,90,156,.35);
    outline-offset: 4px;
    border-radius: 12px;
  }

  .gp-card-top{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:10px;
    margin-bottom: 6px;
  }
  .gp-card-title{ font-weight: 800; font-size: 13px; margin:0; }
  .gp-card-meta{ font-size: 12px; opacity: .75; display:flex; gap:10px; flex-wrap:wrap; }

  .gp-priority-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 8px;
    border: 1px solid #D1D5DB;
    background: #F3F4F6;
    font-size: 15px;
    font-weight: 500;
  }

  .gp-priority-badge i {
    color: #01040c;
    font-size: 16px;
  }

  .gp-time-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 8px;
    border: 1px solid #D1D5DB;
    background: #F3F4F6;
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    min-width: 92px;
    letter-spacing: 1px;
  }

  /* ✅ botón/icono clickeable (paperclip) dentro del badge */
  .gp-icon-btn {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding: 2px 4px;
    border-radius: 6px;
    cursor: pointer;
    user-select: none;
  }
  .gp-icon-btn:hover{
    background: rgba(0,0,0,.06);
  }
    .gp-time-badge--ok{
      border-color: #16A34A;
      background: #DCFCE7;
      color: #166534;
    }

    .gp-time-badge--late{
      border-color: #DC2626;
      background: #FEE2E2;
      color: #991B1B;
    }

  .gp-time-badge--nodue{
    border-color: #D1D5DB;
    background: #F3F4F6;
    color: #374151;
  }
</style>

<div class="gp-kanban" id="gpKanban">

@foreach($statuses as $st)
  @php
    $list  = $tasksByStatus[$st->id] ?? collect();
    $count = is_countable($list) ? count($list) : $list->count();
    $stColor = $st->color ?: '#6B7280';
  @endphp

  <div class="gp-panel gp-col">
    <div class="gp-col-head">
      <div class="gp-col-title">{{ $st->name }}</div>
      <div class="gp-col-count">{{ $count }}</div>
    </div>

    <div class="gp-dropzone" data-status-id="{{ $st->id }}">
      @foreach($list as $t)
        @php
          $payload = [
            'id'          => $t->id,
            'project_id'  => $t->project_id,
            'nodo_id'     => $t->nodo_id,
            'title'       => $t->title,
            'description' => $t->description,
            'status_id'   => $t->status_id,
            'priority'    => $t->priority,
            'start_at'    => $t->start_at ? \Illuminate\Support\Carbon::parse($t->start_at)->format('Y-m-d') : null,
            'due_at'      => $t->due_at ? \Illuminate\Support\Carbon::parse($t->due_at)->format('Y-m-d') : null,
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

          <div class="gp-card-top">
            <div class="gp-card-title text-truncate">{{ $t->title }}</div>
          </div>

          <div class="gp-card-meta">
            @if($t->priority)
              <span style="display:flex; gap:10px; align-items:center;">
                <span class="gp-priority-badge">
                  <i class="bi bi-flag-fill" style="color: {{ $stColor }};"></i>

                  @php
                    $priorityColor = match((int)$t->priority) {
                        1 => '#DC2626',
                        2 => '#EA580C',
                        3 => '#D97706',
                        4 => '#2563EB',
                        5 => '#6B7280',
                        default => '#6B7280'
                    };
                  @endphp

                  <i class="bi bi-{{ $t->priority }}-circle-fill" style="color: {{ $priorityColor }};"></i>
                  <i class="bi bi-paperclip"></i>
                  <i class="bi bi-chat-dots"></i>
                  <span class="gp-icon-btn js-open-task-modal"
                        title="Editar la tarea"
                        data-task-id="{{ $t->id }}">
                    <i class="bi bi-pencil"></i>
                  </span>
                </span>

                   @if(($st->estado ?? null) !== 'APROBADO')
                    <span class="gp-time-badge js-time-badge gp-time-badge--ok"
                          data-created-at="{{ $t->created_at ? \Illuminate\Support\Carbon::parse($t->created_at)->toIso8601String() : '' }}"
                          data-due-at="{{ $t->due_at ? \Illuminate\Support\Carbon::parse($t->due_at)->toIso8601String() : '' }}"
                          data-estado="{{ $st->estado ?? '' }}">
                      <i class="bi bi-smartwatch" style="margin-right:6px;"></i>
                      <span class="js-time-text">..</span>
                    </span>
                  @endif
              </span>
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
  if (window.__gpKanbanBound) return;
  window.__gpKanbanBound = true;

  let draggingEl = null;
  let draggingTaskId = null;

  function getCsrfToken(){
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  function orderedIdsForZone(zone){
    const ids = Array.from(zone.querySelectorAll('.gp-card[data-task-id]'))
      .map(el => parseInt(el.getAttribute('data-task-id'), 10))
      .filter(n => Number.isFinite(n) && n > 0);
    return Array.from(new Set(ids));
  }

  function closestCard(el){
    return el?.closest?.('.gp-card') || null;
  }

  // ✅ Abre modal solo desde paperclip:
  // - dispara el click “normal” de la tarjeta (si tu app ya lo usa para abrir modal)
  // - luego activa el tab/botón "Archivos"
  function openFilesForCard(card){
    if (!card) return;

    // Si tu app abre modal escuchando click en ".js-task" u otra parte,
    // esto mantiene compatibilidad sin duplicar lógica.
    card.dispatchEvent(new MouseEvent('click', { bubbles: true }));

    setTimeout(() => {
      const modal = document.getElementById('modalTask');
      if (!modal) return;

      const btn = Array.from(modal.querySelectorAll('button'))
        .find(b => (b.textContent || '').trim().toLowerCase() === 'archivos');

      if (btn) btn.click();
    }, 160);
  }

  // ❌ Bloquear apertura de modal al hacer click en la tarjeta
  // (Si tu app tenía listener global, lo cortamos aquí)
  document.addEventListener('click', (e) => {
    const card = e.target.closest('.gp-card');
    if (!card) return;

    // Si el click fue en el paperclip, no bloqueamos aquí (lo maneja el handler de abajo)
    if (e.target.closest('.js-open-task-modal')) return;

    // Evita que listeners externos abran el modal por click en la tarjeta
    e.preventDefault();
    e.stopPropagation();
  }, true); // 👈 captura para interceptar antes

  // ✅ Click solo en paperclip abre modal + archivos
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-task-modal');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const card = btn.closest('.gp-card');
    openFilesForCard(card);
  });

  // ✅ IMPORTANTÍSIMO: evitar que el drag se active cuando intentas clickear paperclip
  document.addEventListener('mousedown', (e) => {
    if (e.target.closest('.js-open-task-modal')) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);

  // Drag start
  document.addEventListener('dragstart', (e) => {
    const card = closestCard(e.target);
    if (!card) return;

    draggingEl = card;
    draggingTaskId = card.getAttribute('data-task-id');

    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', draggingTaskId);

    card.classList.add('dragging');
  });

  // Drag end
  document.addEventListener('dragend', () => {
    if (draggingEl) draggingEl.classList.remove('dragging');
    draggingEl = null;
    draggingTaskId = null;
    document.querySelectorAll('.gp-dropzone.drag-over').forEach(z => z.classList.remove('drag-over'));
  });

  // Allow drop
  document.addEventListener('dragover', (e) => {
    const zone = e.target.closest('.gp-dropzone');
    if (!zone || !draggingEl) return;
    e.preventDefault();
    zone.classList.add('drag-over');

    const afterEl = getDragAfterElement(zone, e.clientY);
    if (afterEl == null) {
      zone.appendChild(draggingEl);
    } else {
      zone.insertBefore(draggingEl, afterEl);
    }
  });

  document.addEventListener('drop', async (e) => {
    const zone = e.target.closest('.gp-dropzone');
    if (!zone || !draggingEl) return;
    e.preventDefault();

    zone.classList.remove('drag-over');

    const newStatusId = parseInt(zone.getAttribute('data-status-id'), 10);
    const taskId = parseInt(draggingTaskId, 10);
    if (!Number.isFinite(newStatusId) || !Number.isFinite(taskId)) return;

    let ordered = orderedIdsForZone(zone);
    if (!ordered.includes(taskId)) ordered.push(taskId);

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

      const json = await res.json().catch(() => ({}));

      if(!res.ok){
        console.error('No se pudo mover la tarea', {
          status: res.status,
          message: json.message,
          errors: json.errors
        });
        if (typeof refreshProjectArea === 'function') {
          await refreshProjectArea();
        } else {
          window.location.reload();
        }
        return;
      }

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
<script>
(function(){
  // 8 horas reales: 08:00–12:00 y 13:00–17:00 (L–V)
  const WORK_BLOCKS = [
    { start: {h: 8,  m: 0}, end: {h: 12, m: 0} },
    { start: {h: 13, m: 0}, end: {h: 17, m: 0} },
  ];

  function isWeekend(d){
    const day = d.getDay(); // 0=Dom, 6=Sab
    return day === 0 || day === 6;
  }

  function atTime(baseDate, hh, mm){
    const d = new Date(baseDate);
    d.setHours(hh, mm, 0, 0);
    return d;
  }

  function overlapSeconds(aStart, aEnd, bStart, bEnd){
    const s = Math.max(aStart.getTime(), bStart.getTime());
    const e = Math.min(aEnd.getTime(), bEnd.getTime());
    return Math.max(0, Math.floor((e - s) / 1000));
  }

  // Segundos hábiles entre start y end (start <= end)
  function businessSecondsBetween(start, end){
    if (!(start instanceof Date) || !(end instanceof Date)) return 0;
    if (end <= start) return 0;

    let total = 0;
    const cursor = new Date(start);
    cursor.setSeconds(0,0);

    while (cursor.toDateString() !== end.toDateString()){
      if (!isWeekend(cursor)){
        for (const blk of WORK_BLOCKS){
          const blkStart = atTime(cursor, blk.start.h, blk.start.m);
          const blkEnd   = atTime(cursor, blk.end.h, blk.end.m);

          const dayEnd = new Date(cursor);
          dayEnd.setHours(23,59,59,999);

          total += overlapSeconds(cursor, dayEnd, blkStart, blkEnd);
        }
      }
      cursor.setDate(cursor.getDate() + 1);
      cursor.setHours(0,0,0,0);
    }

    if (!isWeekend(end)){
      for (const blk of WORK_BLOCKS){
        const blkStart = atTime(end, blk.start.h, blk.start.m);
        const blkEnd   = atTime(end, blk.end.h, blk.end.m);
        total += overlapSeconds(start, end, blkStart, blkEnd);
      }
    }

    return total;
  }

  function fmtHMS(totalSeconds){
    const s = Math.max(0, Math.floor(totalSeconds));
    const hh = String(Math.floor(s / 3600)).padStart(2,'0');
    const mm = String(Math.floor((s % 3600) / 60)).padStart(2,'0');
    const ss = String(s % 60).padStart(2,'0');
    return `${hh}:${mm}:${ss}`;
  }

  function setBadgeState(badge, state){
    badge.classList.remove('gp-time-badge--ok','gp-time-badge--late','gp-time-badge--nodue');
    badge.classList.add(state);
  }

  function updateTimeBadges(){
    const now = new Date();

    document.querySelectorAll('.js-time-badge').forEach(badge => {
      const dueIso = (badge.getAttribute('data-due-at') || '').trim();
      const createdIso = (badge.getAttribute('data-created-at') || '').trim();
      const textEl = badge.querySelector('.js-time-text');
      if (!textEl) return;

      const due = dueIso ? new Date(dueIso) : null;
      const created = createdIso ? new Date(createdIso) : null;

      // Si no hay due_at → contar ascendente desde created_at (si existe)
      if (!dueIso || !due || isNaN(due.getTime())){
        if (created && !isNaN(created.getTime())){
          const secs = businessSecondsBetween(created, now);
          textEl.textContent = fmtHMS(secs);
          setBadgeState(badge, 'gp-time-badge--nodue');
        } else {
          textEl.textContent = '00:00:00';
          setBadgeState(badge, 'gp-time-badge--nodue');
        }
        return;
      }

      // due_at existe:
      if (due > now){
        // Regresiva (tiempo restante) → verde
        const secs = businessSecondsBetween(now, due);
        textEl.textContent = fmtHMS(secs);
        setBadgeState(badge, 'gp-time-badge--ok');
      } else {
        // Ascendente (tiempo vencido) → rojo
        const secs = businessSecondsBetween(due, now);
        textEl.textContent = fmtHMS(secs);
        setBadgeState(badge, 'gp-time-badge--late');
      }
    });
  }

  updateTimeBadges();
  setInterval(updateTimeBadges, 1000);
})();
</script>