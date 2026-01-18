<table>
    <thead>
        <tr>
            <th colspan="9"><strong>Informe de Facturas Recibidas</strong></th>
        </tr>
        <tr>
            <td colspan="9">
                Obra: {{ $obra->nombre }} |
                Fecha: {{ now()->format('d/m/Y') }}
            </td>
        </tr>
        <tr></tr>

        <tr>
            <th>Proveedor</th>
            <th>Oficio</th>
            <th>Concepto</th>
            <th>Fecha</th>
            <th>Base</th>
            <th>IVA</th>
            <th>Retención</th>
            <th>Total</th>
            <th>Estado</th>
        </tr>
    </thead>

    <tbody>
        @foreach($facturas as $f)
            <tr>
                <td>{{ $f->proveedor->nombre ?? '-' }}</td>
                <td>{{ $f->oficio->nombre ?? '-' }}</td>
                <td>{{ $f->concepto }}</td>
                <td>{{ $f->fecha_factura->format('d/m/Y') }}</td>
                <td>{{ $f->base_imponible }}</td>
                <td>{{ $f->iva_importe }}</td>
                <td>{{ $f->retencion_importe }}</td>
                <td>{{ $f->total }}</td>
                <td>{{ $f->estado }}</td>
            </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr></tr>
        <tr>
            <td colspan="4"><strong>TOTALES</strong></td>
            <td><strong>{{ $totales['base'] }}</strong></td>
            <td><strong>{{ $totales['iva'] }}</strong></td>
            <td><strong>{{ $totales['retencion'] }}</strong></td>
            <td><strong>{{ $totales['total'] }}</strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>
