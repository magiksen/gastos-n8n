<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; padding: 20px; color: #1f2937; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #1f2937; color: #fff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .content { padding: 24px; }
        .summary { display: flex; gap: 12px; margin-bottom: 24px; }
        .stat { flex: 1; background: #f9fafb; border-radius: 6px; padding: 12px; text-align: center; }
        .stat .label { font-size: 12px; color: #6b7280; text-transform: uppercase; }
        .stat .value { font-size: 20px; font-weight: 700; margin-top: 4px; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; }
        .alert-red { background: #fef2f2; border: 1px solid #fecaca; }
        .alert-yellow { background: #fffbeb; border: 1px solid #fde68a; }
        .alert h3 { margin: 0 0 8px 0; font-size: 14px; }
        .alert-red h3 { color: #991b1b; }
        .alert-yellow h3 { color: #92400e; }
        .item { font-size: 14px; padding: 4px 0; }
        .alert-red .item { color: #b91c1c; }
        .alert-yellow .item { color: #b45309; }
        .footer { padding: 16px 24px; background: #f9fafb; text-align: center; font-size: 12px; color: #9ca3af; }
        table { width: 100%; }
        td { padding: 4px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Resumen de Gastos — {{ str_pad((string)$mes, 2, '0', STR_PAD_LEFT) }}/{{ $anio }}</h1>
        </div>

        <div class="content">
            <table style="width:100%; margin-bottom: 24px;">
                <tr>
                    <td style="text-align:center; background:#f9fafb; border-radius:6px; padding:12px;">
                        <div style="font-size:12px; color:#6b7280; text-transform:uppercase;">Pendiente</div>
                        <div style="font-size:20px; font-weight:700; color:#dc2626; margin-top:4px;">{{ $totalPendienteUsd > 0 ? '$' . number_format($totalPendienteUsd, 2) : '' }}{{ $totalPendienteUsd > 0 && $totalPendienteBs > 0 ? ' + ' : '' }}{{ $totalPendienteBs > 0 ? 'Bs ' . number_format($totalPendienteBs, 2) : '' }}</div>
                    </td>
                </tr>
            </table>

            @if($vencidos->count() > 0)
                <div class="alert alert-red">
                    <h3>Pagos Vencidos ({{ $vencidos->count() }})</h3>
                    @foreach($vencidos as $gm)
                        <div class="item">
                            <strong>{{ $gm->gasto->servicio }}</strong>
                            — Dia {{ $gm->gasto->dia_pago }}
                            @if($gm->gasto->monto) — {{ ($gm->gasto->moneda ?? 'USD') === 'VES' ? 'Bs' : '$' }} {{ number_format((float)$gm->gasto->monto, 2) }} @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if($proximos->count() > 0)
                <div class="alert alert-yellow">
                    <h3>Pagos Proximos — 3 dias ({{ $proximos->count() }})</h3>
                    @foreach($proximos as $gm)
                        <div class="item">
                            <strong>{{ $gm->gasto->servicio }}</strong>
                            — Dia {{ $gm->gasto->dia_pago }}
                            @if($gm->gasto->monto) — {{ ($gm->gasto->moneda ?? 'USD') === 'VES' ? 'Bs' : '$' }} {{ number_format((float)$gm->gasto->monto, 2) }} @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if($vencidos->count() === 0 && $proximos->count() === 0)
                <p style="text-align:center; color:#059669; font-size:16px;">Todo al dia. No hay pagos pendientes ni proximos.</p>
            @endif
        </div>

        <div class="footer">
            Gastos App — {{ config('app.url') }}
        </div>
    </div>
</body>
</html>
