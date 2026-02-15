<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="card shadow-soft overflow-hidden">
        {{-- Header --}}
        <div class="p-5 border-b border-soft">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-xs uppercase tracking-wide" style="color: var(--muted);">
                        Proyecto
                    </div>
                    <div class="text-2xl font-semibold truncate">{{ $project->name }}</div>
                    <div class="text-sm mt-1" style="color: var(--muted);">
                        Vistas tipo ClickUp: Lista · Tablero · Tabla
                    </div>
                </div>

                <div class="flex gap-2 shrink-0">
                    <a class="px-4 py-2 rounded-lg text-white" style="background: var(--inde-blue);"
                       href="{{ route('projects.tasks.create', $project) }}">
                        + Nueva tarea
                    </a>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="mt-4 flex gap-2">
                @php
                    $tabs = [
                        'lista'   => 'Lista',
                        'tablero' => 'Tablero',
                        'tabla'   => 'Tabla',
                    ];
                @endphp

                @foreach($tabs as $key => $label)
                    <a href="{{ route('projects.tasks.index', $project) }}?view={{ $key }}"
                       class="px-4 py-2 rounded-lg border border-soft hover:bg-slate-50 text-sm
                              {{ $viewMode === $key ? 'font-semibold bg-slate-50' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Body --}}
        <div class="p-5 bg-[var(--bg-app)]">
            @if($viewMode === 'lista')
                @include('tasks.views.lista')
            @elseif($viewMode === 'tabla')
                @include('tasks.views.tabla')
            @else
                @include('tasks.views.tablero')
            @endif
        </div>
    </div>
</x-app-layout>
