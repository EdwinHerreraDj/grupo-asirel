<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
            margin: 30px;
        }

        /* ===== CABECERA ===== */
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
            width: 140px;
        }

        .title-box {
            text-align: right;
        }

        .title-box h2 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 11px;
            color: #444;
            margin-top: 2px;
        }

        /* ===== BLOQUES ===== */
        .info-box {
            border: 1px solid #000;
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 16px;
        }

        .info-box p {
            margin: 4px 0;
            font-size: 11px;
        }

        /* ===== TABLA ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #000;
            color: #fff;
            font-size: 10px;
            text-transform: uppercase;
        }

        td {
            font-size: 11px;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        /* ===== TOTAL ===== */
        .total-box {
            margin-top: 10px;
            padding: 8px;
            border: 2px solid #000;
            text-align: right;
            font-size: 13px;
            font-weight: bold;
        }

        /* ===== CAMPOS MANUALES ===== */
        .manual-box {
            margin-top: 18px;
            border-top: 2px dashed #000;
            padding-top: 10px;
            font-size: 11px;
        }

        .manual-box p {
            margin: 6px 0;
        }

        /* ===== FIRMAS ===== */
        .signatures {
            margin-top: 30px;
            width: 100%;
        }

        .signatures td {
            border: none;
            padding-top: 50px;
            text-align: center;
            font-weight: bold;
        }

        .logo {
            max-width: 140px;
        }
    </style>
</head>

<body>

    {{-- ===== CABECERA CON LOGO ===== --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    @if (!empty($empresa->logo))
                        <img src="{{ public_path('storage/' . $empresa->logo) }}" class="logo"
                            alt="Logo {{ $empresa->nombre }}">
                    @else
                        <img src="{{ public_path('images/logo-dark.png') }}" class="logo" alt="Logo por defecto">
                    @endif
                </td>

                <td class="title-box">
                    <h2>ANEXO 1</h2>
                    <div class="subtitle">Mediciones y precios</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== DATOS BASE ===== --}}
    <div class="info-box">
        <p><strong>Obra:</strong> {{ $obra->nombre }}</p>
        <p><strong>Expediente:</strong> {{ $obra->id }}</p>
        <p><strong>Cliente:</strong> _______________________________</p>
        <p><strong>Proveedor:</strong> _______________________________</p>
    </div>

    {{-- ===== TABLA ===== --}}
    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Ud</th>
                <th class="right">Cantidad</th>
                <th class="right">Precio unit.</th>
                <th class="right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($oficios as $oficio)
                @php
                    $p = $presupuestos[$oficio->id] ?? null;
                @endphp
                @if ($p)
                    <tr>
                        <td>{{ $oficio->nombre }}</td>
                        <td>{{ $p->unidad }}</td>
                        <td class="right">{{ number_format($p->cantidad, 2, ',', '.') }}</td>
                        <td class="right">{{ number_format($p->precio_unitario, 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format($p->importe_total, 2, ',', '.') }} €</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    {{-- ===== TOTAL ===== --}}
    <div class="total-box">
        TOTAL: {{ number_format($total, 2, ',', '.') }} €
    </div>

    {{-- ===== CAMPOS MANUALES ===== --}}
    <div class="manual-box">
        <p><strong>Fecha inicio trabajos:</strong> _______________________________</p>
        <p><strong>Fecha fin trabajos:</strong> _______________________________</p>
        <p><strong>Forma de pago:</strong> _______________________________</p>
        <p><strong>Retención:</strong> _______________________________</p>
    </div>

    {{-- ===== FIRMAS ===== --}}
    <table class="signatures">
        <tr>
            <td>Firma proveedor</td>
            <td>Firma cliente</td>
        </tr>
    </table>

</body>

</html>
