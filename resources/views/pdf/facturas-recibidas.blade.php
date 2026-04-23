@php
    $colorPrimario = $empresa->color_primario ?? '#111827';
    $colorSecundario = $empresa->color_secundario ?? '#d1d5db';
    $mostrarLogo = $empresa?->mostrar_logo_pdf ?? true;
    $piePdf = $empresa->pie_pdf ?? null;
    $estados = [
        'pendiente_emision_doc_pago' => 'Pendiente emisión',
        'pendiente_vencimiento'      => 'Pendiente vencimiento',
        'devuelta'                   => 'Devuelta',
        'pagada'                     => 'Pagada',
        'impagada'                   => 'Impagada',
    ];
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Informe de facturas recibidas — {{ $obra->nombre }}</title>
    <style>
        @page {
            margin: 25mm 15mm 20mm 15mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            color: #111;
            margin: 0;
        }

        /* ===== HEADER ===== */
        .header {
            width: 100%;
            border-bottom: 2px solid {{ $colorPrimario }};
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .header td {
            vertical-align: middle;
            padding: 0;
        }

        .logo-cell {
            width: 140px;
        }

        .logo-cell img {
            max-width: 130px;
            max-height: 60px;
        }

        .empresa-info {
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }

        .empresa-info .nombre {
            font-size: 13px;
            font-weight: bold;
            color: {{ $colorPrimario }};
            margin-bottom: 2px;
        }

        .titulo-cell {
            text-align: right;
        }

        .titulo-informe {
            font-size: 18px;
            font-weight: bold;
            color: {{ $colorPrimario }};
            letter-spacing: 0.5px;
            margin: 0;
        }

        .subtitulo-informe {
            font-size: 10.5px;
            color: #555;
            margin-top: 3px;
        }

        /* ===== META OBRA ===== */
        .obra-meta {
            background: #f8f8f8;
            border-left: 3px solid {{ $colorPrimario }};
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 10.5px;
        }

        .obra-meta .etiqueta {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #777;
            display: block;
        }

        .obra-meta .valor {
            font-weight: bold;
            color: #111;
            font-size: 12px;
        }

        /* ===== RESUMEN ===== */
        .resumen {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
        }

        .resumen td {
            padding: 0;
        }

        .resumen .chip {
            border: 1px solid {{ $colorSecundario }};
            border-radius: 8px;
            padding: 8px 10px;
            margin-right: 6px;
            text-align: center;
            background: #fafafa;
        }

        .resumen .chip .label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #777;
            display: block;
            margin-bottom: 3px;
        }

        .resumen .chip .valor {
            font-size: 12px;
            font-weight: bold;
            color: {{ $colorPrimario }};
        }

        /* ===== TABLA ===== */
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        table.data thead tr {
            background-color: {{ $colorPrimario }};
        }

        table.data thead th {
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 6px;
            text-align: left;
            letter-spacing: 0.5px;
            border: none;
        }

        table.data thead th.num {
            text-align: right;
        }

        table.data tbody td {
            padding: 7px 6px;
            font-size: 9.5px;
            border-bottom: 1px solid {{ $colorSecundario }};
            color: #222;
        }

        table.data tbody td.num {
            text-align: right;
        }

        table.data tbody tr:nth-child(even) td {
            background-color: #fafafa;
        }

        /* Estado como píldora */
        .estado-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid;
        }

        .estado-pagada {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }

        .estado-impagada {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .estado-devuelta {
            background: #fffbeb;
            color: #92400e;
            border-color: #fde68a;
        }

        .estado-pendiente_emision_doc_pago,
        .estado-pendiente_vencimiento {
            background: #eff6ff;
            color: #1e40af;
            border-color: #bfdbfe;
        }

        /* Totales */
        table.data tfoot tr {
            background-color: {{ $colorPrimario }};
        }

        table.data tfoot td {
            padding: 10px 6px;
            font-weight: bold;
            color: #ffffff;
            font-size: 10px;
            border: none;
        }

        table.data tfoot td.num {
            text-align: right;
        }

        /* ===== PIE ===== */
        .pie {
            margin-top: 22px;
            padding-top: 10px;
            border-top: 1px solid {{ $colorSecundario }};
            font-size: 9px;
            color: #666;
            text-align: center;
            line-height: 1.5;
        }

        .pie-personalizado {
            margin-top: 6px;
            font-style: italic;
            color: #555;
        }
    </style>
</head>

<body>

    {{-- ================ HEADER ================ --}}
    <table class="header">
        <tr>
            @if ($mostrarLogo)
                <td class="logo-cell">
                    @if (!empty($empresa?->logo))
                        <img src="{{ public_path('storage/' . $empresa->logo) }}"
                            alt="Logo {{ $empresa->nombre }}">
                    @endif
                </td>
            @endif
            <td class="empresa-info">
                <div class="nombre">{{ $empresa->nombre ?? 'Empresa' }}</div>
                @if (!empty($empresa->direccion))
                    {{ $empresa->direccion }}<br>
                @endif
                {{ $empresa->codigo_postal ?? '' }} {{ $empresa->ciudad ?? '' }}
                {{ !empty($empresa->provincia) ? '(' . $empresa->provincia . ')' : '' }}
                @if (!empty($empresa->cif))
                    <br>CIF: {{ $empresa->cif }}
                @endif
                @if (!empty($empresa->telefono))
                    · Tel: {{ $empresa->telefono }}
                @endif
                @if (!empty($empresa->email))
                    · {{ $empresa->email }}
                @endif
            </td>
            <td class="titulo-cell">
                <div class="titulo-informe">Facturas recibidas</div>
                <div class="subtitulo-informe">Informe generado el {{ now()->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>

    {{-- ================ META OBRA ================ --}}
    <div class="obra-meta">
        <span class="etiqueta">Obra</span>
        <span class="valor">{{ $obra->nombre }}</span>
    </div>

    {{-- ================ RESUMEN CHIPS ================ --}}
    <table class="resumen">
        <tr>
            <td style="width:25%">
                <div class="chip">
                    <span class="label">Nº facturas</span>
                    <span class="valor">{{ count($facturas) }}</span>
                </div>
            </td>
            <td style="width:25%">
                <div class="chip">
                    <span class="label">Base total</span>
                    <span class="valor">{{ number_format($totales['base'], 2, ',', '.') }} €</span>
                </div>
            </td>
            <td style="width:25%">
                <div class="chip">
                    <span class="label">IVA</span>
                    <span class="valor">{{ number_format($totales['iva'], 2, ',', '.') }} €</span>
                </div>
            </td>
            <td style="width:25%">
                <div class="chip">
                    <span class="label">Total</span>
                    <span class="valor">{{ number_format($totales['total'], 2, ',', '.') }} €</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- ================ TABLA ================ --}}
    <table class="data">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Oficio</th>
                <th>Nº factura</th>
                <th>Estado</th>
                <th class="num">Base</th>
                <th class="num">IVA</th>
                <th class="num">IRPF</th>
                <th class="num">Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($facturas as $f)
                <tr>
                    <td>{{ $f->fecha_factura?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $f->proveedor->nombre ?? '—' }}</td>
                    <td>{{ $f->oficio->nombre ?? '—' }}</td>
                    <td>{{ $f->numero_factura ?: '—' }}</td>
                    <td>
                        <span class="estado-pill estado-{{ $f->estado }}">
                            {{ $estados[$f->estado] ?? $f->estado }}
                        </span>
                    </td>
                    <td class="num">{{ number_format($f->base_imponible, 2, ',', '.') }} €</td>
                    <td class="num">{{ number_format($f->iva_importe, 2, ',', '.') }} €</td>
                    <td class="num">{{ number_format($f->retencion_importe, 2, ',', '.') }} €</td>
                    <td class="num">{{ number_format($f->total, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:20px; color:#777; font-style:italic;">
                        Sin facturas que coincidan con los filtros.
                    </td>
                </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr>
                <td colspan="5">TOTALES</td>
                <td class="num">{{ number_format($totales['base'], 2, ',', '.') }} €</td>
                <td class="num">{{ number_format($totales['iva'], 2, ',', '.') }} €</td>
                <td class="num">{{ number_format($totales['retencion'], 2, ',', '.') }} €</td>
                <td class="num">{{ number_format($totales['total'], 2, ',', '.') }} €</td>
            </tr>
        </tfoot>
    </table>

    {{-- ================ PIE ================ --}}
    <div class="pie">
        © {{ date('Y') }} {{ $empresa->nombre ?? 'ERP Obras' }} · Documento generado automáticamente
        @if ($piePdf)
            <div class="pie-personalizado">{{ $piePdf }}</div>
        @endif
    </div>

</body>

</html>
