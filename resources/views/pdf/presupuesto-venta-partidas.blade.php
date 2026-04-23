@php
    $colorPrimario = $empresa->color_primario ?? '#111827';
    $colorSecundario = $empresa->color_secundario ?? '#d1d5db';
    $mostrarLogo = $empresa?->mostrar_logo_pdf ?? true;
    $piePdf = $empresa->pie_pdf ?? null;
    $numeroExpediente = 'PV-' . str_pad($obra->id, 4, '0', STR_PAD_LEFT);
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Presupuesto {{ $numeroExpediente }}</title>
    <style>
        @page { margin: 25mm 15mm 20mm 15mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #111; margin: 0; }

        /* ===== HEADER ===== */
        .header {
            width: 100%;
            border-bottom: 2px solid {{ $colorPrimario }};
            padding-bottom: 10px;
            margin-bottom: 18px;
        }
        .header td { vertical-align: top; padding: 0; }
        .logo-cell { width: 140px; }
        .logo-cell img { max-width: 130px; max-height: 60px; }

        .empresa-info { font-size: 10px; color: #333; line-height: 1.4; }
        .empresa-info .nombre { font-size: 13px; font-weight: bold; color: {{ $colorPrimario }}; margin-bottom: 2px; }

        .doc-cell { text-align: right; width: 240px; }
        .doc-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1.5px; color: #777; margin-bottom: 4px; }
        .doc-numero { font-size: 20px; font-weight: bold; color: {{ $colorPrimario }}; letter-spacing: 0.5px; margin: 0; }
        .doc-meta { font-size: 10px; color: #555; margin-top: 6px; line-height: 1.5; }
        .doc-meta strong { color: #222; }

        .badge-tipo {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #f0f9ff;
            color: #0369a1;
            border: 1px solid #bae6fd;
            margin-top: 4px;
        }

        /* ===== PARTES ===== */
        .partes { width: 100%; margin-bottom: 16px; border-collapse: separate; border-spacing: 10px 0; }
        .partes td { padding: 0; vertical-align: top; }
        .parte-box {
            border: 1px solid {{ $colorSecundario }};
            border-left: 3px solid {{ $colorPrimario }};
            padding: 8px 12px;
            background: #fafafa;
        }
        .parte-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 4px; }
        .parte-nombre { font-size: 12px; font-weight: bold; color: #111; }
        .parte-detalle { font-size: 9.5px; color: #444; margin-top: 2px; line-height: 1.4; }

        /* ===== CAPÍTULOS ===== */
        .capitulo-header {
            background: {{ $colorPrimario }};
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 7px 10px;
            margin-top: 14px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .capitulo-header .cap-importe {
            float: right;
            font-weight: bold;
            letter-spacing: 0;
        }

        /* ===== PARTIDAS ===== */
        table.partidas { width: 100%; border-collapse: collapse; margin-top: 0; }
        table.partidas thead tr { background: #f3f4f6; }
        table.partidas thead th {
            padding: 6px 6px;
            font-size: 9px;
            text-transform: uppercase;
            text-align: left;
            letter-spacing: 0.4px;
            color: {{ $colorPrimario }};
            border-bottom: 1px solid {{ $colorSecundario }};
        }
        table.partidas thead th.right { text-align: right; }

        table.partidas tbody td {
            padding: 6px 6px;
            font-size: 9.5px;
            border-bottom: 1px solid {{ $colorSecundario }};
            vertical-align: top;
            color: #222;
        }
        table.partidas tbody td.right { text-align: right; }
        table.partidas tbody tr:nth-child(even) td { background: #fafafa; }

        .codigo-cell { font-family: 'Courier New', monospace; font-size: 9px; color: #555; }

        /* SUBTOTAL CAPÍTULO */
        .subtotal-row td {
            background: #f3f4f6;
            font-weight: bold;
            font-size: 10.5px;
            border-top: 1px solid {{ $colorPrimario }};
            border-bottom: none;
            padding: 7px 8px;
            text-align: right;
            color: {{ $colorPrimario }};
        }
        .subtotal-row td.label {
            text-align: left;
            text-transform: uppercase;
            font-size: 9.5px;
            letter-spacing: 0.4px;
        }

        /* ===== RESUMEN ===== */
        .resumen-wrap { margin-top: 18px; }
        table.resumen {
            margin-left: auto;
            width: 50%;
            border-collapse: collapse;
        }
        table.resumen td { padding: 7px 10px; font-size: 10.5px; }
        table.resumen tr.capitulo-line td {
            border-bottom: 1px dashed {{ $colorSecundario }};
            color: #444;
        }
        table.resumen tr.capitulo-line td.value { text-align: right; color: #111; font-weight: bold; }
        table.resumen tr.total-final td {
            background: {{ $colorPrimario }};
            color: #fff;
            font-size: 13px;
            padding: 10px;
            letter-spacing: 0.3px;
        }
        table.resumen tr.total-final td.label { text-transform: uppercase; }
        table.resumen tr.total-final td.value { text-align: right; font-weight: bold; }

        /* ===== CAMPOS MANUALES ===== */
        .condiciones {
            margin-top: 22px;
            padding: 10px 12px;
            background: #fafafa;
            border-left: 3px solid {{ $colorSecundario }};
            font-size: 10px;
            color: #333;
        }
        .condiciones .label-section {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 4px;
        }
        .condiciones p { margin: 5px 0; }

        /* ===== FIRMAS ===== */
        table.firmas { margin-top: 30px; width: 100%; border-collapse: collapse; }
        table.firmas td {
            width: 50%;
            padding-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #444;
            font-weight: bold;
        }
        .firma-linea {
            border-top: 1px solid #888;
            margin: 0 20px 6px 20px;
        }

        /* ===== PIE ===== */
        .pie {
            margin-top: 22px;
            padding-top: 8px;
            border-top: 1px solid {{ $colorSecundario }};
            font-size: 9px;
            color: #666;
            text-align: center;
            line-height: 1.5;
        }
        .pie-personalizado {
            margin-top: 4px;
            font-style: italic;
            color: #555;
        }

        .page-break { page-break-after: always; }
    </style>
</head>

<body>

    {{-- ===== CABECERA ===== --}}
    <table class="header">
        <tr>
            @if ($mostrarLogo)
                <td class="logo-cell">
                    @if (!empty($empresa?->logo))
                        <img src="{{ public_path('storage/' . $empresa->logo) }}" alt="Logo {{ $empresa->nombre }}">
                    @endif
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
            <td class="doc-cell">
                <div class="doc-label">Presupuesto</div>
                <div class="doc-numero">{{ $numeroExpediente }}</div>
                <div class="doc-meta">
                    <strong>Fecha:</strong> {{ $fecha }}<br>
                    @if (!empty($obra->fecha_inicio))
                        <strong>Inicio obra:</strong> {{ \Carbon\Carbon::parse($obra->fecha_inicio)->format('d/m/Y') }}<br>
                    @endif
                    @if (!empty($obra->fecha_fin))
                        <strong>Fin previsto:</strong> {{ \Carbon\Carbon::parse($obra->fecha_fin)->format('d/m/Y') }}<br>
                    @endif
                </div>
                <div class="badge-tipo">Mediciones y precios</div>
            </td>
        </tr>
    </table>

    {{-- ===== CLIENTE + OBRA ===== --}}
    <table class="partes">
        <tr>
            <td style="width:50%">
                <div class="parte-box">
                    <div class="parte-label">Cliente</div>
                    <div class="parte-nombre">{{ $cliente->nombre ?? '—' }}</div>
                    <div class="parte-detalle">
                        @if (!empty($cliente?->cif))CIF: {{ $cliente->cif }}<br>@endif
                        @if (!empty($cliente?->direccion)){{ $cliente->direccion }}<br>@endif
                        @if (!empty($cliente?->telefono))Tel: {{ $cliente->telefono }}@endif
                        @if (!empty($cliente?->email)) · {{ $cliente->email }}@endif
                    </div>
                </div>
            </td>
            <td style="width:50%">
                <div class="parte-box">
                    <div class="parte-label">Obra</div>
                    <div class="parte-nombre">{{ $obra->nombre ?? '—' }}</div>
                    <div class="parte-detalle">
                        Expediente: {{ $numeroExpediente }}
                        @if (!empty($obra->descripcion))<br>{{ Str::limit($obra->descripcion, 140) }}@endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ===== CAPÍTULOS Y PARTIDAS ===== --}}
    @forelse ($capitulos as $capitulo)
        <div class="capitulo-header">
            {{ $capitulo['nombre'] }}
            <span class="cap-importe">{{ number_format($capitulo['importe_total'], 2, ',', '.') }} €</span>
        </div>

        <table class="partidas">
            <thead>
                <tr>
                    <th style="width:10%">Código</th>
                    <th style="width:40%">Descripción</th>
                    <th style="width:7%">Ud.</th>
                    <th class="right" style="width:12%">Medición</th>
                    <th class="right" style="width:13%">Precio unit.</th>
                    <th class="right" style="width:13%">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($capitulo['partidas'] as $partida)
                    <tr>
                        <td class="codigo-cell">{{ $partida['codigo'] ?? '—' }}</td>
                        <td>{{ $partida['descripcion'] }}</td>
                        <td>{{ $partida['unidad'] ?? '—' }}</td>
                        <td class="right">{{ number_format($partida['medicion'], 4, ',', '.') }}</td>
                        <td class="right">{{ number_format($partida['precio_unitario'], 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format($partida['importe'], 2, ',', '.') }} €</td>
                    </tr>
                @endforeach

                <tr class="subtotal-row">
                    <td colspan="5" class="label">Total capítulo</td>
                    <td>{{ number_format($capitulo['importe_total'], 2, ',', '.') }} €</td>
                </tr>
            </tbody>
        </table>
    @empty
        <div style="padding:30px;text-align:center;color:#888;font-style:italic;border:1px dashed {{ $colorSecundario }};margin-top:10px;">
            Este presupuesto no contiene partidas.
        </div>
    @endforelse

    {{-- ===== RESUMEN ===== --}}
    @if (count($capitulos) > 0)
        <div class="resumen-wrap">
            <table class="resumen">
                @foreach ($capitulos as $capitulo)
                    <tr class="capitulo-line">
                        <td>{{ $capitulo['nombre'] }}</td>
                        <td class="value">{{ number_format($capitulo['importe_total'], 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
                <tr class="total-final">
                    <td class="label">TOTAL PRESUPUESTO</td>
                    <td class="value">{{ number_format($total, 2, ',', '.') }} €</td>
                </tr>
            </table>
        </div>
    @endif

    {{-- ===== CONDICIONES ===== --}}
    <div class="condiciones">
        <div class="label-section">Condiciones</div>
        <p><strong>Fecha inicio trabajos:</strong> _______________________________</p>
        <p><strong>Fecha fin trabajos:</strong> _______________________________</p>
        <p><strong>Forma de pago:</strong> _______________________________</p>
        <p><strong>Retención:</strong> _______________________________</p>
    </div>

    {{-- ===== FIRMAS ===== --}}
    <table class="firmas">
        <tr>
            <td>
                <div class="firma-linea"></div>
                Firma {{ $empresa->nombre ?? 'Proveedor' }}
            </td>
            <td>
                <div class="firma-linea"></div>
                Firma cliente
            </td>
        </tr>
    </table>

    {{-- ===== PIE ===== --}}
    <div class="pie">
        © {{ date('Y') }} {{ $empresa->nombre ?? 'Empresa' }} · Presupuesto generado automáticamente
        @if ($piePdf)
            <div class="pie-personalizado">{{ $piePdf }}</div>
        @endif
    </div>

</body>

</html>
