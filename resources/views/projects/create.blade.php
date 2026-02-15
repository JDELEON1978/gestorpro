<x-app-layout>
    <div class="max-w-xl mx-auto p-6">
        <h1 class="text-2xl font-semibold">Nuevo proyecto</h1>
        <div class="text-sm opacity-70">{{ $workspace->name }}</div>

        <form class="mt-6 space-y-4" method="POST" action="{{ route('workspaces.projects.store', $workspace) }}">
            @csrf

            <div>
                <label class="block text-sm font-medium">Nombre</label>
                <input
                    name="name"
                    class="mt-1 w-full border rounded p-2"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">Descripción</label>
                <textarea
                    name="description"
                    class="mt-1 w-full border rounded p-2"
                    rows="4"
                >{{ old('description') }}</textarea>
                @error('description')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button class="border rounded px-4 py-2" type="submit">
                Guardar
            </button>
        </form>
    </div>
</x-app-layout>
