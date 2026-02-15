<x-app-layout>
    <div class="p-4">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-semibold">Workspaces</h1>
            <a href="{{ route('workspaces.create') }}" class="underline">Nuevo</a>
        </div>

        @if(session('success'))
            <div class="mb-3">{{ session('success') }}</div>
        @endif

        @forelse($workspaces as $w)
            <div class="p-3 border rounded mb-2">{{ $w->name }}</div>
        @empty
            <div>No hay workspaces todavía.</div>
        @endforelse
    </div>
</x-app-layout>
