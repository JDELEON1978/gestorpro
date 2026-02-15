<div class="gp-board">
    <div class="gp-columns">

        @foreach($statuses as $st)
            @php
                $tasks = $tasksByStatus[$st->id] ?? collect();
            @endphp

            <div class="gp-col">
                <div class="gp-col__top">
                    <div class="gp-col__badge">
                        <span class="gp-dot" style="background: {{ $st->color ?? '#64748B' }}"></span>
                        <span>{{ strtoupper($st->name) }}</span>
                        <span class="text-muted">({{ $tasks->count() }})</span>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted">Próximo: mover / ordenar</span></li>
                        </ul>
                    </div>
                </div>

                @forelse($tasks as $t)
                    <div class="gp-card">
                        <div class="gp-card__title">{{ $t->title }}</div>

                        <div class="gp-card__meta">
                            @if($t->priority)
                                <span class="gp-pill">P{{ $t->priority }}</span>
                            @endif

                            @if($t->due_at)
                                <span class="gp-pill">
                                    <i class="bi bi-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($t->due_at)->format('Y-m-d') }}
                                </span>
                            @endif

                            @if($t->assignee)
                                <span class="gp-pill">
                                    <i class="bi bi-person-circle me-1"></i>{{ $t->assignee->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">Sin tareas</div>
                @endforelse

                <a class="btn btn-sm btn-outline-secondary w-100 mt-2"
                   href="{{ route('projects.tasks.create', $project) }}">
                    <i class="bi bi-plus-lg me-1"></i> Añadir tarea
                </a>
            </div>
        @endforeach

    </div>
</div>
