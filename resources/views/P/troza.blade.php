<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Troza #{{ $troza->id_troza }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { height: 80px; }
        .title { font-size: 24px; font-weight: bold; color: #5D4037; }
        .subtitle { font-size: 14px; color: #8D6E63; }
        .date { font-size: 12px; color: #8D6E63; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #558B2F; color: white; text-align: left; padding: 8px; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #8D6E63; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; border: none; }
    </style>
</head>
<body>
<div class="header">
    @if(file_exists($logo))
        <img src="{{ $logo }}" class="logo">
    @endif
    <div class="title">Reporte de Troza</div>
    <div class="subtitle">Generado el: {{ $fecha }}</div>
</div>

<table class="info-table">
    <tr>
        <td><strong>Número de Troza:</strong></td>
        <td>#{{ $troza->id_troza }}</td>
    </tr>
    <tr>
        <td><strong>Parcela:</strong></td>
        <td>{{ $troza->parcela->nom_parcela }}</td>
    </tr>
    <tr>
        <td><strong>Fecha de Corte:</strong></td>
        <td>{{ date('d/m/Y', strtotime($troza->fecha_corte)) }}</td>
    </tr>
    <tr>
        <td><strong>Especie:</strong></td>
        <td>{{ $troza->especie }}</td>
    </tr>
</table>

@if($troza->estimacion)
    <h3>Datos de Estimación</h3>
    <table>
        <thead>
        <tr>
            <th>Fecha Estimación</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ date('d/m/Y', strtotime($troza->estimacion->fecha_estimacion)) }}</td>
            <td>{{ $troza->estimacion->peso_estimado }}</td>
            <td>{{ $troza->estimacion->calidad }}</td>
        </tr>
        </tbody>
    </table>
@else
    <p>Esta troza no tiene estimación registrada.</p>
@endif

<div class="footer">
    © {{ date('Y') }} WoodWise - Sistema de Gestión Forestal
</div>
</body>
</html>
