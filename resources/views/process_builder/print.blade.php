<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Impresion Proceso - {{ $proceso->nombre }}</title>
  <style>
    :root{
      --ink:#243447;
      --line:#4b5563;
      --soft:#eef3f7;
      --accent:#0b4f88;
      --node-width:200px;
      --node-min-height:64px;
    }

    * { box-sizing: border-box; }
    html, body { margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; color:var(--ink); background:#f3f6fa; }

    .screen-toolbar{
      position:sticky;
      top:0;
      z-index:20;
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      padding:12px 18px;
      background:#0b4f88;
      color:#fff;
    }

    .screen-toolbar .actions{
      display:flex;
      gap:8px;
    }

    .screen-toolbar button,
    .screen-toolbar a{
      border:1px solid rgba(255,255,255,.35);
      background:#fff;
      color:#0b4f88;
      padding:8px 12px;
      border-radius:6px;
      text-decoration:none;
      font-size:14px;
      cursor:pointer;
    }

    .print-sheet{
      margin:14px auto;
      background:#fff;
      box-shadow:0 12px 28px rgba(0,0,0,.08);
      overflow:hidden;
    }

    .sheet-a3{
      width:420mm;
      min-height:297mm;
      padding:12mm;
      position:relative;
    }

    .sheet-letter{
      width:279.4mm;
      min-height:215.9mm;
      padding:10mm;
      position:relative;
    }

    .process-header{
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      margin-bottom:6mm;
      gap:16px;
    }

    .process-title h1{
      margin:0 0 4px;
      font-size:24px;
      line-height:1.15;
    }

    .process-title .meta{
      font-size:13px;
      color:#5b6470;
      line-height:1.5;
    }

    .process-flow-wrap{
      position:relative;
      height:243mm;
      border:1px solid #b9c3cf;
      background:
        radial-gradient(circle, rgba(36,52,71,.12) 1px, transparent 1px) 0 0 / 18px 18px,
        #fff;
      overflow:hidden;
    }

    .process-frame{
      position:relative;
      width:100%;
      height:100%;
      overflow:hidden;
    }

    .process-flow{
      position:absolute;
      left:0;
      top:0;
      transform-origin:top left;
    }

    .flow-svg{
      position:absolute;
      inset:0;
      width:100%;
      height:100%;
      overflow:visible;
      pointer-events:none;
    }

    .print-port{
      position:absolute;
      width:14px;
      height:14px;
      opacity:0;
      pointer-events:none;
    }

    .print-node{
      position:absolute;
      width:var(--node-width);
      min-height:var(--node-min-height);
      padding:9px 9px 7px;
      border:1px solid rgba(15,23,42,.12);
      border-left:4px solid #7aa6d6;
      border-radius:11px;
      background:#fff;
      box-shadow:0 8px 18px rgba(15,23,42,.08);
    }

    .print-node[data-tipo="actividad"]{ border-left-color:#8bc8a8; }
    .print-node[data-tipo="decision"]{ border-left-color:#e7c15f; }
    .print-node[data-tipo="fin"]{ border-left-color:#d98f97; }
    .print-node[data-tipo="conector"]{ border-left-color:#adb5bd; }
    .print-node[data-tipo="inicio"]{ border-left-color:#76a7ea; }

    .node-edit-badge{
      width:22px;
      height:22px;
      border:1px solid rgba(15,23,42,.12);
      border-radius:6px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:#fff;
      color:#475569;
      font-size:11px;
      margin-bottom:7px;
    }

    .print-node .node-name{
      font-weight:700;
      font-size:11px;
      line-height:1.3;
      margin-bottom:3px;
    }

    .print-node .node-meta{
      font-size:9px;
      color:#5b6470;
      line-height:1.25;
    }

    .link-label{
      font-size:9px;
      fill:#4b5563;
    }

    .title-block{
      position:absolute;
      right:12mm;
      bottom:12mm;
      width:112mm;
      border:1px solid #4b5563;
      background:#fff;
    }

    .title-block-grid{
      display:grid;
      grid-template-columns:34mm 1fr;
      min-height:32mm;
    }

    .title-block-logo{
      border-right:1px solid #4b5563;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:4mm;
      background:#f6f9fc;
    }

    .title-block-logo img{
      max-width:100%;
      max-height:20mm;
      object-fit:contain;
    }

    .title-block-info{
      display:grid;
      grid-template-rows:repeat(5, auto);
    }

    .tb-row{
      display:grid;
      grid-template-columns:28mm 1fr;
      border-bottom:1px solid #4b5563;
      font-size:10px;
    }

    .tb-row:last-child{ border-bottom:none; }

    .tb-label{
      padding:2.4mm 2.6mm;
      border-right:1px solid #4b5563;
      background:#f2f5f8;
      font-weight:700;
    }

    .tb-value{
      padding:2.4mm 2.6mm;
    }

    .matrix-title{
      display:flex;
      align-items:center;
      gap:18px;
      margin-bottom:5mm;
      font-weight:700;
      color:#4d4d4d;
    }

    .matrix-title .index{
      font-size:22px;
    }

    .matrix-title .name{
      font-size:24px;
    }

    .matrix-subtitle{
      margin-bottom:4mm;
      color:#6b7280;
      font-size:9px;
    }

    .matrix-wrap{
      border:1px solid #4b5563;
    }

    table.matrix{
      width:100%;
      border-collapse:collapse;
      table-layout:fixed;
    }

    .matrix th,
    .matrix td{
      border:1px solid #4b5563;
      padding:5px 6px;
      vertical-align:top;
      font-size:9.2px;
      line-height:1.28;
      word-break:break-word;
    }

    .matrix thead th{
      text-align:center;
      font-weight:700;
      background:#fafbfc;
    }

    .matrix tbody .process-band td{
      font-weight:700;
      background:#f6f7f8;
      font-size:11px;
      text-align:left;
    }

    .text-center{ text-align:center; }
    .na{ color:#6b7280; }
    .matrix-page{ page-break-after:always; }
    .matrix-page:last-of-type{ page-break-after:auto; }

    @media print {
      body{ background:#fff; }
      .screen-toolbar{ display:none !important; }
      .print-sheet{
        margin:0;
        box-shadow:none;
        overflow:visible;
      }
      .sheet-a3{ page: process-a3; }
      .sheet-letter{ page: process-letter; }
    }

    @page process-a3 {
      size:A3 landscape;
      margin:10mm;
    }

    @page process-letter {
      size:letter landscape;
      margin:10mm;
    }
  </style>
</head>
<body>
  <div class="screen-toolbar">
    <div>
      Impresion del Proceso: <strong>{{ $proceso->nombre }}</strong>
    </div>
    <div class="actions">
      <button type="button" onclick="window.print()">Imprimir</button>
      <a href="{{ route('process.builder', $proceso) }}">Volver</a>
    </div>
  </div>

  @php
    $nodeWidth = 200;
    $nodeHeight = 92;
    $flowPadding = 36;
    $minX = $nodos->min('pos_x') ?? 0;
    $minY = $nodos->min('pos_y') ?? 0;
    $maxX = $nodos->max(fn($n) => ($n->pos_x ?? 0) + $nodeWidth) ?? $nodeWidth;
    $maxY = $nodos->max(fn($n) => ($n->pos_y ?? 0) + $nodeHeight) ?? $nodeHeight;
    $sourceWidth = max(900, ($maxX - $minX) + ($flowPadding * 2));
    $sourceHeight = max(520, ($maxY - $minY) + ($flowPadding * 2));
    $frameWidthPx = 1420;
    $frameHeightPx = 860;
    $flowScale = min($frameWidthPx / $sourceWidth, $frameHeightPx / $sourceHeight, 1.24);
    $offsetX = $flowPadding - $minX;
    $offsetY = $flowPadding - $minY;
    $docRefItems = $proceso->items->where('categoria', 'DOCUMENTO')->pluck('nombre')->implode(', ');
    $matrixChunks = $nodos->values()->chunk(4);
    $printLinks = $relaciones->map(function ($rel) {
        return [
            'id' => $rel->id,
            'nodo_origen_id' => $rel->nodo_origen_id,
            'nodo_destino_id' => $rel->nodo_destino_id,
            'condicion' => $rel->condicion,
        ];
    })->values();
  @endphp

  <section class="print-sheet sheet-a3">
    <div class="process-header">
      <div class="process-title">
        <h1>{{ $proceso->nombre }}</h1>
        <div class="meta">
          <div><strong>Codigo:</strong> {{ $proceso->codigo ?: 'N/D' }}</div>
          <div><strong>Version:</strong> {{ $proceso->version ?: 'N/D' }}</div>
          <div><strong>Estado:</strong> {{ $proceso->estado ?: 'N/D' }}</div>
          <div><strong>Descripcion:</strong> {{ $proceso->descripcion ?: 'Sin descripcion registrada' }}</div>
        </div>
      </div>
    </div>

    <div class="process-flow-wrap">
      <div class="process-frame">
        <div class="process-flow" style="width: {{ $sourceWidth }}px; height: {{ $sourceHeight }}px; transform: scale({{ $flowScale }});">
          <svg class="flow-svg" id="printLinkLayer" viewBox="0 0 {{ $sourceWidth }} {{ $sourceHeight }}" preserveAspectRatio="xMinYMin meet">
            <defs>
              <marker id="printArrow" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto" markerUnits="strokeWidth">
                <path d="M0,0 L10,3 L0,6 Z" fill="#243447"></path>
              </marker>
            </defs>
          </svg>

          @foreach($nodos as $nodo)
            @php
              $left = ($nodo->pos_x ?? 0) + $offsetX;
              $top = ($nodo->pos_y ?? 0) + $offsetY;
            @endphp
            <div class="print-node" data-id="{{ $nodo->id }}" data-tipo="{{ $nodo->tipo_nodo }}" style="left: {{ $left }}px; top: {{ $top }}px;">
              <span class="print-port in" style="left:-7px; top:22px;"></span>
              @if($nodo->tipo_nodo === 'decision')
                @foreach($nodo->salientes->values() as $salienteIndex => $saliente)
                  <span class="print-port out" data-rel-id="{{ $saliente->id }}" style="right:-7px; top:{{ 22 + ($salienteIndex * 22) }}px;"></span>
                @endforeach
              @else
                <span class="print-port out" style="right:-7px; top:22px;"></span>
              @endif
              <div class="node-edit-badge">&#9998;</div>
              <div class="node-name">{{ $nodo->nombre }}</div>
              <div class="node-meta">{{ $nodo->tipo_nodo }} · orden {{ $nodo->orden }}</div>
              @if($nodo->responsableRol)
                <div class="node-meta">Responsable: {{ $nodo->responsableRol->nombre }}</div>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="title-block">
      <div class="title-block-grid">
        <div class="title-block-logo">
          <img src="{{ asset('images/inde-logo.png') }}" alt="INDE">
        </div>
        <div class="title-block-info">
          <div class="tb-row">
            <div class="tb-label">Documento</div>
            <div class="tb-value">Mapa del Proceso</div>
          </div>
          <div class="tb-row">
            <div class="tb-label">Proceso</div>
            <div class="tb-value">{{ $proceso->nombre }}</div>
          </div>
          <div class="tb-row">
            <div class="tb-label">Codigo</div>
            <div class="tb-value">{{ $proceso->codigo ?: 'N/D' }}</div>
          </div>
          <div class="tb-row">
            <div class="tb-label">Version</div>
            <div class="tb-value">{{ $proceso->version ?: 'N/D' }}</div>
          </div>
          <div class="tb-row">
            <div class="tb-label">Fecha</div>
            <div class="tb-value">{{ $generatedAt->format('Y-m-d H:i') }}</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  @foreach($matrixChunks as $chunkIndex => $chunk)
    <section class="print-sheet sheet-letter matrix-page">
      <div class="matrix-title">
        <div class="index">6.2.</div>
        <div class="name">Matriz Plan:</div>
      </div>

      <div class="matrix-subtitle">
        Proceso: {{ $proceso->nombre }} | Pagina {{ $chunkIndex + 1 }} de {{ $matrixChunks->count() }}
      </div>

      <div class="matrix-wrap">
        <table class="matrix">
          <thead>
            <tr>
              <th style="width:4%;">(1)<br>No.</th>
              <th style="width:12%;">(2)<br>Actividad<br>(Que)</th>
              <th style="width:11%;">(3)<br>Responsable<br>(Quien)</th>
              <th style="width:16%;">(4)<br>Actividad Especifica<br>(Como)</th>
              <th style="width:8%;">(5)<br>Frecuencia<br>(Cuando)</th>
              <th style="width:10%;">(6)<br>Contingencia</th>
              <th style="width:8%;">(7)<br>Parametros<br>(Indicador)</th>
              <th style="width:9%;">(8)<br>Productos o<br>Servicios</th>
              <th style="width:8%;">(9)<br>Variable de<br>Control</th>
              <th style="width:7%;">(10)<br>Registro</th>
              <th style="width:7%;">(11)<br>Documentos de Referencia</th>
            </tr>
          </thead>
          <tbody>
            <tr class="process-band">
              <td colspan="11">{{ $proceso->nombre }}</td>
            </tr>
            @foreach($chunk as $nodo)
              @php
                $itemsDocumento = $nodo->items->where('categoria', 'DOCUMENTO')->pluck('nombre')->implode(', ');
                $itemsFormulario = $nodo->items->where('categoria', 'FORMULARIO')->pluck('nombre')->implode(', ');
                $itemsOperacion = $nodo->items->where('categoria', 'OPERACION')->pluck('nombre')->implode(', ');
                $salidas = $nodo->salientes
                  ->map(fn($rel) => trim(($rel->condicion ?: 'Continua') . ': ' . ($rel->destino?->nombre ?: 'N/D')))
                  ->implode(' | ');
                $frecuencia = $nodo->sla_horas ? 'SLA ' . $nodo->sla_horas . ' h' : 'Cuando se requiera';
                $actividadEspecifica = $nodo->descripcion ?: 'Ejecutar la actividad segun el flujo definido del proceso.';
                $registro = $itemsFormulario ?: 'N.A.';
                $documentos = $itemsDocumento ?: ($docRefItems ?: 'N.A.');
              @endphp
              <tr>
                <td class="text-center">{{ $nodo->orden ?: $loop->iteration }}</td>
                <td>{{ $nodo->nombre }}</td>
                <td>{{ $nodo->responsableRol?->nombre ?: 'N.A.' }}</td>
                <td>{{ $actividadEspecifica }}</td>
                <td>{{ $frecuencia }}</td>
                <td>{{ $salidas ?: 'N.A.' }}</td>
                <td class="na">N.A.</td>
                <td>{{ $itemsOperacion ?: 'N.A.' }}</td>
                <td class="na">N.A.</td>
                <td>{{ $registro }}</td>
                <td>{{ $documentos }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>
  @endforeach
  <script>
    (function () {
      const procesoId = @json((string) $proceso->id);
      const svg = document.getElementById('printLinkLayer');
      const canvas = document.querySelector('.process-flow');
      if (!svg || !canvas) return;

      const links = @json($printLinks);

      const nodes = new Map();
      canvas.querySelectorAll('.print-node').forEach((nodeEl) => {
        nodes.set(String(nodeEl.dataset.id), nodeEl);
      });

      function portKey(portEl) {
        if (portEl.classList.contains('in')) return 'in';
        const relId = portEl.dataset.relId;
        if (relId) return `out:${relId}`;
        return 'out:base';
      }

      function storageKey(nodeId, pKey) {
        return `gp:pb:${procesoId}:node:${nodeId}:port:${pKey}`;
      }

      function getPortPos(nodeId, pKey) {
        try {
          const raw = localStorage.getItem(storageKey(nodeId, pKey));
          return raw ? JSON.parse(raw) : null;
        } catch (e) {
          return null;
        }
      }

      function setPortAbsolute(portEl, leftPx, topPx) {
        portEl.style.right = 'auto';
        portEl.style.bottom = 'auto';
        portEl.style.left = `${leftPx}px`;
        portEl.style.top = `${topPx}px`;
      }

      function applySavedPortPositionsAll() {
        nodes.forEach((nodeEl, nodeId) => {
          nodeEl.querySelectorAll('.print-port').forEach((portEl) => {
            const pos = getPortPos(nodeId, portKey(portEl));
            if (!pos) return;
            setPortAbsolute(portEl, pos.left, pos.top);
          });
        });
      }

      function portPointByEl(nodeEl, selector) {
        const portEl = nodeEl.querySelector(selector);
        const offL = portEl ? portEl.offsetLeft : 0;
        const offT = portEl ? portEl.offsetTop : 22;
        return {
          x: nodeEl.offsetLeft + offL + 7,
          y: nodeEl.offsetTop + offT + 7,
        };
      }

      function linkStorageKey(relId) {
        return `gp:pb:${procesoId}:rel:${relId}:bezier`;
      }

      function getBezier(relId) {
        if (!relId) return null;
        try {
          const raw = localStorage.getItem(linkStorageKey(relId));
          return raw ? JSON.parse(raw) : null;
        } catch (e) {
          return null;
        }
      }

      function defaultBezier(p1, p2) {
        const dx = Math.max(80, Math.abs(p2.x - p1.x) * 0.5);
        return {
          c1x: p1.x + dx,
          c1y: p1.y,
          c2x: p2.x - dx,
          c2y: p2.y,
        };
      }

      function drawLinks() {
        [...svg.querySelectorAll('path.link')].forEach((el) => el.remove());
        [...svg.querySelectorAll('text.linklabel')].forEach((el) => el.remove());

        links.forEach((link) => {
          const from = nodes.get(String(link.nodo_origen_id));
          const to = nodes.get(String(link.nodo_destino_id));
          if (!from || !to) return;

          let fromSelector = '.print-port.out';
          if (from.dataset.tipo === 'decision' && link.id) {
            fromSelector = `.print-port.out[data-rel-id="${link.id}"]`;
            if (!from.querySelector(fromSelector)) {
              fromSelector = '.print-port.out';
            }
          }

          const p1 = portPointByEl(from, fromSelector);
          const p2 = portPointByEl(to, '.print-port.in');
          const relId = link.id ? String(link.id) : null;
          const saved = relId ? getBezier(relId) : null;
          const base = defaultBezier(p1, p2);
          const c1x = saved?.c1x ?? base.c1x;
          const c1y = saved?.c1y ?? base.c1y;
          const c2x = saved?.c2x ?? base.c2x;
          const c2y = saved?.c2y ?? base.c2y;

          const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
          path.setAttribute('d', `M ${p1.x} ${p1.y} C ${c1x} ${c1y}, ${c2x} ${c2y}, ${p2.x} ${p2.y}`);
          path.setAttribute('fill', 'none');
          path.setAttribute('stroke', '#243447');
          path.setAttribute('stroke-width', '2');
          path.setAttribute('marker-end', 'url(#printArrow)');
          path.classList.add('link');
          svg.appendChild(path);

          if (link.condicion) {
            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.textContent = link.condicion;
            text.setAttribute('x', (p1.x + p2.x) / 2);
            text.setAttribute('y', (p1.y + p2.y) / 2 - 6);
            text.setAttribute('fill', 'rgba(15,23,42,.75)');
            text.setAttribute('font-size', '10');
            text.classList.add('linklabel');
            svg.appendChild(text);
          }
        });
      }

      applySavedPortPositionsAll();
      drawLinks();
    })();
  </script>
</body>
</html>
