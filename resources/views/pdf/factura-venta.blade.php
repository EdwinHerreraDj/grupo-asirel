@php
    $colorPrimario = $empresa->color_primario ?? '#111827';
    $colorSecundario = $empresa->color_secundario ?? '#d1d5db';
    $mostrarLogo = $empresa?->mostrar_logo_pdf ?? true;
    $piePdf = $empresa->pie_pdf ?? null;

    // Logo embebido como base64: evita problemas de symlink/rutas en DomPDF.
    $logoSrc = null;
    if ($mostrarLogo && !empty($empresa?->logo)) {
        $logoPath = storage_path('app/public/' . $empresa->logo);
        if (is_file($logoPath)) {
            $logoExt = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION)) ?: 'png';
            $logoSrc = 'data:image/' . $logoExt . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    }
    $numeroFormateado = $factura->serie . '-' . ($factura->numero_factura ?? 'BORRADOR');
    $estados = [
        'borrador' => 'BORRADOR',
        'emitida'  => 'EMITIDA',
        'enviada'  => 'ENVIADA',
        'pagada'   => 'PAGADA',
        'anulada'  => 'ANULADA',
    ];
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Factura {{ $numeroFormateado }}</title>
    <style>
        @page { margin: 25mm 15mm 20mm 15mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #111; margin: 0; }

        .watermark {
            position: fixed; top: 40%; left: 20%; width: 60%; text-align: center;
            font-size: 72px; font-weight: bold; color: rgba(220, 38, 38, 0.15);
            transform: rotate(-20deg); z-index: -1;
        }
        .watermark-copia {
            position: fixed; top: 42%; left: 15%; width: 70%; text-align: center;
            font-size: 68px; font-weight: bold; color: rgba(100, 116, 139, 0.12);
            transform: rotate(-20deg); z-index: -1;
        }
        .copia-badge {
            display: inline-block; margin-top: 4px; padding: 3px 10px; border-radius: 12px;
            font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;
            background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;
        }
        .copia-aviso {
            margin-top: 4px; font-size: 8.5px; color: #64748b; font-style: italic;
        }

        .header { width: 100%; border-bottom: 2px solid {{ $colorPrimario }}; padding-bottom: 10px; margin-bottom: 18px; }
        .header td { vertical-align: top; padding: 0; }
        .logo-cell { width: 140px; }
        .logo-cell img { max-width: 130px; max-height: 60px; }
        .empresa-info { font-size: 10px; color: #333; line-height: 1.4; }
        .empresa-info .nombre { font-size: 13px; font-weight: bold; color: {{ $colorPrimario }}; margin-bottom: 2px; }
        .factura-cell { text-align: right; width: 250px; }
        .factura-numero { font-size: 22px; font-weight: bold; color: {{ $colorPrimario }}; letter-spacing: 0.5px; margin: 0; }
        .factura-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1.5px; color: #777; margin-bottom: 4px; }
        .factura-meta { font-size: 10px; color: #555; margin-top: 6px; line-height: 1.5; }
        .factura-meta strong { color: #222; }

        .estado-badge {
            display: inline-block; padding: 3px 10px; border-radius: 12px;
            font-size: 9px; font-weight: bold; text-transform: uppercase;
            letter-spacing: 0.5px; border: 1px solid; margin-top: 4px;
        }
        .estado-borrador { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
        .estado-emitida, .estado-enviada { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
        .estado-pagada { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
        .estado-anulada { background: #fef2f2; color: #991b1b; border-color: #fecaca; }

        .partes { width: 100%; margin-bottom: 16px; border-collapse: separate; border-spacing: 10px 0; }
        .partes td { padding: 0; vertical-align: top; }
        .parte-box {
            border: 1px solid {{ $colorSecundario }};
            border-left: 3px solid {{ $colorPrimario }};
            padding: 8px 12px; background: #fafafa;
        }
        .parte-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 4px; }
        .parte-nombre { font-size: 12px; font-weight: bold; color: #111; }
        .parte-detalle { font-size: 9.5px; color: #444; margin-top: 2px; line-height: 1.4; }

        table.lineas { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 14px; }
        table.lineas thead tr { background-color: {{ $colorPrimario }}; }
        table.lineas thead th {
            color: #ffffff; font-weight: bold; text-transform: uppercase; font-size: 9px;
            padding: 8px 6px; text-align: left; letter-spacing: 0.5px; border: none;
        }
        table.lineas thead th.num { text-align: right; }
        table.lineas tbody td {
            padding: 7px 6px; font-size: 9.5px;
            border-bottom: 1px solid {{ $colorSecundario }};
            color: #222; vertical-align: top;
        }
        table.lineas tbody td.num { text-align: right; }
        table.lineas tbody tr:nth-child(even) td { background-color: #fafafa; }

        .totales-wrap { margin-top: 16px; }
        table.totales { margin-left: auto; width: 45%; border-collapse: collapse; }
        table.totales td { padding: 7px 10px; font-size: 10px; }
        table.totales td.label { text-align: right; color: #555; }
        table.totales td.value { text-align: right; font-weight: bold; color: #111; width: 40%; }
        table.totales tr.total-final td {
            background: {{ $colorPrimario }}; color: #fff; font-size: 12px; padding: 10px;
        }
        table.totales tr.total-final td.value { color: #fff; }

        .observaciones {
            margin-top: 20px; padding: 10px 12px; background: #fafafa;
            border-left: 3px solid {{ $colorSecundario }};
            font-size: 9.5px; color: #555;
        }
        .observaciones .label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 3px; }

        .anulacion-box {
            margin-top: 18px; padding: 10px 14px; background: #fef2f2;
            border-left: 4px solid #dc2626; color: #7f1d1d; font-size: 10px;
        }

        .pie {
            margin-top: 24px; padding-top: 10px;
            border-top: 1px solid {{ $colorSecundario }};
            font-size: 9px; color: #666; text-align: center; line-height: 1.5;
        }
        .pie-personalizado { margin-top: 6px; font-style: italic; color: #555; }
    </style>
</head>

<body>

    @if ($factura->estado === 'anulada')
        <div class="watermark">ANULADA</div>
    @elseif ($esCopia ?? false)
        <div class="watermark-copia">COPIA</div>
    @endif

    {{-- HEADER --}}
    <table class="header">
        <tr>
            @if ($logoSrc)
                <td class="logo-cell">
                    <img src="{{ $logoSrc }}" alt="Logo {{ $empresa->nombre }}">
                </td>
            @endif
            <td class="empresa-info">
                <div class="nombre">{{ $empresa->nombre ?? 'Empresa' }}</div>
                @if (!empty($empresa->direccion)){{ $empresa->direccion }}<br>@endif
                {{ $empresa->codigo_postal ?? '' }} {{ $empresa->ciudad ?? '' }}
                {{ !empty($empresa->provincia) ? '(' . $empresa->provincia . ')' : '' }}
                @if (!empty($empresa->cif))<br>CIF: {{ $empresa->cif }}@endif
                @if (!empty($empresa->telefono)) · Tel: {{ $empresa->telefono }}@endif
                @if (!empty($empresa->email)) · {{ $empresa->email }}@endif
            </td>
            <td class="factura-cell">
                <div class="factura-label">Factura</div>
                <div class="factura-numero">{{ $numeroFormateado }}</div>
                <div class="factura-meta">
                    <strong>Fecha emisión:</strong> {{ $factura->fecha_emision?->format('d/m/Y') ?? '—' }}<br>
                    @if ($factura->vencimiento)<strong>Vencimiento:</strong> {{ $factura->vencimiento->format('d/m/Y') }}<br>@endif
                    @if ($factura->codigo_certificacion)<strong>Cert.:</strong> {{ $factura->codigo_certificacion }}<br>@endif
                </div>
                <div class="estado-badge estado-{{ $factura->estado }}">
                    {{ $estados[$factura->estado] ?? $factura->estado }}
                </div>
                @if ($esCopia ?? false)
                    <div><span class="copia-badge">Copia · Reimpresión</span></div>
                    <div class="copia-aviso">
                        Copia generada el {{ ($fechaCopia ?? now())->format('d/m/Y H:i') }}.<br>
                        No sustituye al documento original emitido.
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- CLIENTE + OBRA --}}
    <table class="partes">
        <tr>
            <td style="width:50%">
                <div class="parte-box">
                    <div class="parte-label">Facturar a</div>
                    <div class="parte-nombre">{{ $factura->cliente->nombre ?? '—' }}</div>
                    <div class="parte-detalle">
                        @if (!empty($factura->cliente?->cif))CIF: {{ $factura->cliente->cif }}<br>@endif
                        @if (!empty($factura->cliente?->direccion)){{ $factura->cliente->direccion }}<br>@endif
                        {{ $factura->cliente?->codigo_postal ?? '' }} {{ $factura->cliente?->ciudad ?? '' }}
                    </div>
                </div>
            </td>
            <td style="width:50%">
                <div class="parte-box">
                    <div class="parte-label">Obra</div>
                    <div class="parte-nombre">{{ $factura->obra->nombre ?? '—' }}</div>
                    @if (!empty($factura->obra?->descripcion))
                        <div class="parte-detalle">{{ Str::limit($factura->obra->descripcion, 120) }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- LÍNEAS --}}
    <table class="lineas">
        <thead>
            <tr>
                <th>Concepto</th>
                <th style="width:60px">Ud</th>
                <th class="num" style="width:80px">Cantidad</th>
                <th class="num" style="width:90px">P.U.</th>
                <th class="num" style="width:100px">Importe</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($factura->detalles as $linea)
                <tr>
                    <td>{{ $linea->concepto }}</td>
                    <td>{{ $linea->unidad ?: '—' }}</td>
                    <td class="num">{{ number_format($linea->cantidad, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($linea->precio_unitario, 2, ',', '.') }} €</td>
                    <td class="num">{{ number_format($linea->importe_linea, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:20px;color:#999;font-style:italic;">Sin líneas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TOTALES --}}
    <div class="totales-wrap">
        <table class="totales">
            <tr>
                <td class="label">Base imponible</td>
                <td class="value">{{ number_format($factura->base_imponible, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td class="label">IVA ({{ number_format($factura->iva_porcentaje, 2, ',', '.') }}%)</td>
                <td class="value">{{ number_format($factura->iva_importe, 2, ',', '.') }} €</td>
            </tr>
            @if ($factura->retencion_porcentaje > 0)
                <tr>
                    <td class="label">Retención ({{ number_format($factura->retencion_porcentaje, 2, ',', '.') }}%)</td>
                    <td class="value" style="color:#b91c1c">-{{ number_format($factura->retencion_importe, 2, ',', '.') }} €</td>
                </tr>
            @endif
            <tr class="total-final">
                <td class="label">TOTAL</td>
                <td class="value">{{ number_format($factura->total, 2, ',', '.') }} €</td>
            </tr>
        </table>
    </div>

    @if (!empty($factura->observaciones))
        <div class="observaciones">
            <div class="label">Observaciones</div>
            {{ $factura->observaciones }}
        </div>
    @endif

    @if ($factura->estado === 'anulada')
        <div class="anulacion-box">
            <strong>FACTURA ANULADA.</strong>
            @if ($factura->motivo_anulacion) Motivo: {{ $factura->motivo_anulacion }}@endif
        </div>
    @endif

    <div class="pie">
        © {{ date('Y') }} {{ $empresa->nombre ?? 'Empresa' }} ·
        {{ ($esCopia ?? false) ? 'Copia / reimpresión — representación posterior del documento original' : 'Factura generada automáticamente' }}
        @if ($piePdf)
            <div class="pie-personalizado">{{ $piePdf }}</div>
        @endif
    </div>

</body>

</html>
