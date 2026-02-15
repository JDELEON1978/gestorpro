<x-app-layout>
    <div class="p-4 max-w-md">
        <h1 class="text-xl font-semibold mb-4">Crear Workspace</h1>

        <form method="POST" action="{{ route('workspaces.store') }}">
            @csrf
            <div class="mb-3">
                <label class="block mb-1">Nombre</label>
                <input name="name" class="border rounded w-full p-2" required />
                @error('name') <div class="text-sm">{{ $message }}</div> @enderror
            </div>

            <button class="border rounded px-4 py-2">Guardar</button>
        </form>
    </div>
</x-app-layout>
