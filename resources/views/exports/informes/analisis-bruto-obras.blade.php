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

    $estadoTexto = $estado && $estado !== 'todas' ? ucfirst($estado) : 'Todos los estados';

    $formatNum = fn($n, $dec = 2) => number_format($n ?? 0, $dec, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Análisis bruto de obras</title>
    <style>
        @page { margin: 15mm 10mm; size: A4 landscape; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #111; margin: 0; }

        .header { width: 100%; border-bottom: 2px solid {{ $colorPrimario }}; padding-bottom: 8px; margin-bottom: 12px; }
        .header td { vertical-align: top; padding: 0; }
        .logo-cell { width: 120px; }
        .logo-cell img { max-width: 110px; max-height: 50px; }
        .empresa-info { font-size: 9px; color: #333; line-height: 1.4; }
        .empresa-info .nombre { font-size: 12px; font-weight: bold; color: {{ $colorPrimario }}; margin-bottom: 2px; }
        .doc-cell { text-align: right; width: 290px; }
        .doc-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1.5px; color: #777; margin-bottom: 4px; }
        .doc-titulo { font-size: 14px; font-weight: bold; color: {{ $colorPrimario }}; margin: 0; }
        .doc-meta { font-size: 9px; color: #555; margin-top: 5px; line-height: 1.5; }
        .doc-meta strong { color: #222; }

        /* Resumen KPIs */
        .kpis { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 12px; }
        .kpi {
            border: 1px solid {{ $colorSecundario }};
            border-left: 3px solid {{ $colorPrimario }};
            padding: 6px 10px;
            background: #fafafa;
        }
        .kpi .label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; color: #888; }
        .kpi .value { font-size: 12px; font-weight: bold; color: #111; margin-top: 2px; }
        .kpi.positivo { border-left-color: #059669; }
        .kpi.positivo .value { color: #047857; }
        .kpi.negativo { border-left-color: #dc2626; }
        .kpi.negativo .value { color: #b91c1c; }

        table.obras { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.obras thead tr { background: {{ $colorPrimario }}; }
        table.obras thead th {
            color: #fff; font-weight: bold; text-transform: uppercase;
            font-size: 8px; padding: 6px 5px; text-align: left;
            letter-spacing: 0.3px; border: none;
        }
        table.obras thead th.right { text-align: right; }
        table.obras tbody td {
            padding: 5px; font-size: 9px;
            border-bottom: 1px solid {{ $colorSecundario }};
            vertical-align: top;
        }
        table.obras tbody td.right { text-align: right; }
        table.obras tbody tr:nth-child(even) td { background: #fafafa; }

        .estado-badge {
            display: inline-block; padding: 1.5px 6px;
            border-radius: 10px; font-size: 8px;
            font-weight: bold; text-transform: uppercase;
            letter-spacing: 0.3px; border: 1px solid;
        }
        .est-planificacion { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
        .est-ejecucion    { background: #fffbeb; color: #92400e; border-color: #fde68a; }
        .est-finalizada   { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }

        .margen-positivo { color: #047857; font-weight: bold; }
        .margen-negativo { color: #b91c1c; font-weight: bold; }

        table.obras tfoot td {
            background: #f3f4f6; color: {{ $colorPrimario }};
            font-size: 10px; font-weight: bold;
            padding: 7px 5px;
            border-top: 2px solid {{ $colorPrimario }};
            border-bottom: none;
        }
        table.obras tfoot td.right { text-align: right; }

        .empty-state {
            padding: 20px; text-align: center; color: #888;
            font-size: 10px; font-style: italic;
            border: 1px dashed {{ $colorSecundario }};
            margin-top: 10px;
        }

        .nota {
            margin-top: 14px; padding: 8px 10px;
            background: #fffbeb; border-left: 3px solid #fbbf24;
            font-size: 8.5px; color: #78350f; line-height: 1.4;
        }

        .pie {
            margin-top: 14px; padding-top: 6px;
            border-top: 1px solid {{ $colorSecundario }};
            font-size: 8.5px; color: #666;
            text-align: center; line-height: 1.5;
        }
        .pie-personalizado { margin-top: 3px; font-style: italic; color: #555; }
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
                <div class="doc-titulo">Análisis bruto de obras</div>
                <div class="doc-meta">
                    <strong>Periodo:</strong> {{ $periodoTexto }}<br>
                    <strong>Estado:</strong> {{ $estadoTexto }} ·
                    <strong>Obras:</strong> {{ $filas->count() }}<br>
                    <strong>Generado:</strong> {{ $generadoEn }}
                </div>
            </td>
        </tr>
    </table>

    {{-- KPIs RESUMEN --}}
    <table class="kpis">
        <tr>
            <td style="width:25%">
                <div class="kpi">
                    <div class="label">Total ingresos (base)</div>
                    <div class="value">{{ $formatNum($totales['ingresos_base']) }} €</div>
                </div>
            </td>
            <td style="width:25%">
                <div class="kpi">
                    <div class="label">Total costes (base)</div>
                    <div class="value">{{ $formatNum($totales['costes_base']) }} €</div>
                </div>
            </td>
            <td style="width:25%">
                <div class="kpi {{ $totales['beneficio_bruto'] >= 0 ? 'positivo' : 'negativo' }}">
                    <div class="label">Beneficio bruto</div>
                    <div class="value">{{ $formatNum($totales['beneficio_bruto']) }} €</div>
                </div>
            </td>
            <td style="width:25%">
                <div class="kpi {{ ($totales['margen_pct'] ?? 0) >= 0 ? 'positivo' : 'negativo' }}">
                    <div class="label">Margen medio</div>
                    <div class="value">
                        {{ $totales['margen_pct'] !== null ? $formatNum($totales['margen_pct']) . ' %' : '—' }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- TABLA OBRAS --}}
    @if ($filas->isEmpty())
        <div class="empty-state">
            No se encontraron obras con los filtros aplicados.
        </div>
    @else
        <table class="obras">
            <thead>
                <tr>
                    <th style="width:3%">#</th>
                    <th style="width:18%">Obra</th>
                    <th style="width:8%">Estado</th>
                    <th class="right" style="width:10%">Presupuestado</th>
                    <th class="right" style="width:10%">Ingresos (base)</th>
                    <th class="right" style="width:9%">Ingresos (total)</th>
                    <th class="right" style="width:10%">Costes (base)</th>
                    <th class="right" style="width:9%">Costes (total)</th>
                    <th class="right" style="width:10%">Beneficio bruto</th>
                    <th class="right" style="width:7%">Margen %</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filas as $fila)
                    @php
                        $estadoClass = 'est-' . ($fila['estado'] ?? 'planificacion');
                        $margenClass = $fila['beneficio_bruto'] >= 0 ? 'margen-positivo' : 'margen-negativo';
                    @endphp
                    <tr>
                        <td>{{ $fila['id'] }}</td>
                        <td><strong>{{ $fila['nombre'] }}</strong></td>
                        <td>
                            <span class="estado-badge {{ $estadoClass }}">
                                {{ ucfirst($fila['estado']) }}
                            </span>
                        </td>
                        <td class="right">{{ $formatNum($fila['presupuestado']) }} €</td>
                        <td class="right">{{ $formatNum($fila['ingresos_base']) }} €</td>
                        <td class="right">{{ $formatNum($fila['ingresos_total']) }} €</td>
                        <td class="right">{{ $formatNum($fila['costes_base']) }} €</td>
                        <td class="right">{{ $formatNum($fila['costes_total']) }} €</td>
                        <td class="right {{ $margenClass }}">{{ $formatNum($fila['beneficio_bruto']) }} €</td>
                        <td class="right {{ $margenClass }}">
                            {{ $fila['margen_pct'] !== null ? $formatNum($fila['margen_pct']) . ' %' : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">TOTALES</td>
                    <td class="right">{{ $formatNum($totales['presupuestado']) }} €</td>
                    <td class="right">{{ $formatNum($totales['ingresos_base']) }} €</td>
                    <td class="right">{{ $formatNum($totales['ingresos_total']) }} €</td>
                    <td class="right">{{ $formatNum($totales['costes_base']) }} €</td>
                    <td class="right">{{ $formatNum($totales['costes_total']) }} €</td>
                    <td class="right">{{ $formatNum($totales['beneficio_bruto']) }} €</td>
                    <td class="right">
                        {{ $totales['margen_pct'] !== null ? $formatNum($totales['margen_pct']) . ' %' : '—' }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="nota">
        <strong>Nota:</strong> el análisis bruto compara la facturación emitida contra el coste de las facturas
        recibidas imputadas a cada obra en el periodo seleccionado. Los ingresos se calculan con facturas en
        estado emitida, enviada o pagada. El margen se calcula sobre el importe base (sin IVA).
    </div>

    <div class="pie">
        © {{ date('Y') }} {{ $empresa->nombre ?? 'Empresa' }} · Informe generado automáticamente
        @if ($piePdf)
            <div class="pie-personalizado">{{ $piePdf }}</div>
        @endif
    </div>

</body>

</html>
