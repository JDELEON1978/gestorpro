<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'GestorPro - División DCPA' }}</title>

  {{-- Bootstrap 5 --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  {{-- Bootstrap Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  {{-- Estilos del proyecto --}}
  <link href="{{ asset('css/gestorpro.css') }}?v={{ time() }}" rel="stylesheet">
</head>

<body class="gp-body">

  {{-- Topbar INDE (sin logo.svg) --}}
<header style="
    background: linear-gradient(90deg, #003A70 0%, #005A9C 100%);
    height: 72px;
    display: flex;
    align-items: center;
    padding: 0 24px;
    border-bottom: 4px solid #002C55;
">
    <div class="d-flex align-items-center justify-content-between w-100">

       <div class="d-flex align-items-center gap-4">

    <div style="
        display:flex;
        align-items:center;
        justify-content:center;
    ">
        <img src="{{ asset('images/inde-logo.png') }}"
             alt="INDE"
             style="
                height:48px;
                width:auto;
                object-fit:contain;
             ">
    </div>

    <div style="line-height:1.2;">
        <div style="
            color:white;
            font-weight:700;
            font-size:20px;
            letter-spacing:.3px;
        ">
            GestorPro - División DCPA
        </div>

        <div style="
            color:rgba(255,255,255,0.75);
            font-size:13px;
            margin-top:2px;
        ">
            INDE
        </div>
    </div>

</div>


        <div>
            <div class="dropdown">
                <button class="btn btn-sm text-white dropdown-toggle"
                        style="background: rgba(255,255,255,0.15); border:none;"
                        data-bs-toggle="dropdown">
                    {{ auth()->user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item" type="submit">
                                <i class="bi bi-box-arrow-right me-1"></i> Salir
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</header>


  <main class="gp-main">
    @yield('content')
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
