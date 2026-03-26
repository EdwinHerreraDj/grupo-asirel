<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 30px;
        }

        /* CABECERA */
        .header {
            width: 100%;
            border-bottom: 3px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
        }

        .logo {
            max-width: 140px;
        }

        .title-box {
            text-align: right;
        }

        .title-box h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .subtitle {
            font-size: 10px;
            color: #444;
            margin-top: 2px;
        }

        /* INFO */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .info-grid td {
            border: none;
            padding: 2px 6px;
            vertical-align: top;
            font-size: 11px;
        }

        .info-box {
            border: 1px solid #000;
            background: #f5f5f5;
            padding: 8px 10px;
        }

        .info-box p {
            margin: 3px 0;
            font-size: 11px;
        }

        /* CAPÍTULO */
        .capitulo-header {
            background: #222;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 6px 8px;
            margin-top: 14px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* TABLA PARTIDAS */
        table.partidas {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        table.partidas thead tr {
            background: #444;
            color: #fff;
        }

        table.partidas th {
            padding: 5px 6px;
            font-size: 9px;
            text-transform: uppercase;
            text-align: left;
            border: 1px solid #333;
        }

        table.partidas th.right {
            text-align: right;
        }

        table.partidas td {
            padding: 5px 6px;
            font-size: 10px;
            border: 1px solid #ccc;
            vertical-align: top;
        }

        table.partidas td.right {
            text-align: right;
        }

        table.partidas tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        /* SUBTOTAL CAPÍTULO */
        .subtotal-row td {
            background: #eee;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #999;
            padding: 5px 6px;
            text-align: right;
        }

        .subtotal-row td.label {
            text-align: left;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.3px;
        }

        /* TOTAL GENERAL */
        .total-box {
            margin-top: 16px;
            padding: 10px 12px;
            border: 2px solid #000;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            background: #f0f0f0;
        }

        /* CAMPOS MANUALES */
        .manual-box {
            margin-top: 20px;
            border-top: 2px dashed #000;
            padding-top: 12px;
            font-size: 11px;
        }

        .manual-box p {
            margin: 7px 0;
        }

        /* FIRMAS */
        table.signatures {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
        }

        table.signatures td {
            border: none;
            padding-top: 50px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            width: 50%;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    {{-- CABECERA --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    @if (!empty($empresa->logo))
                        <img src="{{ public_path('storage/' . $empresa->logo) }}" class="logo" alt="Logo">
                    @else
                        <img src="{{ public_path('images/logo-dark.png') }}" class="logo" alt="Logo">
                    @endif
                </td>
                <td class="title-box">
                    <h2>Presupuesto de venta</h2>
                    <div class="subtitle">Mediciones y precios unitarios</div>
                    <div class="subtitle">Fecha: {{ $fecha }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- DATOS OBRA + CLIENTE --}}
    <table class="info-grid">
        <tr>
            <td width="50%">
                <div class="info-box">
                    <p><strong>Obra:</strong> {{ $obra->nombre }}</p>
                    <p><strong>Expediente:</strong> {{ $obra->id }}</p>
                    @if ($obra->fecha_inicio)
                        <p><strong>Fecha inicio:</strong>
                            {{ \Carbon\Carbon::parse($obra->fecha_inicio)->format('d/m/Y') }}</p>
                    @endif
                </div>
            </td>
            <td width="50%">
                <div class="info-box">
                    <p><strong>Cliente:</strong> {{ $cliente->nombre }}</p>
                    @if (!empty($cliente->nif))
                        <p><strong>NIF:</strong> {{ $cliente->nif }}</p>
                    @endif
                    @if (!empty($cliente->direccion))
                        <p><strong>Dirección:</strong> {{ $cliente->direccion }}</p>
                    @endif
                    @if (!empty($cliente->telefono))
                        <p><strong>Teléfono:</strong> {{ $cliente->telefono }}</p>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- CAPÍTULOS Y PARTIDAS --}}
    @foreach ($capitulos as $capitulo)
        {{-- CABECERA CAPÍTULO --}}
        <div class="capitulo-header">
            {{ $capitulo['nombre'] }}
        </div>

        {{-- TABLA PARTIDAS --}}
        <table class="partidas">
            <thead>
                <tr>
                    <th style="width:8%">Código</th>
                    <th style="width:40%">Descripción</th>
                    <th style="width:7%">Ud.</th>
                    <th class="right" style="width:12%">Medición</th>
                    <th class="right" style="width:13%">Precio unit.</th>
                    <th class="right" style="width:13%">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($capitulo['partidas'] as $partida)
                    <tr>
                        <td>{{ $partida['codigo'] ?? '—' }}</td>
                        <td>{{ $partida['descripcion'] }}</td>
                        <td>{{ $partida['unidad'] ?? '—' }}</td>
                        <td class="right">
                            {{ number_format($partida['medicion'], 4, ',', '.') }}
                        </td>
                        <td class="right">
                            {{ number_format($partida['precio_unitario'], 2, ',', '.') }} €
                        </td>
                        <td class="right">
                            {{ number_format($partida['importe'], 2, ',', '.') }} €
                        </td>
                    </tr>
                @endforeach

                {{-- SUBTOTAL CAPÍTULO --}}
                <tr class="subtotal-row">
                    <td colspan="5" class="label">Total {{ $capitulo['nombre'] }}</td>
                    <td>{{ number_format($capitulo['importe_total'], 2, ',', '.') }} €</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    {{-- TOTAL GENERAL --}}
    <div class="total-box">
        TOTAL PRESUPUESTO: {{ number_format($total, 2, ',', '.') }} €
    </div>

    {{-- CAMPOS MANUALES --}}
    <div class="manual-box">
        <p><strong>Fecha inicio trabajos:</strong> _______________________________</p>
        <p><strong>Fecha fin trabajos:</strong> _______________________________</p>
        <p><strong>Forma de pago:</strong> _______________________________</p>
        <p><strong>Retención:</strong> _______________________________</p>
    </div>

    {{-- FIRMAS --}}
    <table class="signatures">
        <tr>
            <td>Firma proveedor</td>
            <td>Firma cliente</td>
        </tr>
    </table>

</body>

</html>
