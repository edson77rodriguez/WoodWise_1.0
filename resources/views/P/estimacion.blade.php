<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Estimación #{{ $estimacion->id_estimacion }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header img { max-height: 80px; }
        h1 { color: #2d4a08; text-align: center; }
        .fecha { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
<div class="header">
    @if(file_exists($logo))
        <img src="{{ $logo }}" alt="Logo">
    @endif
    <h1>Reporte de Estimación</h1>
    <div class="fecha">Generado el: {{ $fecha }}</div>
</div>

<h2>Información de la Estimación</h2>
<table>
    <tr>
        <th>ID Estimación</th>
        <td>{{ $estimacion->id_estimacion }}</td>
    </tr>
    <tr>
        <th>Fecha</th>
        <td>{{ $estimacion->fecha_estimacion ?? $estimacion->created_at->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <th>Volumen Estimado</th>
        <td>{{ $estimacion->volumen_estimado }} m³</td>
    </tr>
    <tr>
        <th>Calidad</th>
        <td>{{ $estimacion->calidad }}</td>
    </tr>
</table>

<h2>Información de la Troza</h2>
<table>
    <tr>
        <th>ID Troza</th>
        <td>{{ $estimacion->troza->id_troza }}</td>
    </tr>
    <tr>
        <th>Longitud</th>
        <td>{{ $estimacion->troza->longitud }} m</td>
    </tr>
    <tr>
        <th>Diámetro</th>
        <td>{{ $estimacion->troza->diametro }} cm</td>
    </tr>
    <tr>
        <th>Densidad</th>
        <td>{{ $estimacion->troza->densidad }} kg/m³</td>
    </tr>
</table>

<h2>Información de la Parcela</h2>
<table>
    <tr>
        <th>Nombre</th>
        <td>{{ $estimacion->troza->parcela->nom_parcela }}</td>
    </tr>
    <tr>
        <th>Ubicación</th>
        <td>{{ $estimacion->troza->parcela->ubicacion }}</td>
    </tr>
    <tr>
        <th>Extensión</th>
        <td>{{ $estimacion->troza->parcela->extension }} ha</td>
    </tr>
</table>
</body>
</html>
