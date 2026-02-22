@extends('layouts.app')

@section('content')
<div class="container-fluid gp-app">

  <div class="d-flex align-items-center justify-content-between mb-2">
    <div>
      <div class="text-muted small">
        <a class="gp-link" href="{{ route('expedientes.index') }}">Expedientes</a> › {{ $expediente->correlativo }}
      </div>
      <h4 class="m-0">{{ $expediente->correlativo }} — {{ $expediente->titulo }}</h4>
      <div class="text-muted small mt-1">
        Proceso: <strong>{{ $expediente->proceso->nombre }}</strong>
        <span class="mx-2" style="opacity:.35;">|</span>
        Nodo actual: <strong>{{ $expediente->nodoActual->nombre ?? '—' }}</strong>
        <span class="mx-2" style="opacity:.35;">|</span>
        Estado: <span class="gp-status">{{ $expediente->estado }}</span>
      </div>
    </div>

    <a class="btn btn-sm btn-outline-secondary" href="{{ route('expedientes.index') }}">Volver</a>
  </div>

  <div class="gp-card mb-3">
    <div class="gp-card-title">Items del nodo actual</div>
    <div class="gp-card-desc">Aprobar obligatorios + evidencias antes de transicionar.</div>

    <div class="mt-3 d-flex flex-column gap-2">
      @foreach($itemsNodoActual as $ei)
        <div class="gp-panel p-3">

          <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
              <div class="fw-bold">{{ $ei->item->nombre }}</div>
              <div class="text-muted small mt-1">
                Estado:
                @if($ei->estado === 'APROBADO')
                  <span class="gp-status gp-status-ok">APROBADO</span>
                @elseif($ei->estado === 'RECHAZADO')
                  <span class="gp-status gp-status-bad">RECHAZADO</span>
                @else
                  <span class="gp-status">{{ $ei->estado }}</span>
                @endif

                @if((int)($ei->item->requiere_evidencia ?? 0) === 1)
                  <span class="ms-2">• requiere evidencia</span>
                @endif
              </div>

              @if($ei->evidencias->count())
                <div class="text-muted small mt-2">
                  Evidencias:
                  @foreach($ei->evidencias as $ev)
                    <span class="gp-badge">• {{ basename($ev->archivo_path) }}</span>
                  @endforeach
                </div>
              @endif
            </div>
          </div>

          <div class="row g-2 align-items-center mt-3">
            <div class="col-lg-6">
              <form method="POST" action="{{ route('expediente_items.evidencias.store', $ei) }}"
                    enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                @csrf
                <input type="file" name="archivo" class="gp-input" required>
                <button class="gp-btn"><i class="bi bi-upload"></i> Subir</button>
              </form>
            </div>

            <div class="col-lg-6 d-flex gap-2 justify-content-end flex-wrap">
              <form method="POST" action="{{ route('expediente_items.review', $ei) }}">
                @csrf
                <input type="hidden" name="accion" value="aprobar">
                <button class="gp-btn gp-btn-primary"><i class="bi bi-check2"></i> Aprobar</button>
              </form>

              <form method="POST" action="{{ route('expediente_items.review', $ei) }}" class="d-flex gap-2">
                @csrf
                <input type="hidden" name="accion" value="rechazar">
                <input name="observaciones" class="gp-input" placeholder="Motivo (opcional)" style="min-width:220px;">
                <button class="gp-btn gp-btn-danger"><i class="bi bi-x"></i> Rechazar</button>
              </form>
            </div>
          </div>

        </div>
      @endforeach
    </div>
  </div>

  <div class="gp-card">
    <div class="gp-card-title">Transicionar</div>
    <div class="gp-card-desc">Solo destinos conectados desde el nodo actual. El motor valida todo.</div>

    <form method="POST" action="{{ route('expedientes.transition', $expediente) }}" class="row g-2 align-items-end mt-2">
      @csrf

      <div class="col-md-4">
        <div class="gp-muted small mb-1 fw-bold">Destino</div>
        <select name="nodo_destino_id" class="gp-select" required>
          @foreach($destinos as $rel)
            <option value="{{ $rel->nodo_destino_id }}">
              {{ $rel->destino->nombre ?? ('Nodo '.$rel->nodo_destino_id) }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <div class="gp-muted small mb-1 fw-bold">Motivo (opcional)</div>
        <input name="motivo" class="gp-input" placeholder="Ej: pasa a revisión..." />
      </div>

      <div class="col-md-2 d-grid">
        <button class="gp-btn gp-btn-primary justify-content-center">
          <i class="bi bi-arrow-right"></i> Aplicar
        </button>
      </div>
    </form>
  </div>

</div>
@endsection