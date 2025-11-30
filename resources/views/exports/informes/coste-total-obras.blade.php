<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 20px;
        }

        /* ==== ENCABEZADO EMPRESA ==== */
        .empresa {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #93c5fd;
        }
        .empresa strong {
            font-size: 14px;
            color: #1d4ed8;
        }
        .empresa p {
            margin: 2px 0;
            color: #374151;
            font-size: 11px;
        }

        /* ==== TITULO ==== */
        h2 {
            text-align: center;
            font-weight: bold;
            color: #1d4ed8;
            margin-bottom: 4px;
        }
        .meta {
            text-align: center;
            color: #4b5563;
            font-size: 11px;
            margin-bottom: 10px;
        }

        /* ==== TABLA ==== */
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
            background-color: #e0f2fe;
            color: #1e3a8a;
            font-weight: bold;
        }
        td:first-child, th:first-child {
            text-align: left;
        }
        tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        /* ==== TOTALES ==== */
        tfoot td {
            font-weight: bold;
            background-color: #eff6ff;
            border-top: 2px solid #93c5fd;
        }
    </style>
</head>
<body>

    {{-- Datos empresa (seguros para Excel y PDF) --}}
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
    <h2>Informe de Coste Total de Obras</h2>
    <p class="meta">
        <strong>Estado:</strong> {{ $filtros['estado'] !== 'todas' ? ucfirst($filtros['estado']) : 'Todos' }} |
        <strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}
    </p>
    @if($filtros['fechaInicio'] && $filtros['fechaFin'])
        <p class="meta">
            <strong>Periodo:</strong>
            {{ \Carbon\Carbon::parse($filtros['fechaInicio'])->format('d/m/Y') }} -
            {{ \Carbon\Carbon::parse($filtros['fechaFin'])->format('d/m/Y') }}
        </p>
    @endif

    {{-- Tabla principal --}}
    <table>
        <thead>
            <tr>
                <th>Obra</th>
                <th>Estado</th>
                <th>Materiales (€)</th>
                <th>Alquileres (€)</th>
                <th>Subcontratas (€)</th>
                <th>Gastos varios (€)</th>
                <th>Total (€)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resultado as $r)
                <tr>
                    <td>{{ $r['nombre'] }}</td>
                    <td>{{ ucfirst($r['estado']) }}</td>
                    <td>{{ number_format($r['materiales'], 2, ',', '.') }}</td>
                    <td>{{ number_format($r['alquileres'], 2, ',', '.') }}</td>
                    <td>{{ number_format($r['subcontratas'], 2, ',', '.') }}</td>
                    <td>{{ number_format($r['gastos_varios'], 2, ',', '.') }}</td>
                    <td><strong>{{ number_format($r['total'], 2, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align:right;">TOTAL GLOBAL</td>
                <td>{{ number_format($totalGlobal, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
