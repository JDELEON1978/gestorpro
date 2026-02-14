<?php
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name') }}</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-appbg text-slate-800">
  <header class="border-b border-border bg-white">
    <div class="mx-auto max-w-7xl px-4 py-3 flex items-center gap-3">
      <div class="w-2 h-8 bg-inde-verde rounded"></div>
      <div class="font-semibold tracking-tight">
        <span class="text-inde-azul">{{ config('app.name') }}</span>
        <span class="text-slate-400 text-sm">| Dashboard</span>
      </div>

      <div class="ml-auto flex items-center gap-3">
        @auth
          <span class="text-sm text-slate-600">{{ auth()->user()->name }}</span>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm text-slate-600 hover:text-inde-azul">Salir</button>
          </form>
        @endauth
      </div>
    </div>
    <div class="h-1 bg-gradient-to-r from-inde-azul via-inde-celeste to-inde-verde"></div>
  </header>

  <main class="mx-auto max-w-7xl px-4 py-6">
    @if(session('success'))
      <div class="mb-4 rounded-lg border border-border bg-white p-3 text-sm">
        ✅ {{ session('success') }}
      </div>
    @endif

    {{ $slot }}
  </main>
</body>
</html>
