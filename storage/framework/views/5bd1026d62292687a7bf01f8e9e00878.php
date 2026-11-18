<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Estimación #<?php echo e($estimacion->id_estimacion); ?></title>
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
    <?php if(file_exists($logo)): ?>
        <img src="<?php echo e($logo); ?>" alt="Logo">
    <?php endif; ?>
    <h1>Reporte de Estimación</h1>
    <div class="fecha">Generado el: <?php echo e($fecha); ?></div>
</div>

<h2>Información de la Estimación</h2>
<table>
    <tr>
        <th>ID Estimación</th>
        <td><?php echo e($estimacion->id_estimacion); ?></td>
    </tr>
    <tr>
        <th>Fecha</th>
        <td><?php echo e($estimacion->fecha_estimacion ?? $estimacion->created_at->format('d/m/Y')); ?></td>
    </tr>
    <tr>
        <th>Volumen Estimado</th>
        <td><?php echo e($estimacion->volumen_estimado); ?> m³</td>
    </tr>
    <tr>
        <th>Calidad</th>
        <td><?php echo e($estimacion->calidad); ?></td>
    </tr>
</table>

<h2>Información de la Troza</h2>
<table>
    <tr>
        <th>ID Troza</th>
        <td><?php echo e($estimacion->troza->id_troza); ?></td>
    </tr>
    <tr>
        <th>Longitud</th>
        <td><?php echo e($estimacion->troza->longitud); ?> m</td>
    </tr>
    <tr>
        <th>Diámetro</th>
        <td><?php echo e($estimacion->troza->diametro); ?> m</td>
    </tr>
    <tr>
        <th>Densidad</th>
        <td><?php echo e($estimacion->troza->densidad); ?> ton/m³</td>
    </tr>
</table>

<h2>Información de la Parcela</h2>
<table>
    <tr>
        <th>Nombre</th>
        <td><?php echo e($estimacion->troza->parcela->nom_parcela); ?></td>
    </tr>
    <tr>
        <th>Ubicación</th>
        <td><?php echo e($estimacion->troza->parcela->ubicacion); ?></td>
    </tr>
    <tr>
        <th>Extensión</th>
        <td><?php echo e($estimacion->troza->parcela->extension); ?> ha</td>
    </tr>
</table>
</body>
</html>
<?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/P/estimacion.blade.php ENDPATH**/ ?>