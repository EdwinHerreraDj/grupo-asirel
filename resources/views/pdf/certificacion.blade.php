@php
    $colorPrimario = $empresa->color_primario ?? '#111827';
    $colorSecundario = $empresa->color_secundario ?? '#d1d5db';
    $mostrarLogo = $empresa?->mostrar_logo_pdf ?? true;
    $piePdf = $empresa->pie_pdf ?? null;
    $numeroFormateado = 'CERT-' . str_replace(['/', '\\'], '-', $numero_certificacion);
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Informe de certificación {{ $numero_certificacion }}</title>
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
        .doc-numero { font-size: 18px; font-weight: bold; color: {{ $colorPrimario }}; letter-spacing: 0.5px; margin: 0; }
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

        /* ===== TABLA LÍNEAS ===== */
        table.lineas { width: 100%; border-collapse: collapse; margin-top: 0; }
        table.lineas thead tr { background: #f3f4f6; }
        table.lineas thead th {
            padding: 6px 6px;
            font-size: 9px;
            text-transform: uppercase;
            text-align: left;
            letter-spacing: 0.4px;
            color: {{ $colorPrimario }};
            border-bottom: 1px solid {{ $colorSecundario }};
        }
        table.lineas thead th.right { text-align: right; }
        table.lineas tbody td {
            padding: 6px 6px;
            font-size: 9.5px;
            border-bottom: 1px solid {{ $colorSecundario }};
            vertical-align: top;
            color: #222;
        }
        table.lineas tbody td.right { text-align: right; }
        table.lineas tbody tr:nth-child(even) td { background: #fafafa; }

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

        /* ===== DESGLOSE FISCAL ===== */
        .desglose-wrap { margin-top: 22px; }
        .desglose-titulo {
            background: {{ $colorPrimario }};
            color: #fff;
            font-size: 10.5px;
            font-weight: bold;
            padding: 6px 10px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            width: 55%;
            margin-left: auto;
        }
        table.desglose {
            margin-left: auto;
            width: 55%;
            border-collapse: collapse;
            border: 1px solid {{ $colorSecundario }};
            border-top: none;
        }
        table.desglose td {
            padding: 6px 10px;
            font-size: 10.5px;
            border-bottom: 1px solid {{ $colorSecundario }};
        }
        table.desglose tr:last-child td { border-bottom: none; }
        table.desglose td.label { color: #555; }
        table.desglose td.value { text-align: right; font-weight: bold; color: #111; width: 40%; }
        table.desglose td.subdetail {
            font-size: 9.5px;
            color: #777;
            padding-left: 22px;
        }
        table.desglose td.subdetail-value {
            font-size: 9.5px;
            color: #555;
            font-weight: normal;
            text-align: right;
        }
        table.desglose tr.retencion td.value { color: #b91c1c; }
        table.desglose tr.total-final td {
            background: {{ $colorPrimario }};
            color: #fff;
            font-size: 12.5px;
            padding: 9px 10px;
            letter-spacing: 0.3px;
        }
        table.desglose tr.total-final td.label { text-transform: uppercase; }
        table.desglose tr.total-final td.value { color: #fff; }

        /* ===== NOTA ===== */
        .nota {
            margin-top: 22px;
            padding: 9px 12px;
            background: #fffbeb;
            border-left: 3px solid #fbbf24;
            font-size: 9.5px;
            color: #78350f;
            line-height: 1.5;
        }
        .nota .label { font-weight: bold; text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px; }

        /* ===== PIE ===== */
        .pie {
            margin-top: 20px;
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
                <div class="doc-label">Informe certificación</div>
                <div class="doc-numero">{{ $numero_certificacion }}</div>
                <div class="doc-meta">
                    <strong>Fecha:</strong> {{ $fecha }}<br>
                </div>
                <div class="badge-tipo">No es factura</div>
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
                        Nº certificación: <strong>{{ $numero_certificacion }}</strong>
                        @if (!empty($obra->descripcion))<br>{{ Str::limit($obra->descripcion, 140) }}@endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ===== CAPÍTULOS ===== --}}
    @forelse ($capitulos as $capitulo)
        <div class="capitulo-header">
            {{ $capitulo['oficio'] }}
            <span class="cap-importe">{{ number_format($capitulo['total'], 2, ',', '.') }} €</span>
        </div>

        <table class="lineas">
            <thead>
                <tr>
                    <th style="width:55%">Descripción</th>
                    <th style="width:8%">Ud.</th>
                    <th class="right" style="width:12%">Cantidad</th>
                    <th class="right" style="width:12%">Precio</th>
                    <th class="right" style="width:13%">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($capitulo['lineas'] as $linea)
                    <tr>
                        <td>{{ $linea['descripcion'] }}</td>
                        <td>{{ $linea['unidad'] ?? '—' }}</td>
                        <td class="right">{{ number_format($linea['cantidad'], 2, ',', '.') }}</td>
                        <td class="right">{{ number_format($linea['precio'], 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format($linea['total'], 2, ',', '.') }} €</td>
                    </tr>
                @endforeach

                <tr class="subtotal-row">
                    <td colspan="4" class="label">Subtotal capítulo</td>
                    <td>{{ number_format($capitulo['total'], 2, ',', '.') }} €</td>
                </tr>
            </tbody>
        </table>
    @empty
        <div style="padding:30px;text-align:center;color:#888;font-style:italic;border:1px dashed {{ $colorSecundario }};margin-top:10px;">
            Esta certificación no contiene capítulos seleccionados.
        </div>
    @endforelse

    {{-- ===== DESGLOSE FISCAL ===== --}}
    @if (count($capitulos) > 0)
        <div class="desglose-wrap">
            <div class="desglose-titulo">Desglose económico</div>
            <table class="desglose">
                {{-- Base imponible --}}
                <tr>
                    <td class="label">Base imponible</td>
                    <td class="value">{{ number_format($totales['base'], 2, ',', '.') }} €</td>
                </tr>

                {{-- IVA: total + desglose por tipo si hay más de uno --}}
                <tr>
                    <td class="label">
                        IVA
                        @if (count($totales['iva_grupos']) === 1)
                            ({{ number_format($totales['iva_grupos'][0]['porcentaje'], 2, ',', '.') }}%)
                        @endif
                    </td>
                    <td class="value">{{ number_format($totales['iva_total'], 2, ',', '.') }} €</td>
                </tr>
                @if (count($totales['iva_grupos']) > 1)
                    @foreach ($totales['iva_grupos'] as $grupo)
                        <tr>
                            <td class="subdetail">
                                · {{ number_format($grupo['porcentaje'], 2, ',', '.') }}% sobre
                                {{ number_format($grupo['base'], 2, ',', '.') }} €
                            </td>
                            <td class="subdetail-value">
                                {{ number_format($grupo['importe'], 2, ',', '.') }} €
                            </td>
                        </tr>
                    @endforeach
                @endif

                {{-- Retención: solo si hay alguna > 0 --}}
                @if (count($totales['retencion_grupos']) > 0)
                    <tr class="retencion">
                        <td class="label">
                            Retención
                            @if (count($totales['retencion_grupos']) === 1)
                                ({{ number_format($totales['retencion_grupos'][0]['porcentaje'], 2, ',', '.') }}%)
                            @endif
                        </td>
                        <td class="value">−{{ number_format($totales['retencion_total'], 2, ',', '.') }} €</td>
                    </tr>
                    @if (count($totales['retencion_grupos']) > 1)
                        @foreach ($totales['retencion_grupos'] as $grupo)
                            <tr>
                                <td class="subdetail">
                                    · {{ number_format($grupo['porcentaje'], 2, ',', '.') }}% sobre
                                    {{ number_format($grupo['base'], 2, ',', '.') }} €
                                </td>
                                <td class="subdetail-value">
                                    −{{ number_format($grupo['importe'], 2, ',', '.') }} €
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endif

                {{-- Total final --}}
                <tr class="total-final">
                    <td class="label">Total certificado</td>
                    <td class="value">{{ number_format($totales['total'], 2, ',', '.') }} €</td>
                </tr>
            </table>
        </div>
    @endif

    {{-- ===== NOTA ===== --}}
    <div class="nota">
        <span class="label">Aviso:</span>
        Este documento es un <strong>informe de certificación</strong> y no constituye una factura ni tiene
        validez fiscal. Los importes reflejan los trabajos certificados a la fecha indicada y el
        desglose corresponde a los capítulos seleccionados. La factura correspondiente, cuando se emita,
        se entregará por separado.
    </div>

    {{-- ===== PIE ===== --}}
    <div class="pie">
        © {{ date('Y') }} {{ $empresa->nombre ?? 'Empresa' }} · Informe generado automáticamente
        @if ($piePdf)
            <div class="pie-personalizado">{{ $piePdf }}</div>
        @endif
    </div>

</body>

</html>
