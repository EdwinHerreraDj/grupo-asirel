<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Factura {{ $factura->serie }}-{{ $factura->numero_factura }}</title>

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
            color: #111;
            margin: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo {
            width: 140px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
        }

        .box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 11px;
        }

        th {
            background: #f3f4f6;
            text-transform: uppercase;
        }

        .right {
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            font-size: 10px;
            text-align: center;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <table class="header-table" style="margin-bottom: 20px;">
        <tr>
            <td>
                @php
                    $logoPath = storage_path('app/public/' . $empresa->logo);
                    $logoBase64 = base64_encode(file_get_contents($logoPath));
                @endphp

                <img class="logo" src="data:image/png;base64,{{ $logoBase64 }}">


            </td>
            <td class="right">
                <div class="title">FACTURA</div>
                <div><strong>{{ $factura->serie }}-{{ $factura->numero_factura }}</strong></div>
                <div>Fecha: {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- EMPRESA --}}
    <div class="box">
        <strong>{{ $empresa->nombre }}</strong><br>
        {{ $empresa->direccion }}<br>
        {{ $empresa->codigo_postal }} {{ $empresa->ciudad }} ({{ $empresa->provincia }})<br>
        CIF: {{ $empresa->cif }}
    </div>

    {{-- CLIENTE --}}
    <div class="box">
        <strong>Cliente</strong><br>
        {{ $factura->cliente->nombre }}<br>
        {{ $factura->cliente->direccion }}<br>
        {{ $factura->cliente->codigo_postal }} {{ $factura->cliente->ciudad }}<br>
        NIF: {{ $factura->cliente->nif }}
    </div>

    {{-- LÍNEAS --}}
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Unidad</th>
                <th class="right">Cantidad</th>
                <th class="right">Precio</th>
                <th class="right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($factura->detalles as $d)
                <tr>
                    <td>{{ $d->concepto }}</td>
                    <td>{{ $d->unidad }}</td>
                    <td class="right">{{ number_format($d->cantidad, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($d->precio_unitario, 2, ',', '.') }} €</td>
                    <td class="right">{{ number_format($d->importe_linea, 2, ',', '.') }} €</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="right"><strong>Base imponible</strong></td>
                <td class="right">{{ number_format($factura->base_imponible, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td colspan="4" class="right"><strong>IVA</strong></td>
                <td class="right">{{ number_format($factura->iva_importe, 2, ',', '.') }} €</td>
            </tr>
            @if ($factura->retencion_porcentaje > 0)
                <tr>
                    <td colspan="4" class="right"><strong>Retención</strong></td>
                    <td class="right">-{{ number_format($factura->retencion_importe, 2, ',', '.') }} €</td>
                </tr>
            @endif
            <tr>
                <td colspan="4" class="right"><strong>TOTAL</strong></td>
                <td class="right"><strong>{{ number_format($factura->total, 2, ',', '.') }} €</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Documento generado automaticamente - {{ now()->format('d/m/Y H:i') }}

    </div>

</body>

</html>
