<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Informe de Materiales</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111111;
            background-color: #ffffff;
            margin: 40px;
        }

        /* ===== ENCABEZADO ===== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333333;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }

        .header img {
            width: 140px;
            opacity: 0.9;
        }

        .header .info {
            text-align: right;
        }

        .header .info h1 {
            margin: 0;
            font-size: 22px;
            color: #111111;
            letter-spacing: 0.5px;
        }

        .header .info p {
            margin: 2px 0;
            color: #444444;
            font-size: 11.5px;
        }

        /* ===== SEPARADOR OBRA Y FECHA ===== */
        .meta {
            text-align: center;
            color: #555555;
            font-size: 11.5px;
            margin-bottom: 18px;
        }

        /* ===== TABLA ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 1px solid #dddddd;
        }

        th,
        td {
            border: 1px solid #dddddd;
            padding: 8px 10px;
            font-size: 11.5px;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            color: #111111;
            font-size: 11px;
            text-align: left;
        }

        td {
            color: #222222;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        tfoot td {
            background-color: #f5f5f5;
            font-weight: bold;
            border-top: 2px solid #999999;
        }

        /* ===== PIE DE PÁGINA ===== */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #555555;
            border-top: 1px solid #cccccc;
            padding-top: 10px;
        }

        /* ===== DETALLES DE EMPRESA (arriba del logo si aplica) ===== */
        .empresa {
            margin-top: 10px;
            color: #333333;
            font-size: 11px;
        }

        .empresa strong {
            font-size: 13px;
            color: #000000;
        }
    </style>
</head>

<body>

    {{-- ENCABEZADO --}}
    <div class="header">
        @if (!empty($empresa->logo))
            <img src="{{ public_path('storage/' . $empresa->logo) }}" alt="Logo {{ $empresa->nombre }}">
        @else
            <img src="{{ public_path('images/logo-dark.png') }}" alt="Logo por defecto">
        @endif

        <div class="info">
            <h1>Informe facturas recibidas</h1>
            <p><strong>Obra:</strong> {{ $obra->nombre }}</p>
            <p><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>

    {{-- EMPRESA (opcional) --}}
    <div class="empresa">
        <strong>{{ $empresa->nombre ?? 'Empresa' }}</strong><br>
        @if (!empty($empresa->direccion))
            {{ $empresa->direccion }}<br>
        @endif
        {{ $empresa->codigo_postal ?? '' }} {{ $empresa->ciudad ?? '' }}
        {{ !empty($empresa->provincia) ? '(' . $empresa->provincia . ')' : '' }}<br>
        @if (!empty($empresa->cif))
            CIF: {{ $empresa->cif }}
        @endif
        @if (!empty($empresa->telefono))
            · Tel: {{ $empresa->telefono }}
        @endif
        @if (!empty($empresa->email))
            · {{ $empresa->email }}
        @endif
    </div>

    {{-- META --}}
    <div class="meta">
        Documento generado automáticamente el {{ now()->format('d/m/Y H:i') }}
    </div>

    {{-- TABLA DE MATERIALES --}}
    <table>
        <thead>
            <tr>
                <th>Proveedor</th>
                <th>Oficio</th>
                <th>Factura</th>
                <th>Base</th>
                <th>IVA</th>
                <th>IRPF</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($facturas as $f)
                <tr>
                    <td>{{ $f->proveedor->nombre ?? '—' }}</td>
                    <td>{{ $f->oficio->nombre ?? '—' }}</td>
                    <td>{{ $f->numero_factura }}</td>
                    <td>{{ number_format($f->base_imponible, 2, ',', '.') }} €</td>
                    <td>{{ number_format($f->iva_importe, 2, ',', '.') }} €</td>
                    <td>{{ number_format($f->retencion_importe, 2, ',', '.') }} €</td>
                    <td>{{ number_format($f->total, 2, ',', '.') }} €</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="3">TOTALES</td>
                <td>{{ number_format($totales['base'], 2, ',', '.') }} €</td>
                <td>{{ number_format($totales['iva'], 2, ',', '.') }} €</td>
                <td>{{ number_format($totales['retencion'], 2, ',', '.') }} €</td>
                <td>{{ number_format($totales['total'], 2, ',', '.') }} €</td>
            </tr>
        </tfoot>
    </table>


    {{-- PIE --}}
    <div class="footer">
        © {{ date('Y') }} {{ $empresa->nombre ?? 'ERP Obras' }} — Documento generado automáticamente.
    </div>

</body>

</html>
