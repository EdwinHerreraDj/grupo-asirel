<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comparativa mensual</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 18mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            color: #111;
        }

        h1 {
            font-size: 18px;
            margin: 0;
        }

        .header {
            border-bottom: 2px solid #000;
            margin-bottom: 12px;
            padding-bottom: 6px;
        }

        .meta {
            font-size: 10px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px 5px;
        }

        th {
            background: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        td.right {
            text-align: right;
        }

        td.center {
            text-align: center;
        }

        td.partida {
            width: 26%;
        }

        .totales {
            margin-top: 18px;
            width: 45%;
            float: right;
            font-size: 11px;
        }

        .totales table td {
            border: none;
            padding: 3px 4px;
        }

        .totales .label {
            text-align: right;
            padding-right: 10px;
        }

        .totales .importe {
            text-align: right;
            width: 90px;
        }

        .totales .strong {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .totales .iva {
            color: #b00000;
        }

        .totales .liquido {
            font-size: 12px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 6px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>

{{-- CABECERA --}}
<div class="header">
    <h1>COMPARATIVA MENSUAL DE CERTIFICACIONES</h1>
</div>

{{-- META --}}
<div class="meta">
    <strong>Obra:</strong> {{ $obra->nombre }}<br>
    <strong>Periodo:</strong> {{ $periodo }}
</div>

{{-- TABLA PRINCIPAL --}}
<table>
    <thead>
        <tr>
            <th class="partida">Partida</th>
            <th>Ud</th>
            <th>Contrato</th>
            <th>Origen ant.</th>
            <th>Mes</th>
            <th>A origen</th>
            <th>Pendiente</th>
            <th>Precio</th>
            <th>Imp. mes</th>
            <th>Imp. a origen</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($filas as $fila)
            <tr>
                <td class="partida">{{ $fila['oficio'] }}</td>
                <td class="center">{{ $fila['unidad'] }}</td>
                <td class="right">{{ number_format($fila['contrato'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($fila['origen_anterior'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($fila['mes'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($fila['a_origen'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($fila['pendiente'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($fila['precio_unitario'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($fila['importe_mes'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($fila['importe_origen'], 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- BLOQUE ECONÓMICO --}}
@php
    $importeMes = collect($filas)->sum('importe_mes');
    $importeOrigen = collect($filas)->sum('importe_origen');

    $iva = round($importeMes * 0.21, 2);
    $irpf = round($importeMes * 0.05, 2);
    $liquido = $importeMes + $iva - $irpf;
@endphp

<div class="clearfix">
    <div class="totales">
        <table>
            <tr>
                <td class="label">Importe facturado a origen:</td>
                <td class="importe">{{ number_format($importeOrigen, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td class="label">Importe facturado en el mes:</td>
                <td class="importe strong">{{ number_format($importeMes, 2, ',', '.') }} €</td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
