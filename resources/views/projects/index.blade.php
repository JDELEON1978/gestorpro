<x-app-layout>
    <div class="max-w-5xl mx-auto p-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Proyectos</h1>
                <div class="text-sm opacity-70">{{ $workspace->name }}</div>
            </div>

            <a class="border rounded px-4 py-2" href="{{ route('workspaces.projects.create', $workspace) }}">
                Nuevo proyecto
            </a>
        </div>

        <div class="mt-6 space-y-3">
            @forelse($projects as $project)
                <div class="p-4 border rounded flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $project->name }}</div>
                        <div class="text-sm opacity-70">{{ $project->slug }}</div>

                        @if($project->description)
                            <div class="text-sm mt-2">{{ $project->description }}</div>
                        @endif
                    </div>

                    @if($project->archived)
                        <span class="text-xs border rounded px-2 py-1">Archivado</span>
                    @endif
                </div>
            @empty
                <div class="p-4 border rounded">
                    No hay proyectos aún.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $projects->links() }}
        </div>

    </div>
</x-app-layout>
