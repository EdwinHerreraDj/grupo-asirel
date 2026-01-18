<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Informe de certificación</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .header {
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .empresa {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .datos {
            width: 100%;
            font-size: 11px;
        }

        .datos td {
            padding: 3px 0;
        }

        .capitulo {
            margin-top: 20px;
        }

        .capitulo h3 {
            background: #f2f2f2;
            padding: 6px;
            font-size: 13px;
            border: 1px solid #ccc;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        th {
            background: #eaeaea;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .total-capitulo {
            font-weight: bold;
            background: #fafafa;
        }

        .total-general {
            margin-top: 25px;
            width: 100%;
            font-size: 14px;
        }

        .total-general td {
            padding: 8px;
        }

        .label {
            text-align: right;
            font-weight: bold;
        }

        .nota {
            margin-top: 35px;
            font-size: 10px;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    {{-- CABECERA --}}
    <div class="header">
        <div class="empresa">
            {{ $empresa->nombre ?? config('app.name') }}
        </div>

        <table class="datos">
            <tr>
                <td><strong>Obra:</strong> {{ $obra->nombre ?? '—' }}</td>
                <td><strong>Cliente:</strong> {{ $cliente->nombre ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Nº certificación:</strong> {{ $numero_certificacion }}</td>
                <td><strong>Fecha:</strong> {{ $fecha }}</td>
            </tr>
        </table>
    </div>

    {{-- CUERPO --}}
    @foreach ($capitulos as $capitulo)
        <div class="capitulo">
            <h3>{{ $capitulo['oficio'] }}</h3>

            <table>
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th class="right">Cantidad</th>
                        <th class="right">Precio</th>
                        <th class="right">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($capitulo['lineas'] as $linea)
                        <tr>
                            <td>{{ $linea['descripcion'] }}</td>
                            <td class="right">
                                {{ number_format($linea['cantidad'], 2, ',', '.') }}
                            </td>
                            <td class="right">
                                {{ number_format($linea['precio'], 2, ',', '.') }} €
                            </td>
                            <td class="right">
                                {{ number_format($linea['total'], 2, ',', '.') }} €
                            </td>
                        </tr>
                    @endforeach

                    <tr class="total-capitulo">
                        <td colspan="3" class="right">Total {{ $capitulo['oficio'] }}</td>
                        <td class="right">
                            {{ number_format($capitulo['total'], 2, ',', '.') }} €
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>
    @endforeach

    {{-- TOTAL GENERAL --}}
    <table class="total-general">
        <tr>
            <td class="label">TOTAL CERTIFICADO</td>
            <td class="right">
                {{ number_format($total, 2, ',', '.') }} €
            </td>
        </tr>
    </table>

    {{-- NOTA --}}
    <div class="nota">
        Este documento es un informe de certificación y no constituye una factura ni tiene validez fiscal.
        Los importes reflejan los trabajos certificados a la fecha indicada.
    </div>

</body>

</html>
