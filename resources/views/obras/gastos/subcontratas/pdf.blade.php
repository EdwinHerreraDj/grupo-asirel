<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Subcontratas</title>
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

        .info {
            text-align: right;
        }

        .info h1 {
            margin: 0;
            font-size: 22px;
            color: #000000;
            letter-spacing: 0.5px;
        }

        .info p {
            margin: 2px 0;
            color: #444444;
            font-size: 11.5px;
        }

        /* ===== META ===== */
        .meta {
            text-align: center;
            color: #555555;
            font-size: 11.5px;
            margin-bottom: 15px;
        }

        /* ===== TABLA ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 1px solid #dddddd;
        }

        th, td {
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

        /* ===== EMPRESA ===== */
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
        @if(!empty($empresa->logo))
            <img src="{{ public_path('storage/' . $empresa->logo) }}" alt="Logo {{ $empresa->nombre }}">
        @else
            <img src="{{ public_path('images/logo-dark.png') }}" alt="Logo por defecto">
        @endif

        <div class="info">
            <h1>Informe de Subcontratas</h1>
            <p><strong>Obra:</strong> {{ $obra->nombre }}</p>
            <p><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>

    {{-- DATOS EMPRESA --}}
    <div class="empresa">
        <strong>{{ $empresa->nombre ?? 'Empresa' }}</strong><br>
        @if(!empty($empresa->direccion))
            {{ $empresa->direccion }}<br>
        @endif
        {{ $empresa->codigo_postal ?? '' }} {{ $empresa->ciudad ?? '' }} {{ !empty($empresa->provincia) ? '(' . $empresa->provincia . ')' : '' }}<br>
        @if(!empty($empresa->cif)) CIF: {{ $empresa->cif }} @endif
        @if(!empty($empresa->telefono)) · Tel: {{ $empresa->telefono }} @endif
        @if(!empty($empresa->email)) · {{ $empresa->email }} @endif
    </div>

    {{-- FECHA DE GENERACIÓN DEL INFORME --}}
    <p class="meta">
        <strong>Generado automáticamente el:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </p>

    {{-- TABLA DE SUBCONTRATAS --}}
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Fecha</th>
                <th>Importe (€)</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($subcontratas as $subcontrata)
                @php $total += $subcontrata->importe; @endphp
                <tr>
                    <td>{{ $subcontrata->id }}</td>
                    <td>{{ $subcontrata->nombre }}</td>
                    <td>{{ $subcontrata->descripcion }}</td>
                    <td>{{ \Carbon\Carbon::parse($subcontrata->fecha)->format('d/m/Y') }}</td>
                    <td>{{ number_format($subcontrata->importe, 2, ',', '.') }} €</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;">Total general:</td>
                <td>{{ number_format($total, 2, ',', '.') }} €</td>
            </tr>
        </tfoot>
    </table>

    {{-- PIE --}}
    <div class="footer">
        © {{ date('Y') }} {{ $empresa->nombre ?? 'Geocaminos' }} — Documento generado automáticamente por el sistema de gestión.
    </div>

</body>
</html>