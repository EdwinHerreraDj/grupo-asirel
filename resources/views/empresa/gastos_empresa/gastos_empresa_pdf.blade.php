<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Informe de Gastos de Empresa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            color: #333;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 180px;
        }

        .header .info {
            text-align: right;
        }

        .header .info h1 {
            margin: 0;
            font-size: 26px;
            color: #333;
        }

        .header .info p {
            margin: 2px 0;
            color: #666;
            font-size: 13px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #f9f9f9;
        }

        .table th,
        .table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table td {
            font-size: 11px;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            color: #555;
        }

        .table tfoot td {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>

<body>
    <!-- Encabezado -->
    <div class="header">
        <img src="{{ public_path('images/logo-dark.png') }}" alt="Logo">
        <div class="info">
            <h1>Informe de Gastos</h1>
            <p><strong>Fecha del informe:</strong> {{ now()->format('d/m/Y') }}</p>
            @if (!empty(request('inicio')) || !empty(request('fin')))
                <p>
                    <strong>Periodo:</strong>
                    @if(request('inicio') && request('fin'))
                        {{ \Carbon\Carbon::parse(request('inicio'))->format('d/m/Y') }}
                        — {{ \Carbon\Carbon::parse(request('fin'))->format('d/m/Y') }}
                    @elseif(request('inicio'))
                        Desde {{ \Carbon\Carbon::parse(request('inicio'))->format('d/m/Y') }}
                    @elseif(request('fin'))
                        Hasta {{ \Carbon\Carbon::parse(request('fin'))->format('d/m/Y') }}
                    @endif
                </p>
            @endif
        </div>
    </div>

    <!-- Tabla de Gastos -->
    <table class="table">
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Categoría</th>
                <th>Fecha</th>
                <th>Importe (€)</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach ($gastos as $gasto)
                <tr>
                    <td>{{ $gasto->concepto }}</td>
                    <td>{{ $gasto->categoria->nombre ?? '—' }}</td>
                    <td>
                        {{ $gasto->fecha_gasto ? \Carbon\Carbon::parse($gasto->fecha_gasto)->format('d/m/Y') : 'Sin fecha' }}
                    </td>
                    <td>{{ number_format($gasto->importe, 2, ',', '.') }} €</td>
                    <td>{{ $gasto->descripcion ?: '—' }}</td>
                </tr>
                @php $total += $gasto->importe; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;">Total general:</td>
                <td colspan="2">{{ number_format($total, 2, ',', '.') }} €</td>
            </tr>
        </tfoot>
    </table>

    <!-- Pie de página -->
    <div class="footer">
        <p>© {{ date('Y') }} Alminares S.L. Todos los derechos reservados.</p>
    </div>
</body>

</html>
