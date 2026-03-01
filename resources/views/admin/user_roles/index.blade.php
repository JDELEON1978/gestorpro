@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="m-0">Asignación de Roles (Usuarios)</h3>

        <form method="GET" action="{{ route('admin.user_roles.index') }}" class="d-flex gap-2">
            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Buscar por nombre o email" style="min-width: 280px;">
            <button class="btn btn-outline-primary">Buscar</button>
        </form>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%;">Usuario</th>
                            <th style="width: 25%;">Cuenta</th>
                            <th>Roles de proceso</th>
                            <th style="width: 140px;" class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $u)
                            @php
                                $selected = $u->roles->pluck('id')->all();
                            @endphp

                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $u->name }}</div>
                                    <div class="text-muted" style="font-size: 12px;">{{ $u->email }}</div>
                                </td>

                                <td>
                                    <span class="badge bg-secondary">{{ $u->role }}</span>
                                    @if (!$u->active)
                                        <span class="badge bg-danger ms-1">INACTIVO</span>
                                    @endif
                                </td>

                                <td>
                                    <form method="POST" action="{{ route('admin.user_roles.update', $u) }}">
                                        @csrf
                                        @method('PUT')

                                        {{-- Multi-select (Bootstrap). Simple y funcional. --}}
                                        <select name="roles[]" class="form-select" multiple size="3">
                                            @foreach ($roles as $r)
                                                <option value="{{ $r->id }}" @selected(in_array($r->id, $selected))>
                                                    {{ $r->nombre }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div class="text-muted mt-1" style="font-size: 12px;">
                                            Tip: Ctrl/Cmd para seleccionar varios.
                                        </div>
                                </td>

                                <td class="text-end">
                                        <button class="btn btn-primary btn-sm">
                                            Guardar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        @if ($users->count() === 0)
                            <tr>
                                <td colspan="4" class="text-center text-muted p-4">No hay usuarios para mostrar.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection