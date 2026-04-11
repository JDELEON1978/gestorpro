{{-- resources/views/tasks/views/tablero.blade.php --}}
@php
  // $statuses: collection
  // $tasksByStatus: array keyed by status_id
  $businessSecondsBetween = function ($from, $to) {
      if (!$from || !$to) {
          return null;
      }

      $start = \Illuminate\Support\Carbon::parse($from);
      $end = \Illuminate\Support\Carbon::parse($to);

      if ($end->lessThanOrEqualTo($start)) {
          return 0;
      }

      $blocks = [
          [[8, 0], [12, 0]],
          [[13, 0], [17, 0]],
      ];

      $cursor = $start->copy()->startOfDay();
      $lastDay = $end->copy()->startOfDay();
      $seconds = 0;

      while ($cursor->lessThanOrEqualTo($lastDay)) {
          if ($cursor->isWeekday()) {
              foreach ($blocks as [$blockStart, $blockEnd]) {
                  $blockFrom = $cursor->copy()->setTime($blockStart[0], $blockStart[1], 0);
                  $blockTo = $cursor->copy()->setTime($blockEnd[0], $blockEnd[1], 0);

                  $effectiveFrom = $start->greaterThan($blockFrom) ? $start : $blockFrom;
                  $effectiveTo = $end->lessThan($blockTo) ? $end : $blockTo;

                  if ($effectiveTo->greaterThan($effectiveFrom)) {
                      $seconds += $effectiveTo->diffInSeconds($effectiveFrom);
                  }
              }
          }

          $cursor->addDay();
      }

      return $seconds;
  };

  $formatBusinessDuration = function ($seconds) {
      if ($seconds === null) {
          return 'Sin datos';
      }

      $seconds = max(0, (int) $seconds);
      $hours = intdiv($seconds, 3600);
      $minutes = intdiv($seconds % 3600, 60);
      $days = intdiv($hours, 8);
      $remainingHours = $hours % 8;

      $parts = [];
      if ($days > 0) {
          $parts[] = $days.'d';
      }
      if ($remainingHours > 0 || $days > 0) {
          $parts[] = $remainingHours.'h';
      }
      $parts[] = $minutes.'m';

      return implode(' ', $parts);
  };

  $formatElapsedDuration = function ($from, $to) {
      if (!$from || !$to) {
          return 'Sin datos';
      }

      $start = \Illuminate\Support\Carbon::parse($from);
      $end = \Illuminate\Support\Carbon::parse($to);

      $seconds = abs($end->diffInSeconds($start, false));
      if ($seconds > 0 && $seconds < 60) {
          $seconds = 60;
      }

      $totalMinutes = (int) ceil($seconds / 60);
      $days = intdiv($totalMinutes, 24 * 60);
      $remainingMinutes = $totalMinutes % (24 * 60);
      $hours = intdiv($remainingMinutes, 60);
      $minutes = $remainingMinutes % 60;

      return $days.'d:'.$hours.'h:'.$minutes.'m';
  };
@endphp

