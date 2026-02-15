{{-- resources/views/tasks/views/tablero.blade.php --}}
<style>
  .gp-kanban-wrap{ overflow-x:auto; padding-bottom:8px; }
  .gp-kanban{ display:flex; gap:16px; min-width: 980px; }
  .gp-col{
    width: 320px;
    background: #fff;
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 16px;
    padding: 12px;
  }
  .gp-col-head{
    display:flex; align-items:center; justify-content:space-between;
    padding: 6px 4px 10px 4px;
    border-bottom: 1px solid rgba(0,0,0,.06);
    margin-bottom: 10px;
  }
  .gp-card{
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 14px;
    padding: 12px;
    background:#fff;
    margin-bottom: 10px;
  }
  .gp-meta{ font-size: 13px; color: #6c757d; display:flex; gap:14px; flex-wrap:wrap; margin-top:6px; }
</style>

<div class="gp-kanban-wrap">
  <div class="gp-kanban">
    @foreach($statuses as $st)
      @php
        $sid = $st->id;
        $tasks = $tasksByStatus[$sid] ?? collect();
      @endphp

      <div class="gp-col">
        <div class="gp-col-head">
          <div class="d-flex align-items-center gap-2">
            <div class="fw-bold">{{ $st->name }}</div>
            <span class="badge rounded-pill text-bg-light">{{ $tasks->count() }}</span>
          </div>
          <button class="btn btn-sm gp-btn" type="button" disabled><i class="bi bi-plus-lg"></i></button>
        </div>

        @if($tasks->count() === 0)
          <div class="text-muted small py-2">Sin tareas</div>
        @else
          @foreach($tasks as $t)
            <div class="gp-card">
              <div class="d-flex align-items-start justify-content-between gap-2">
                <div class="min-w-0">
                  <div class="fw-bold text-truncate">{{ $t->title ?? ('Tarea #' . $t->id) }}</div>
                  @if(!empty($t->description))
                    <div class="text-muted small text-truncate">{{ $t->description }}</div>
                  @endif
                </div>
                <span class="badge rounded-pill text-bg-light">{{ strtoupper($t->priority ?? '—') }}</span>
              </div>

              <div class="gp-meta">
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
          @endforeach
        @endif
      </div>
    @endforeach
  </div>
</div>
