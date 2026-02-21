{{-- resources/views/tasks/views/tabla.blade.php --}}
<div class="table-responsive">
  <table class="table table-sm table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th>Título</th>
        <th>Estado</th>
        <th>Inicio</th>
        <th>Fin</th>
        <th>Prioridad</th>
      </tr>
    </thead>
    <tbody>
      @foreach($statuses as $st)
        @php $items = $tasksByStatus[$st->id] ?? collect(); @endphp

        @foreach($items as $t)
          @php
            $payload = [
              'id' => $t->id,
              'title' => $t->title,
              'description' => $t->description,
              'status_id' => $t->status_id,
              'priority' => $t->priority,
              'start_at' => $t->start_at ? $t->start_at->format('Y-m-d') : null,
              'due_at' => $t->due_at ? $t->due_at->format('Y-m-d') : null,
            ];

            $payloadJson = json_encode(
              $payload,
              JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            );
          @endphp

          <tr class="js-task task-clickable"
              style="cursor:pointer;"
              data-task="{{ $payloadJson }}">
            <td>{{ $t->title }}</td>
            <td>{{ $st->name }}</td>
            <td>{{ $t->start_at ? $t->start_at->format('Y-m-d') : '—' }}</td>
            <td>{{ $t->due_at ? $t->due_at->format('Y-m-d') : '—' }}</td>
            <td>
              @if($t->priority)
                <span class="badge text-bg-primary">P{{ $t->priority }}</span>
              @else
                —
              @endif
            </td>
          </tr>

        @endforeach
      @endforeach
    </tbody>
  </table>
</div>
