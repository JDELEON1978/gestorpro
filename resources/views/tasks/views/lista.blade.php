{{-- resources/views/tasks/views/lista.blade.php --}}
<div class="d-flex flex-column gap-3">

  @foreach($statuses as $st)
    @php $items = $tasksByStatus[$st->id] ?? collect(); @endphp

    <div class="border rounded-3 p-3 bg-white" style="border-color: var(--border);">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="fw-bold">{{ $st->name }}</div>
        <span class="badge text-bg-secondary">{{ $items->count() }}</span>
      </div>

      @if($items->count() === 0)
        <div class="text-muted small">Sin tareas</div>
      @else
        <div class="list-group list-group-flush">
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

              // IMPORTANTE: JSON seguro para HTML + parseable en JS
              $payloadJson = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
              );
            @endphp

            <div class="list-group-item js-task task-clickable"
                 style="border-left:0; border-right:0; cursor:pointer;"
                 data-task="{{ $payloadJson }}">
              <div class="d-flex align-items-start justify-content-between gap-3">
                <div class="min-w-0">
                  <div class="fw-semibold text-truncate">{{ $t->title }}</div>

                  @if($t->description)
                    <div class="text-muted small text-truncate">{{ $t->description }}</div>
                  @endif

                  <div class="text-muted small mt-1">
                    <i class="bi bi-calendar-event me-1"></i>
                    Inicio: {{ $t->start_at ? $t->start_at->format('Y-m-d') : '—' }}
                    &nbsp; | &nbsp;
                    Fin: {{ $t->due_at ? $t->due_at->format('Y-m-d') : '—' }}
                  </div>
                </div>

                <div class="text-end">
                    {{-- Files --}}
                    <button type="button"
                            class="btn btn-sm gp-btn js-open-files"
                            data-task-id="{{ $t->id }}"
                            title="Archivos">
                    <i class="bi bi-paperclip"></i>
                    </button>

                    {{-- Asignaciones (solo ícono por ahora) --}}
                    <button type="button"
                            class="btn btn-sm gp-btn"
                            title="Asignar (próximamente)"
                            disabled>
                    <i class="bi bi-person-plus"></i>
                    </button>
                  @if($t->priority)
                    <span class="badge text-bg-primary">P{{ $t->priority }}</span>
                  @else
                    <span class="text-muted small">—</span>
                  @endif
                </div>
              </div>
            </div>

          @endforeach
        </div>
      @endif
    </div>
  @endforeach

</div>
