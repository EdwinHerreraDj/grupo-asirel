@php
    $colorPrimario = $empresa->color_primario ?? '#111827';
    $colorSecundario = $empresa->color_secundario ?? '#d1d5db';
    $mostrarLogo = $empresa?->mostrar_logo_pdf ?? true;
    $piePdf = $empresa->pie_pdf ?? null;

    $periodoTexto = '';
    if ($fechaInicio && $fechaFin) {
        $periodoTexto = \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') . ' — ' . \Carbon\Carbon::parse($fechaFin)->format('d/m/Y');
    } elseif ($fechaInicio) {
        $periodoTexto = 'Desde ' . \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y');
    } elseif ($fechaFin) {
        $periodoTexto = 'Hasta ' . \Carbon\Carbon::parse($fechaFin)->format('d/m/Y');
    } else {
        $periodoTexto = 'Histórico completo';
    }
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Liquidación de IVA</title>
    <style>
        @page { margin: 18mm 12mm 15mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; margin: 0; }

        .header { width: 100%; border-bottom: 2px solid {{ $colorPrimario }}; padding-bottom: 10px; margin-bottom: 16px; }
        .header td { vertical-align: top; padding: 0; }
        .logo-cell { width: 130px; }
        .logo-cell img { max-width: 120px; max-height: 55px; }
        .empresa-info { font-size: 9.5px; color: #333; line-height: 1.4; }
        .empresa-info .nombre { font-size: 12.5px; font-weight: bold; color: {{ $colorPrimario }}; margin-bottom: 2px; }
        .doc-cell { text-align: right; width: 250px; }
        .doc-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1.5px; color: #777; margin-bottom: 4px; }
        .doc-titulo { font-size: 15px; font-weight: bold; color: {{ $colorPrimario }}; margin: 0; }
        .doc-meta { font-size: 9.5px; color: #555; margin-top: 6px; line-height: 1.5; }
        .doc-meta strong { color: #222; }

        h2.section {
            font-size: 11px;
            font-weight: bold;
            color: #fff;
            background: {{ $colorPrimario }};
            padding: 6px 10px;
            margin: 18px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.resumen-tipos { width: 100%; border-collapse: collapse; margin-top: 0; margin-bottom: 14px; }
        table.resumen-tipos thead th {
            background: #f3f4f6; color: {{ $colorPrimario }};
            font-size: 9px; font-weight: bold;
            text-transform: uppercase; letter-spacing: 0.4px;
            padding: 6px 8px; border-bottom: 1px solid {{ $colorSecundario }};
            text-align: left;
        }
        table.resumen-tipos thead th.right { text-align: right; }
        table.resumen-tipos tbody td {
            padding: 6px 8px;
            font-size: 10px;
            border-bottom: 1px solid {{ $colorSecundario }};
        }
        table.resumen-tipos tbody td.right { text-align: right; }
        table.resumen-tipos tfoot td {
            background: #f3f4f6; font-weight: bold; font-size: 10px;
            padding: 7px 8px; border-top: 1px solid {{ $colorPrimario }};
            color: {{ $colorPrimario }};
        }
        table.resumen-tipos tfoot td.right { text-align: right; }

        table.detalle { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 10px; }
        table.detalle thead th {
            background: #f3f4f6; color: {{ $colorPrimario }};
            font-size: 8.5px; font-weight: bold;
            text-transform: uppercase; letter-spacing: 0.3px;
            padding: 5px 6px; border-bottom: 1px solid {{ $colorSecundario }};
            text-align: left;
        }
        table.detalle thead th.right { text-align: right; }
        table.detalle tbody td {
            padding: 4px 6px; font-size: 9px;
            border-bottom: 1px solid #eee; vertical-align: top;
        }
        table.detalle tbody td.right { text-align: right; }
        table.detalle tbody tr:nth-child(even) td { background: #fafafa; }

        .empty-state {
            padding: 16px; text-align: center; color: #888;
            font-size: 9.5px; font-style: italic;
            border: 1px dashed {{ $colorSecundario }};
            margin: 8px 0;
        }

        /* LIQUIDACIÓN FINAL */
        .liquidacion-box {
            margin-top: 22px;
            border: 2px solid {{ $colorPrimario }};
            border-radius: 4px;
            overflow: hidden;
        }
        .liquidacion-box .titulo {
            background: {{ $colorPrimario }};
            color: #fff;
            padding: 8px 12px;
            font-size: 11.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.liquidacion { width: 100%; border-collapse: collapse; }
        table.liquidacion td {
            padding: 7px 12px; font-size: 10.5px;
            border-bottom: 1px solid {{ $colorSecundario }};
        }
        table.liquidacion tr:last-child td { border-bottom: none; }
        table.liquidacion td.label { color: #444; }
        table.liquidacion td.value { text-align: right; font-weight: bold; color: #111; width: 35%; }
        table.liquidacion tr.resultado-pagar td { background: #fef2f2; }
        table.liquidacion tr.resultado-compensar td { background: #ecfdf5; }
        table.liquidacion tr.resultado-pagar td.value { color: #b91c1c; font-size: 13px; }
        table.liquidacion tr.resultado-compensar td.value { color: #047857; font-size: 13px; }
        table.liquidacion tr.resultado-pagar td.label,
        table.liquidacion tr.resultado-compensar td.label {
            text-transform: uppercase; font-weight: bold; letter-spacing: 0.3px;
        }

        .nota {
            margin-top: 18px; padding: 8px 12px;
            background: #fffbeb; border-left: 3px solid #fbbf24;
            font-size: 9px; color: #78350f; line-height: 1.5;
        }

        .pie {
            margin-top: 18px; padding-top: 8px;
            border-top: 1px solid {{ $colorSecundario }};
            font-size: 8.5px; color: #666;
            text-align: center; line-height: 1.5;
        }
        .pie-personalizado { margin-top: 4px; font-style: italic; color: #555; }
    </style>
</head>

<body>

    {{-- CABECERA --}}
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
                @if (!empty($empresa->cif))CIF: {{ $empresa->cif }}@endif
            </td>
            <td class="doc-cell">
                <div class="doc-label">Informe</div>
                <div class="doc-titulo">Liquidación de IVA</div>
                <div class="doc-meta">
                    <strong>Periodo:</strong> {{ $periodoTexto }}<br>
                    <strong>Generado:</strong> {{ $generadoEn }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ===== IVA REPERCUTIDO (EMITIDAS) ===== --}}
    <h2 class="section">IVA repercutido · Facturas emitidas</h2>

    @if ($emitidasPorTipo->isEmpty())
        <div class="empty-state">No hay facturas emitidas en el periodo seleccionado.</div>
    @else
        <table class="resumen-tipos">
            <thead>
                <tr>
                    <th>Tipo IVA</th>
                    <th class="right">Nº facturas</th>
                    <th class="right">Base imponible</th>
                    <th class="right">Cuota IVA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($emitidasPorTipo as $grupo)
                    <tr>
                        <td>{{ number_format($grupo['porcentaje'], 2, ',', '.') }} %</td>
                        <td class="right">{{ $grupo['count'] }}</td>
                        <td class="right">{{ number_format($grupo['base'], 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format($grupo['cuota'], 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total repercutido</td>
                    <td class="right">{{ number_format($emitidas->sum('base_imponible'), 2, ',', '.') }} €</td>
                    <td class="right">{{ number_format($totalRepercutido, 2, ',', '.') }} €</td>
                </tr>
            </tfoot>
        </table>

        <table class="detalle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Nº factura</th>
                    <th>Cliente</th>
                    <th class="right">Base</th>
                    <th class="right">IVA %</th>
                    <th class="right">Cuota IVA</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($emitidas as $f)
                    <tr>
                        <td>{{ $f->fecha_emision?->format('d/m/Y') }}</td>
                        <td>{{ $f->serie }}-{{ $f->numero_factura ?? '—' }}</td>
                        <td>{{ $f->cliente->nombre ?? '—' }}</td>
                        <td class="right">{{ number_format($f->base_imponible, 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format($f->iva_porcentaje, 2, ',', '.') }} %</td>
                        <td class="right">{{ number_format($f->iva_importe, 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format($f->total, 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ===== IVA SOPORTADO (RECIBIDAS) ===== --}}
    <h2 class="section">IVA soportado · Facturas recibidas</h2>

    @if ($recibidasPorTipo->isEmpty())
        <div class="empty-state">No hay facturas recibidas en el periodo seleccionado.</div>
    @else
        <table class="resumen-tipos">
            <thead>
                <tr>
                    <th>Tipo IVA</th>
                    <th class="right">Nº facturas</th>
                    <th class="right">Base imponible</th>
                    <th class="right">Cuota IVA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recibidasPorTipo as $grupo)
                    <tr>
                        <td>{{ number_format($grupo['porcentaje'], 2, ',', '.') }} %</td>
                        <td class="right">{{ $grupo['count'] }}</td>
                        <td class="right">{{ number_format($grupo['base'], 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format($grupo['cuota'], 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total soportado</td>
                    <td class="right">{{ number_format($recibidas->sum('base_imponible'), 2, ',', '.') }} €</td>
                    <td class="right">{{ number_format($totalSoportado, 2, ',', '.') }} €</td>
                </tr>
            </tfoot>
        </table>

        <table class="detalle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Nº factura</th>
                    <th>Proveedor</th>
                    <th class="right">Base</th>
                    <th class="right">IVA %</th>
                    <th class="right">Cuota IVA</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recibidas as $f)
                    <tr>
                        <td>{{ $f->fecha_factura?->format('d/m/Y') }}</td>
                        <td>{{ $f->numero_factura ?? '—' }}</td>
                        <td>{{ $f->proveedor->nombre ?? '—' }}</td>
                        <td class="right">{{ number_format($f->base_imponible, 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format($f->iva_porcentaje, 2, ',', '.') }} %</td>
                        <td class="right">{{ number_format($f->iva_importe, 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format($f->total, 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ===== LIQUIDACIÓN ===== --}}
    <div class="liquidacion-box">
        <div class="titulo">Resumen de liquidación</div>
        <table class="liquidacion">
            <tr>
                <td class="label">IVA repercutido (facturas emitidas)</td>
                <td class="value">{{ number_format($totalRepercutido, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td class="label">IVA soportado (facturas recibidas)</td>
                <td class="value">− {{ number_format($totalSoportado, 2, ',', '.') }} €</td>
            </tr>
            @if ($liquidacion >= 0)
                <tr class="resultado-pagar">
                    <td class="label">Resultado · A ingresar</td>
                    <td class="value">{{ number_format($liquidacion, 2, ',', '.') }} €</td>
                </tr>
            @else
                <tr class="resultado-compensar">
                    <td class="label">Resultado · A compensar / devolver</td>
                    <td class="value">{{ number_format(abs($liquidacion), 2, ',', '.') }} €</td>
                </tr>
            @endif
        </table>
    </div>

    {{-- NOTA --}}
    <div class="nota">
        <strong>Aviso:</strong> Este informe es orientativo. Incluye únicamente facturas emitidas en estado
        <em>emitida, enviada o pagada</em>, y facturas recibidas excluyendo las devueltas. Contrasta los
        importes con tu asesoría antes de presentar el modelo fiscal correspondiente.
    </div>

    <div class="pie">
        © {{ date('Y') }} {{ $empresa->nombre ?? 'Empresa' }} · Informe generado automáticamente
        @if ($piePdf)
            <div class="pie-personalizado">{{ $piePdf }}</div>
        @endif
    </div>

</body>

</html>
