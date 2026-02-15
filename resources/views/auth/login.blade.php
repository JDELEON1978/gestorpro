@extends('layouts.guest', ['title' => 'Ingresar'])

@section('content')

<div class="gp-login-card text-center">

    {{-- Logo INDE --}}
    <img src="{{ asset('images/inde.png') }}" class="gp-login-logo mb-3" alt="INDE">

    <div class="gp-login-title">Sistema GestorPro</div>
    <div class="gp-login-sub mb-4">División DCPA</div>

    @if ($errors->any())
        <div class="alert alert-danger small text-start">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3 text-start">
            <label class="form-label fw-semibold">Usuario</label>
            <input type="email"
                   name="email"
                   class="form-control form-control-lg"
                   required
                   autofocus>
        </div>

        <div class="mb-3 text-start">
            <label class="form-label fw-semibold">Contraseña</label>
            <input type="password"
                   name="password"
                   class="form-control form-control-lg"
                   required>
        </div>

        <div class="form-check mb-3 text-start">
            <input class="form-check-input" type="checkbox" name="remember">
            <label class="form-check-label small">
                Recordarme
            </label>
        </div>

        <button type="submit" class="btn btn-primary gp-login-btn">
            Ingresar
        </button>
    </form>
</div>

@endsection
