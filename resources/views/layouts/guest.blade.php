<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Ingresar - GestorPro' }}</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Iconos --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root{
            --gp-blue:#167DB7;
            --gp-blue-dark:#0F5F8F;
        }

        body{
            background:#eef2f6;
        }

        /* Cintillo superior */
        .gp-login-topbar{
            background:var(--gp-blue);
            color:#fff;
            height:56px;
            display:flex;
            align-items:center;
        }

        .gp-login-topbar .brand{
            font-weight:800;
            font-size:16px;
        }

        .gp-login-topbar .right{
            font-weight:600;
        }

        /* Centro */
        .gp-login-wrapper{
            min-height:calc(100vh - 56px);
            display:flex;
            align-items:center;
            justify-content:center;
        }

        /* Card */
        .gp-login-card{
            width:100%;
            max-width:420px;
            background:#fff;
            border-radius:12px;
            padding:28px;
            box-shadow:0 8px 30px rgba(0,0,0,0.12);
            border:1px solid #e5e7eb;
        }

        .gp-login-logo{
            max-width:180px;
        }

        .gp-login-title{
            font-weight:700;
            margin-top:10px;
        }

        .gp-login-sub{
            font-size:13px;
            color:#6b7280;
        }

        .gp-login-btn{
            background:var(--gp-blue);
            border:none;
            width:100%;
            padding:10px;
            font-weight:700;
            border-radius:8px;
        }

        .gp-login-btn:hover{
            background:var(--gp-blue-dark);
        }

    </style>
</head>

<body>

    {{-- CINTILLO SUPERIOR --}}
    <div class="gp-login-topbar">
        <div class="container-fluid d-flex justify-content-between">
            <div class="brand">GestorPro</div>
            <div class="right">Ingresar</div>
        </div>
    </div>

    {{-- CONTENIDO --}}
    <div class="gp-login-wrapper">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