<style>
  .gp-kanban { display:flex; gap:12px; overflow:auto; padding-bottom: 6px; }
  .gp-col { min-width: 400px; max-width: 400px; }
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
    justify-content: flex-start;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid #D1D5DB;
    background: #F3F4F6;
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    min-width: 154px;
    max-width: 154px;
    min-height: 48px;
    letter-spacing: .4px;
    white-space: nowrap;
    overflow: hidden;
  }
  .gp-time-badge-body{
    display:flex;
    flex-direction:column;
    align-items:flex-start;
    min-width:0;
    line-height:1.05;
  }
  .gp-time-label{
    font-size:10px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.5px;
    opacity:.82;
    margin-bottom:2px;
  }
  .gp-time-text{
    font-size:15px;
    font-weight:700;
    line-height:1.1;
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

  .gp-done-group{
    margin-bottom: 10px;
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 10px;
    background: rgba(255,255,255,.72);
  }
  .gp-done-group summary{
    list-style: none;
    cursor: pointer;
    padding: 10px 12px;
    font-weight: 800;
    font-size: 13px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
  }
  .gp-done-group summary::-webkit-details-marker{ display:none; }
  .gp-done-group-count{
    font-size: 12px;
    opacity: .7;
    font-weight: 700;
  }
  .gp-done-group-body{
    padding: 0 10px 10px;
  }
  .gp-done-group-open{
    margin-bottom: 10px;
  }
  .gp-done-group-open .gp-done-group-title{
    padding: 8px 2px 10px;
    font-size: 13px;
    font-weight: 800;
    display:flex;
    align-items:center;
    justify-content:space-between;
  }
  .gp-card-done{
    cursor: pointer;
    padding: 10px 12px;
  }
  .gp-card-done .gp-card-top{
    margin-bottom: 4px;
  }
  .gp-card-done-title{
    font-weight: 800;
    font-size: 13px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .gp-card-done-meta,
  .gp-card-done-extra,
  .gp-card-done-desc{
    font-size: 12px;
    color: rgba(15,23,42,.72);
    line-height: 1.35;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .gp-card-done-meta{
    -webkit-line-clamp: 1;
    margin-bottom: 2px;
  }
  .gp-card-done-extra{
    -webkit-line-clamp: 1;
    margin-bottom: 2px;
  }
  .gp-card-done-desc{
    -webkit-line-clamp: 1;
  }
  .gp-done-status-ok{
    color: #15803d;
    font-weight: 800;
  }
  .gp-done-status-late{
    color: #b91c1c;
    font-weight: 800;
  }
</style>

<div class="gp-kanban" id="gpKanban">

@foreach($statuses as $st)
  @php
    $list  = $tasksByStatus[$st->id] ?? collect();
    $count = is_countable($list) ? count($list) : $list->count();
    $stColor = $st->color ?: '#6B7280';
    $isDoneColumn = strtolower((string)($st->slug ?? '')) === 'done' || ($st->estado ?? null) === 'APROBADO';
    $doneGroups = collect();

    if ($isDoneColumn) {
      $now = \Illuminate\Support\Carbon::now();
      $startOfWeek = $now->copy()->startOfWeek();
      $startOfLastWeek = $startOfWeek->copy()->subWeek();
      $startOfMonth = $now->copy()->startOfMonth();
      $startOfLastMonth = $startOfMonth->copy()->subMonth();
      $startOfYear = $now->copy()->startOfYear();

      $doneGroups = collect([
        ['key' => 'today', 'label' => 'Hoy', 'items' => collect()],
        ['key' => 'yesterday', 'label' => 'Ayer', 'items' => collect()],
        ['key' => 'early_week', 'label' => 'Al principio de esta semana', 'items' => collect()],
        ['key' => 'last_week', 'label' => 'La semana pasada', 'items' => collect()],
        ['key' => 'last_month', 'label' => 'El mes pasado', 'items' => collect()],
        ['key' => 'early_year', 'label' => 'Al principio de este año', 'items' => collect()],
        ['key' => 'long_time', 'label' => 'Hace mucho tiempo', 'items' => collect()],
      ])->keyBy('key');

      foreach ($list as $doneTask) {
        $completedAt = $doneTask->completed_at ?? $doneTask->updated_at ?? $doneTask->created_at ?? null;
        $completedAt = $completedAt ? \Illuminate\Support\Carbon::parse($completedAt) : null;

        $bucket = 'long_time';
        if ($completedAt) {
          if ($completedAt->isToday()) {
            $bucket = 'today';
          } elseif ($completedAt->isYesterday()) {
            $bucket = 'yesterday';
          } elseif ($completedAt->greaterThanOrEqualTo($startOfWeek)) {
            $bucket = 'early_week';
          } elseif ($completedAt->greaterThanOrEqualTo($startOfLastWeek) && $completedAt->lt($startOfWeek)) {
            $bucket = 'last_week';
          } elseif ($completedAt->greaterThanOrEqualTo($startOfLastMonth) && $completedAt->lt($startOfMonth)) {
            $bucket = 'last_month';
          } elseif ($completedAt->greaterThanOrEqualTo($startOfYear)) {
            $bucket = 'early_year';
          }
        }

        $group = $doneGroups->get($bucket);
        $group['items'] = $group['items']->push($doneTask);
        $doneGroups->put($bucket, $group);
      }
    }
  @endphp

  <div class="gp-panel gp-col">
    <div class="gp-col-head">
      <div class="gp-col-title">{{ $st->name }}</div>
      <div class="gp-col-count">{{ $count }}</div>
    </div>

    <div class="gp-dropzone" data-status-id="{{ $st->id }}">
      @php
        $renderTaskCard = function ($t) use ($st, $stColor, $isDoneColumn, $businessSecondsBetween, $formatBusinessDuration, $formatElapsedDuration) {
            $payload = [
                'id'          => $t->id,
                'project_id'  => $t->project_id,
                'nodo_id'     => $t->nodo_id,
                'title'       => $t->title,
                'description' => $t->description,
                'status_id'   => $t->status_id,
                'priority'    => $t->priority,
                'assignee_name' => $t->assignee?->name,
                'creator_name' => $t->creator?->name,
                'nodo_nombre' => $t->nodo?->nombre,
                'start_at'    => $t->start_at ? \Illuminate\Support\Carbon::parse($t->start_at)->format('Y-m-d') : null,
                'due_at'      => $t->due_at ? \Illuminate\Support\Carbon::parse($t->due_at)->format('Y-m-d') : null,
                'sla_hours'   => $t->sla_hours,
                'sla_started_at' => $t->sla_started_at?->toIso8601String(),
                'sla_due_at'  => $t->sla_due_at?->toIso8601String(),
            ];

            $payloadJson = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            );
            $payloadJsonAttr = e($payloadJson);
            $taskTitle = e($t->title);
            $completedAt = $t->completed_at ?? $t->updated_at ?? $t->created_at ?? null;
            $startedAt = $t->started_at ?? $t->start_at ?? $t->created_at ?? null;
            $durationLabel = $formatElapsedDuration($startedAt, $completedAt);
            $doneMeta = 'Tiempo: '.e($durationLabel).' / SLA: '.e(!empty($t->sla_hours) ? ((int) $t->sla_hours).'h' : 'Sin SLA');
            $slaDueAt = $t->sla_due_at ?? $t->due_at ?? null;
            $isLateForSla = false;
            if ($completedAt && $slaDueAt) {
                $isLateForSla = \Illuminate\Support\Carbon::parse($completedAt)
                    ->greaterThan(\Illuminate\Support\Carbon::parse($slaDueAt));
            }
            $doneUser = e($t->assignee?->name ?? $t->creator?->name ?? 'Sin usuario');
            $doneCompliance = $isLateForSla
                ? '<span class="gp-done-status-late">Con retraso</span>'
                : '<span class="gp-done-status-ok">En tiempo</span>';
            $doneExtra = $doneUser.' | '.$doneCompliance;
            $doneProcessState = 'Estado del proceso: '.e($t->nodo?->nombre ?? 'Sin nodo').' Finalizado';
            if (!empty($t->priority)) {
                $doneExtra .= ' · P'.$t->priority;
            }

            $doneExtra = $doneUser.' | '.$doneCompliance;
            $priorityColor = match((int)$t->priority) {
                1 => '#DC2626',
                2 => '#EA580C',
                3 => '#D97706',
                4 => '#2563EB',
                5 => '#6B7280',
                default => '#6B7280'
            };

            if ($isDoneColumn) {
                return <<<HTML
<div class="gp-card gp-card-done js-task" draggable="true" data-task-id="{$t->id}" data-task="{$payloadJsonAttr}">
  <div class="gp-card-top">
    <div class="gp-card-done-title">{$taskTitle}</div>
  </div>
  <div class="gp-card-done-meta">{$doneMeta}</div>
  <div class="gp-card-done-extra">{$doneExtra}</div>
  <div class="gp-card-done-desc">{$doneProcessState}</div>
</div>
HTML;
            }

            return <<<HTML
<div class="gp-card js-task" draggable="true" data-task-id="{$t->id}" data-task="{$payloadJsonAttr}">
  <div class="gp-card-top">
    <div class="gp-card-title text-truncate">{$taskTitle}</div>
  </div>
  <div class="gp-card-meta">
HTML
            . (
                $t->priority
                ? <<<HTML
      <span style="display:flex; gap:10px; align-items:center;">
        <span class="gp-priority-badge">
          <i class="bi bi-flag-fill" style="color: {$stColor};"></i>
          <i class="bi bi-{$t->priority}-circle-fill" style="color: {$priorityColor};"></i>
          <span class="gp-icon-btn js-open-node-evidences" title="Subir archivos del nodo" data-task-id="{$t->id}">
            <i class="bi bi-paperclip"></i>
          </span>
          <span class="gp-icon-btn js-open-task-activities" title="Ver actividades" data-task-id="{$t->id}">
            <i class="bi bi-chat-dots"></i>
          </span>
          <span class="gp-icon-btn js-open-task-modal" title="Editar la tarea" data-task-id="{$t->id}">
            <i class="bi bi-pencil"></i>
          </span>
        </span>
HTML
                . (
                    (($st->estado ?? null) !== 'APROBADO')
                    ? <<<HTML
        <span class="gp-time-badge js-time-badge gp-time-badge--ok js-open-task-activities"
              title="Ver actividades"
              data-task-id="{$t->id}"
              data-sla-hours="{$t->sla_hours}"
              data-sla-started-at="{$t->sla_started_at?->toIso8601String()}"
              data-sla-due-at="{$t->sla_due_at?->toIso8601String()}"
              data-started-at="{$t->started_at?->toIso8601String()}"
              data-created-at="{$t->created_at?->toIso8601String()}"
              data-due-at="{$t->due_at?->toIso8601String()}"
              data-estado="{$st->estado}">
          <i class="bi bi-smartwatch"></i>
          <span class="gp-time-badge-body">
            <span class="gp-time-label js-time-label">Restante</span>
            <span class="js-time-text gp-time-text">..</span>
          </span>
        </span>
HTML
                    : ''
                )
                . '</span>'
                : ''
            )
            . (
                $t->start_at
                ? '<span><i class="bi bi-play"></i> '.\Illuminate\Support\Carbon::parse($t->start_at)->format('Y-m-d').'</span>'
                : ''
            )
            . (
                $t->due_at
                ? '<span><i class="bi bi-calendar2-check"></i> '.\Illuminate\Support\Carbon::parse($t->due_at)->format('Y-m-d').'</span>'
                : ''
            )
            . <<<HTML
  </div>
</div>
HTML;
        };
      @endphp

      @if($isDoneColumn)
        @foreach($doneGroups->values() as $group)
          @continue($group['items']->isEmpty())

          @if($group['key'] === 'today')
            <div class="gp-done-group-open">
              <div class="gp-done-group-title">
                <span>{{ $group['label'] }}</span>
                <span class="gp-done-group-count">{{ $group['items']->count() }}</span>
              </div>
              @foreach($group['items'] as $t)
                {!! $renderTaskCard($t) !!}
              @endforeach
            </div>
          @else
            <details class="gp-done-group">
              <summary>
                <span>{{ $group['label'] }}</span>
                <span class="gp-done-group-count">{{ $group['items']->count() }}</span>
              </summary>
              <div class="gp-done-group-body">
                @foreach($group['items'] as $t)
                  {!! $renderTaskCard($t) !!}
                @endforeach
              </div>
            </details>
          @endif
        @endforeach
      @else
        @foreach($list as $t)
          {!! $renderTaskCard($t) !!}
        @endforeach
      @endif
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

  function taskFromCard(card){
    if (!card) return null;

    const raw = card.getAttribute('data-task');
    if (!raw) return null;

    try {
      return JSON.parse(raw);
    } catch (err) {
      console.error('data-task invalido', err);
      return null;
    }
  }

  function openNodeEvidencesForCard(card){
    const task = taskFromCard(card);
    if (!task) return;

    if (typeof window.openTaskEvidencesModal === 'function') {
      window.openTaskEvidencesModal(task);
    } else if (typeof window.openEditTaskModal === 'function') {
      window.openEditTaskModal(task);
    }
  }

  function openEditForCard(card){
    const task = taskFromCard(card);
    if (!task || typeof window.openEditTaskModal !== 'function') return;
    window.openEditTaskModal(task);
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

      // Permitir acciones explícitas dentro de la tarjeta
      if (
        e.target.closest('.js-open-task-modal') ||
        e.target.closest('.js-open-node-evidences') ||
        e.target.closest('.js-open-task-activities') ||
        e.target.closest('.js-time-badge') ||
        e.target.closest('.js-open-files')
      ) {
        return;
      }

      e.preventDefault();
      e.stopPropagation();
    }, true);

  // ✅ Click solo en paperclip abre modal + archivos
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-node-evidences');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const card = btn.closest('.gp-card');
    openNodeEvidencesForCard(card);
  });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-task-modal');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    openEditForCard(btn.closest('.gp-card'));
  });

  // ✅ IMPORTANTÍSIMO: evitar que el drag se active cuando intentas clickear paperclip
    document.addEventListener('mousedown', (e) => {
      if (
        e.target.closest('.js-open-task-modal') ||
        e.target.closest('.js-open-node-evidences') ||
        e.target.closest('.js-open-task-activities') ||
        e.target.closest('.js-time-badge')
      ) {
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

  function fmtBusinessClock(totalSeconds){
    const s = Math.max(0, Math.floor(totalSeconds));
    const totalHours = Math.floor(s / 3600);
    const days = Math.floor(totalHours / 8);
    const hh = String(totalHours % 8).padStart(2,'0');
    const mm = String(Math.floor((s % 3600) / 60)).padStart(2,'0');
    const ss = String(s % 60).padStart(2,'0');
    if (days > 0) {
      return `${days}d ${hh}:${mm}:${ss}`;
    }
    return `${hh}:${mm}:${ss}`;
  }

  function setBadgeState(badge, state){
    badge.classList.remove('gp-time-badge--ok','gp-time-badge--late','gp-time-badge--nodue');
    badge.classList.add(state);
  }

  function normalizeDueDate(due){
    if (!(due instanceof Date) || isNaN(due.getTime())) return due;

    // Si la fecha limite viene sin hora efectiva, asumimos cierre de jornada.
    if (
      due.getHours() === 0 &&
      due.getMinutes() === 0 &&
      due.getSeconds() === 0 &&
      due.getMilliseconds() === 0
    ) {
      const normalized = new Date(due);
      normalized.setHours(17, 0, 0, 0);
      return normalized;
    }

    return due;
  }

  function updateTimeBadges(){
    const now = new Date();

    document.querySelectorAll('.js-time-badge').forEach(badge => {
      const slaStartedIso = (badge.getAttribute('data-sla-started-at') || '').trim();
      const slaDueIso = (badge.getAttribute('data-sla-due-at') || '').trim();
      const dueIso = (badge.getAttribute('data-due-at') || '').trim();
      const startedIso = (badge.getAttribute('data-started-at') || '').trim();
      const createdIso = (badge.getAttribute('data-created-at') || '').trim();
      const labelEl = badge.querySelector('.js-time-label');
      const textEl = badge.querySelector('.js-time-text');
      if (!textEl || !labelEl) return;

      const slaStarted = slaStartedIso ? new Date(slaStartedIso) : null;
      const slaDue = slaDueIso ? normalizeDueDate(new Date(slaDueIso)) : null;
      const due = dueIso ? normalizeDueDate(new Date(dueIso)) : null;
      const started = startedIso ? new Date(startedIso) : null;
      const created = createdIso ? new Date(createdIso) : null;
      const baseStart = (slaStarted && !isNaN(slaStarted.getTime()))
        ? slaStarted
        : ((created && !isNaN(created.getTime())) ? created : started);
      const limit = (slaDue && !isNaN(slaDue.getTime())) ? slaDue : due;

      // Si no hay fecha límite → contar ascendente desde inicio SLA/creación.
      if (!limit || isNaN(limit.getTime())){
        if (baseStart && !isNaN(baseStart.getTime())){
          const secs = businessSecondsBetween(baseStart, now);
          labelEl.textContent = 'En curso';
          textEl.textContent = fmtBusinessClock(secs);
          setBadgeState(badge, 'gp-time-badge--nodue');
        } else {
          labelEl.textContent = 'En curso';
          textEl.textContent = '00:00:00';
          setBadgeState(badge, 'gp-time-badge--nodue');
        }
        return;
      }

      // Fecha límite SLA o planificada:
      if (limit > now){
        // Regresiva (tiempo restante) → verde
        const secs = businessSecondsBetween(now, limit);
        labelEl.textContent = 'Restante';
        textEl.textContent = fmtBusinessClock(secs);
        setBadgeState(badge, 'gp-time-badge--ok');
      } else {
        // Ascendente (tiempo vencido) → rojo.
        const secs = businessSecondsBetween(limit, now);
        labelEl.textContent = 'Atraso';
        textEl.textContent = fmtBusinessClock(secs);
        setBadgeState(badge, 'gp-time-badge--late');
      }
    });
  }

  updateTimeBadges();
  setInterval(updateTimeBadges, 1000);
})();
</script>
