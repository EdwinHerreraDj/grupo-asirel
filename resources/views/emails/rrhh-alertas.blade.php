<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Alertas de Recursos humanos</title>
</head>
<body style="margin:0;padding:24px;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
        <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;">
            <p style="margin:0;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#0891b2;font-weight:bold;">Recursos humanos</p>
            <h1 style="margin:6px 0 0;font-size:20px;">Alertas de hoy</h1>
            <p style="margin:6px 0 0;font-size:14px;color:#475569;">
                {{ $totales['critico'] }} urgentes · {{ $totales['aviso'] }} avisos · {{ $totales['info'] }} informativas
            </p>
        </div>

        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
            @foreach ($alertas as $a)
                @php
                    $color = ['critico' => '#e11d48', 'aviso' => '#d97706', 'info' => '#0284c7'][$a['nivel']];
                @endphp
                <tr>
                    <td style="padding:12px 24px;border-bottom:1px solid #f1f5f9;border-left:4px solid {{ $color }};">
                        <p style="margin:0;font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#64748b;">
                            {{ $categorias[$a['categoria']] ?? $a['categoria'] }}
                        </p>
                        <p style="margin:2px 0 0;font-size:14px;font-weight:bold;">
                            {{ $a['titulo'] }}@if ($a['empleado']) · {{ $a['empleado']['nombre_completo'] }}@endif
                        </p>
                        <p style="margin:2px 0 0;font-size:13px;color:#475569;">{{ $a['detalle'] }}</p>
                    </td>
                </tr>
            @endforeach
        </table>

        <div style="padding:20px 24px;">
            <a href="{{ $url }}" style="display:inline-block;background:#0891b2;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:10px;font-size:14px;font-weight:bold;">
                Abrir Recursos humanos
            </a>
        </div>
    </div>
</body>
</html>
