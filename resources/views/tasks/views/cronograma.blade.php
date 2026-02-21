{{-- resources/views/tasks/views/cronograma.blade.php --}}
@php
  use Illuminate\Support\Carbon;

  // 1) Fuente de tareas:
  // - Si el controlador manda $tasks, lo usamos
  // - Si no, lo armamos desde $tasksByStatus (lo que ya mandas hoy)
  $rows = $tasks ?? collect();
  if($rows->isEmpty() && isset($tasksByStatus) && is_array($tasksByStatus)){
    $rows = collect($tasksByStatus)->flatten(1);
  }

  // Mapa de nombres de status para no depender de $t->status
  $statusMap = isset($statuses)
    ? $statuses->mapWithKeys(fn($s) => [$s->id => $s->name])
    : collect();

  // 2) Normalizamos rango de fechas para pintar barras
  $dates = $rows->flatMap(function($t){
    $a = $t->start_at ? Carbon::parse($t->start_at) : null;
    $b = $t->due_at   ? Carbon::parse($t->due_at)   : null;
    return collect([$a, $b])->filter();
  });

  $min = $dates->min();
  $max = $dates->max();

  // Evitar división por cero
  $spanDays = ($min && $max) ? max(1, $min->diffInDays($max)) : 1;

  $sorted = $rows->sortBy(function($t){
    return $t->start_at ?? $t->due_at ?? now();
  })->values();
@endphp

@if($sorted->isEmpty())
  <div class="text-muted">No hay tareas para mostrar en cronograma.</div>
@else

  <div class="d-flex align-items-center justify-content-between mb-2">
    <div class="small text-muted">
      Rango:
      <strong>{{ $min ? Carbon::parse($min)->format('Y-m-d') : '—' }}</strong>
      a
      <strong>{{ $max ? Carbon::parse($max)->format('Y-m-d') : '—' }}</strong>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead>
        <tr>
          <th style="width: 38%;">Tarea</th>
          <th style="width: 12%;">Inicio</th>
          <th style="width: 12%;">Fin</th>
          <th style="width: 38%;">Cronograma</th>
        </tr>
      </thead>
      <tbody>
        @foreach($sorted as $t)
          @php
            $s = $t->start_at ? Carbon::parse($t->start_at) : ($t->due_at ? Carbon::parse($t->due_at) : null);
            $e = $t->due_at   ? Carbon::parse($t->due_at)   : ($t->start_at ? Carbon::parse($t->start_at) : null);

            $leftPct = 0;
            $widthPct = 0;

            if($min && $max && $s && $e){
              $startOff = $min->diffInDays($s, false);
              $endOff   = $min->diffInDays($e, false);

              $startOff = max(0, $startOff);
              $endOff   = max($startOff, $endOff);

              $leftPct  = ($startOff / $spanDays) * 100;
              $widthPct = (max(1, ($endOff - $startOff)) / $spanDays) * 100;
            }

            // Payload para abrir el modal (igual que tu JS espera)
            $payload = [
              'id' => $t->id,
              'title' => $t->title,
              'description' => $t->description,
              'status_id' => $t->status_id,
              'priority' => $t->priority,
              'start_at' => $t->start_at ? Carbon::parse($t->start_at)->format('Y-m-d') : null,
              'due_at' => $t->due_at ? Carbon::parse($t->due_at)->format('Y-m-d') : null,
            ];

            // IMPORTANTÍSIMO para no romper HTML/Blade
            $payloadJson = json_encode(
              $payload,
              JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            );

            $statusName = $statusMap[$t->status_id] ?? '';
          @endphp

          <tr class="js-task task-clickable" style="cursor:pointer;" data-task="{{ $payloadJson }}">
            <td>
              <div class="fw-semibold text-truncate" style="max-width: 420px;">
                {{ $t->title }}
              </div>
              <div class="small text-muted">
                {{ $statusName }}
              </div>
            </td>
            <td class="small">
              {{ $t->start_at ? Carbon::parse($t->start_at)->format('Y-m-d') : '—' }}
            </td>
            <td class="small">
              {{ $t->due_at ? Carbon::parse($t->due_at)->format('Y-m-d') : '—' }}
            </td>
            <td>
              <div style="position:relative; height:14px; background:rgba(0,0,0,.06); border-radius:999px; overflow:hidden;">
                <div style="
                    position:absolute;
                    left: {{ $leftPct }}%;
                    width: {{ $widthPct }}%;
                    top:0; bottom:0;
                    background: rgba(0,90,156,.65);
                "></div>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif
