<?php
<x-app-shell>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Columna principal --}}
    <div class="lg:col-span-2">
      <div class="bg-card border border-border rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <h1 class="text-xl font-semibold text-inde-azul">Dashboard</h1>
          <span class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700">
            {{ $workspaces->count() }} workspaces
          </span>
        </div>

        @if($workspaces->isEmpty())
          <div class="text-slate-600 text-sm">
            Aún no tienes workspaces. Crea el primero para empezar.
          </div>
        @else
          <div class="space-y-2">
            @foreach($workspaces as $ws)
              <div class="flex items-center justify-between rounded-lg border border-border bg-white p-3">
                <div>
                  <div class="font-medium">{{ $ws->name }}</div>
                  <div class="text-xs text-slate-500">Workspace</div>
                </div>
                <button class="text-sm text-inde-azul hover:underline">
                  Abrir
                </button>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    {{-- Panel derecho: crear workspace --}}
    <div>
      <div class="bg-card border border-border rounded-xl shadow-sm p-5">
        <h2 class="text-lg font-semibold text-inde-azul mb-3">Crear Workspace</h2>

        <form method="POST" action="{{ route('workspaces.store') }}" class="space-y-3">
          @csrf

          <div>
            <label class="block text-sm text-slate-700 mb-1">Nombre</label>
            <input
              name="name"
              value="{{ old('name') }}"
              class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-inde-celeste"
              placeholder="Ej: INDE - EGEE / Yottabi / Mareste"
              required
            >
            @error('name')
              <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
            @enderror
          </div>

          <button class="w-full rounded-lg bg-inde-azul text-white py-2 text-sm font-medium hover:opacity-95">
            Crear
          </button>

          <div class="text-xs text-slate-500">
            El creador queda como <b>owner</b>. Luego podrás invitar <b>admin</b> y <b>member</b>.
          </div>
        </form>
      </div>
    </div>

  </div>
</x-app-shell>
