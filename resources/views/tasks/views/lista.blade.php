{{-- resources/views/tasks/views/lista.blade.php --}}
@php
  // $tasksByStatus: array o collection indexado por status_id
@endphp

@foreach($statuses as $st)
  @php
    $sid = $st->id;
    $tasks = $tasksByStatus[$sid] ?? collect();
  @endphp

  <div class="mb-4">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="d-flex align-items-center gap-2">
        <div class="fw-bold">{{ $st->name }}</div>
        <span class="badge rounded-pill text-bg-light">{{ $tasks->count() }}</span>
      </div>
    </div>

    @if($tasks->count() === 0)
      <div class="text-muted small">Sin tareas</div>
    @else
      <div class="list-group">
        @foreach($tasks as $t)
          <div class="list-group-item d-flex align-items-center justify-content-between">
            <div class="min-w-0">
              <div class="fw-semibold text-truncate">{{ $t->title ?? ('Tarea #' . $t->id) }}</div>
              <div class="text-muted small d-flex gap-3 mt-1 flex-wrap">
                <span class="d-inline-flex align-items-center gap-1">
                  <i class="bi bi-person"></i> {{ $t->assignee?->name ?? 'Sin asignar' }}
                </span>
                @if(!empty($t->due_date))
                  <span class="d-inline-flex align-items-center gap-1">
                    <i class="bi bi-calendar3"></i> {{ \Illuminate\Support\Carbon::parse($t->due_date)->toDateString() }}
                  </span>
                @endif
              </div>
            </div>
            <span class="badge rounded-pill text-bg-secondary">{{ strtoupper($t->priority ?? '—') }}</span>
          </div>
        @endforeach
      </div>
    @endif
  </div>
@endforeach
