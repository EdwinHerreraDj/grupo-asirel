<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 20px;
        }

        /* === ENCABEZADO EMPRESA === */
        .empresa {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #059669;
        }

        .empresa strong {
            font-size: 14px;
            color: #047857;
        }

        .empresa p {
            margin: 2px 0;
            color: #374151;
            font-size: 11px;
        }

        /* === TITULOS === */
        h2 {
            text-align: center;
            font-weight: bold;
            color: #065f46;
            margin-bottom: 4px;
        }

        .meta {
            text-align: center;
            color: #4b5563;
            font-size: 11px;
            margin-bottom: 10px;
        }

        /* === TABLA === */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: right;
            vertical-align: middle;
        }

        th {
            background-color: #a7f3d0;
            color: #064e3b;
            font-weight: bold;
        }

        td:first-child, th:first-child {
            text-align: left;
        }

        tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        /* === COLORES DE VALOR === */
        .negativo {
            color: #b91c1c;
            font-weight: bold;
        }

        .positivo {
            color: #065f46;
            font-weight: bold;
        }

        /* === TOTALES === */
        tfoot td {
            font-weight: bold;
            background-color: #ecfdf5;
            border-top: 2px solid #059669;
        }

        /* === DATOS === */
        .datos {
            text-align: center;
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    {{-- ENCABEZADO EMPRESA (compatible con PDF y Excel) --}}
    @if(isset($empresa))
        <div class="empresa">
            <strong>{{ $empresa->nombre ?? 'Empresa' }}</strong><br>
            @if(!empty($empresa->direccion))
                <p>{{ $empresa->direccion }}</p>
            @endif
            @if(!empty($empresa->codigo_postal) || !empty($empresa->ciudad))
                <p>{{ $empresa->codigo_postal ?? '' }} {{ $empresa->ciudad ?? '' }} {{ !empty($empresa->provincia) ? '(' . $empresa->provincia . ')' : '' }}</p>
            @endif
            <p>
                @if(!empty($empresa->cif)) CIF: {{ $empresa->cif }} @endif
                @if(!empty($empresa->telefono)) · Tel: {{ $empresa->telefono }} @endif
            </p>
            @if(!empty($empresa->email) || !empty($empresa->sitio_web))
                <p>
                    @if(!empty($empresa->email)) {{ $empresa->email }} @endif
                    @if(!empty($empresa->sitio_web)) · {{ $empresa->sitio_web }} @endif
                </p>
            @endif
        </div>
    @endif

    {{-- Título y metadatos --}}
    <h2>Informe Coste - Venta Mensual</h2>
    <p class="meta">
        <strong>% Gastos Internos:</strong> {{ $filtros['porcentaje'] ?? 0 }}% |
        <strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}
    </p>

    {{-- Tabla principal --}}
    <table>
        <thead>
            <tr>
                <th>Obra</th>
                <th>Mes</th>
                <th>Coste Real (€)</th>
                <th>% Extra</th>
                <th>Coste Ajustado (€)</th>
                <th>Facturación (€)</th>
                <th>Beneficio (€)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resultado as $r)
                <tr>
                    <td>{{ $r['obra'] }}</td>
                    <td>{{ $r['mes'] }}</td>
                    <td>{{ number_format($r['coste_real'], 2, ',', '.') }}</td>
                    <td>{{ $r['porcentaje'] }}%</td>
                    <td>{{ number_format($r['coste_ajustado'], 2, ',', '.') }}</td>
                    <td>{{ number_format($r['facturacion'], 2, ',', '.') }}</td>
                    <td class="{{ $r['beneficio'] < 0 ? 'negativo' : 'positivo' }}">
                        {{ number_format($r['beneficio'], 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
