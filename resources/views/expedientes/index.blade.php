@extends('layouts.app')

@section('content')
<div class="container-fluid gp-app">

  <div class="d-flex align-items-center justify-content-between mb-2">
    <div class="d-flex gap-2 align-items-center">
      <h4 class="m-0">Expedientes</h4>
      <span class="text-muted small">Ejecución real del workflow</span>
    </div>
    <a class="btn btn-sm btn-outline-primary" href="{{ url('/process-builder') }}">Ir al Builder</a>
  </div>

  <div class="gp-card mb-3">
    <div class="gp-card-title">Crear expediente</div>
    <div class="gp-card-desc">Se asigna al nodo inicial y crea los items requeridos.</div>

    <form method="POST" action="{{ route('expedientes.store') }}" class="row g-2 align-items-end mt-2">
      @csrf

      <div class="col-md-4">
        <div class="gp-muted small mb-1 fw-bold">Proceso</div>
        <select name="proceso_id" class="gp-select" required>
          @foreach($procesos as $p)
            <option value="{{ $p->id }}">{{ $p->nombre }} {{ $p->version ? 'v'.$p->version : '' }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <div class="gp-muted small mb-1 fw-bold">Título</div>
        <input name="titulo" class="gp-input" placeholder="Ej: Solicitud de compra..." required>
      </div>

      <div class="col-md-2 d-grid">
        <button class="gp-btn gp-btn-primary justify-content-center">+ Crear</button>
      </div>
    </form>
  </div>

  <div class="gp-card">
    <div class="gp-card-title">Listado</div>

    <div class="table-responsive mt-2">
      <table class="gp-table">
        <thead>
          <tr>
            <th>Correlativo</th>
            <th>Proceso</th>
            <th>Título</th>
            <th>Nodo actual</th>
            <th>Estado</th>
            <th style="text-align:right;">Acción</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $r)
            <tr>
              <td>{{ $r->correlativo }}</td>
              <td>{{ $r->proceso->nombre ?? '-' }}</td>
              <td>{{ $r->titulo }}</td>
              <td>{{ $r->nodoActual->nombre ?? '—' }}</td>
              <td><span class="gp-status">{{ $r->estado }}</span></td>
              <td style="text-align:right;">
                <a class="gp-link" href="{{ route('expedientes.show', $r) }}">Abrir</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $rows->links() }}</div>
  </div>

</div>
@endsection