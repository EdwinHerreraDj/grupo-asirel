<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Informe General de la Obra</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* ===== ENCABEZADO ===== */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #f8fafc;
            border-bottom: 3px solid #2563eb;
            padding: 12px 24px;
        }

        .empresa-info {
            color: #374151;
            line-height: 1.5;
            font-size: 11px;
        }

        .empresa-info strong {
            font-size: 15px;
            color: #1e3a8a;
            display: block;
            margin-bottom: 2px;
        }

        .logo {
            max-height: 60px;
            max-width: 180px;
        }

        /* ===== TITULOS ===== */
        h1 {
            text-align: center;
            color: #1e40af;
            font-weight: bold;
            font-size: 18px;
            margin: 20px 0 5px;
        }

        .fecha {
            text-align: center;
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 15px;
        }

        /* ===== TABLA ===== */
        table {
            width: 90%;
            margin: 0 auto 25px;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
        }

        th {
            background-color: #e0f2fe;
            color: #1e3a8a;
            font-weight: bold;
            padding: 8px;
            text-align: left;
            border: 1px solid #cbd5e1;
        }

        td {
            border: 1px solid #e5e7eb;
            padding: 7px 8px;
            text-align: right;
            font-size: 12px;
        }

        td:first-child {
            text-align: left;
        }

        tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        tr:hover td {
            background-color: #f1f5f9;
        }

        /* ===== BALANCE ===== */
        .balance {
            width: 90%;
            margin: 0 auto;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 30px;
        }

        .positive {
            background-color: #dcfce7;
            color: #166534;
        }

        .negative {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* ===== PIE ===== */
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #f8fafc;
            border-top: 2px solid #2563eb;
            color: #1e3a8a;
            font-size: 10px;
            text-align: center;
            padding: 6px 0;
        }
    </style>
</head>

<body>

    {{-- ENCABEZADO --}}
    <header>
        <div class="empresa-info">
            <strong>{{ $empresa->nombre ?? 'Nombre de empresa no disponible' }}</strong>

            @if (!empty($empresa->direccion))
                {{ $empresa->direccion }}<br>
            @endif

            @if (!empty($empresa->codigo_postal) || !empty($empresa->ciudad) || !empty($empresa->provincia))
                {{ $empresa->codigo_postal ? $empresa->codigo_postal . ' - ' : '' }}
                {{ $empresa->ciudad ?? '' }}
                {{ !empty($empresa->provincia) ? ', ' . $empresa->provincia : '' }}<br>
            @endif

            @if (!empty($empresa->cif) || !empty($empresa->telefono))
                @if (!empty($empresa->cif))
                    CIF: {{ $empresa->cif }}
                @endif
                @if (!empty($empresa->telefono))
                    · Tel: {{ $empresa->telefono }}
                @endif
                <br>
            @endif

            @if (!empty($empresa->email) || !empty($empresa->sitio_web))
                @if (!empty($empresa->email))
                    Email: {{ $empresa->email }}
                @endif
                @if (!empty($empresa->sitio_web))
                    · {{ $empresa->sitio_web }}
                @endif
            @endif
        </div>

        @if (!empty($empresa->logo))
            <img src="{{ public_path('storage/' . $empresa->logo) }}" alt="Logo {{ $empresa->nombre }}" class="logo">
        @endif
    </header>

    {{-- TITULO --}}
    <h1>Informe General de la Obra: {{ $obra->nombre }}</h1>
    <p class="fecha">
        <strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}
    </p>

    <h2>Desglose de Facturas Recibidas (Pagadas)</h2>

    <table>
        <thead>
            <tr>
                <th>Nº Factura</th>
                <th>Proveedor</th>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Importe</th>
            </tr>
        </thead>
        <tbody>
            @forelse($facturas as $f)
                <tr>
                    <td>{{ $f->numero_factura }}</td>
                    <td>{{ $f->proveedor->nombre ?? '—' }}</td>
                    <td>{{ optional($f->fecha_factura)->format('d/m/Y') }}</td>
                    <td>{{ $f->concepto }}</td>
                    <td>{{ number_format($f->importe, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">No hay facturas pagadas</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Desglose de Certificaciones</h2>

    <table>
        <thead>
            <tr>
                <th>Nº Certificación</th>
                <th>Fecha</th>
                <th>Categoría</th>
                <th>Especificación</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($certificaciones as $c)
                <tr>
                    <td>{{ $c->numero_certificacion }}</td>
                    <td>{{ optional($c->fecha_ingreso)->format('d/m/Y') }}</td>
                    <td>{{ $c->oficio->nombre ?? '—' }}</td>
                    <td>{{ $c->especificacion }}</td>
                    <td>{{ number_format($c->total, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">No hay certificaciones</td>
                </tr>
            @endforelse
        </tbody>
    </table>



    {{-- TABLA DE GASTOS Y VENTAS --}}
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Total (€)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Gastos (Facturas pagadas)</td>
                <td>{{ number_format($totalGastos, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td>Total Ventas (Certificaciones)</td>
                <td>{{ number_format($totalVentas, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <th>Resultado</th>
                <th>{{ number_format($resultado, 2, ',', '.') }} €</th>
            </tr>
        </tbody>
    </table>

    {{-- BALANCE --}}
    <div class="balance {{ $resultado >= 0 ? 'positive' : 'negative' }}">
        <strong>Balance:</strong>
        {{ number_format($balance, 2, ',', '.') }}%
        ({{ $rentable }})
    </div>

    {{-- PIE --}}
    <footer>
        © {{ date('Y') }} {{ $empresa->nombre ?? 'Empresa' }} — Documento generado automáticamente por el sistema
        de gestión.
    </footer>

</body>

</html>
