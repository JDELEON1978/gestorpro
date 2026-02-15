{{-- resources/views/tasks/views/tabla.blade.php --}}
@php
  $rows = collect();

  foreach($statuses as $st){
    $sid = $st->id;
    $tasks = $tasksByStatus[$sid] ?? collect();

    foreach($tasks as $t){
      $rows->push([
        'id' => $t->id,
        'title' => $t->title ?? ('Tarea #' . $t->id),
        'status' => $st->name,
        'assignee' => $t->assignee?->name ?? 'Sin asignar',
        'due_date' => !empty($t->due_date) ? \Illuminate\Support\Carbon::parse($t->due_date)->toDateString() : '—',
        'priority' => strtoupper($t->priority ?? '—'),
      ]);
    }
  }
@endphp

<div class="table-responsive">
  <table class="table align-middle">
    <thead>
      <tr class="text-muted small">
        <th style="width:90px;">ID</th>
        <th>Nombre</th>
        <th style="width:180px;">Estado</th>
        <th style="width:200px;">Asignado</th>
        <th style="width:140px;">Fecha límite</th>
        <th style="width:120px;">Prioridad</th>
      </tr>
    </thead>

    <tbody>
      @if($rows->count() === 0)
        <tr>
          <td colspan="6" class="text-center text-muted py-4">No hay tareas</td>
        </tr>
      @else
        @foreach($rows as $r)
          <tr>
            <td class="text-muted">#{{ $r['id'] }}</td>
            <td class="fw-semibold">{{ $r['title'] }}</td>
            <td><span class="badge rounded-pill text-bg-light">{{ $r['status'] }}</span></td>
            <td><i class="bi bi-person me-1"></i>{{ $r['assignee'] }}</td>
            <td><i class="bi bi-calendar3 me-1"></i>{{ $r['due_date'] }}</td>
            <td><span class="badge rounded-pill text-bg-secondary">{{ $r['priority'] }}</span></td>
          </tr>
        @endforeach
      @endif
    </tbody>
  </table>
</div>
