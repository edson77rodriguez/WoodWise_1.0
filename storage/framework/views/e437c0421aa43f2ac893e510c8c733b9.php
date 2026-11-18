<!DOCTYPE html>
<html>
<head>
    <title><?php echo e($titulo); ?></title>
    <style>
        @page {
            margin: 50px 40px;
            size: landscape;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.5;
            background-color: #ffffff;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4a7c59;
        }
        
        .logo-container {
            flex: 1;
        }
        
        .logo {
            max-height: 70px;
        }
        
        .title-container {
            flex: 2;
            text-align: center;
        }
        
        h1 {
            color: #2d4a08;
            font-size: 24px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .subtitle {
            color: #6a8c3d;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .fecha {
            flex: 1;
            text-align: right;
            color: #666;
            font-size: 12px;
        }
        
        .resumen {
            background: linear-gradient(to right, #f8f9f2, #e8efe0);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .resumen h2 {
            color: #4a7c59;
            margin-top: 0;
            font-size: 18px;
            border-bottom: 1px solid #cbd8bf;
            padding-bottom: 8px;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            flex: 1;
            margin: 0 10px;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #4a7c59;
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .parcela-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .parcela-header {
            background-color: #4a7c59;
            color: white;
            padding: 12px 15px;
            border-radius: 5px 5px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .parcela-name {
            font-size: 16px;
            font-weight: bold;
        }
        
        .parcela-details {
            background-color: #f9fbf7;
            padding: 15px;
            border: 1px solid #e0e6d6;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: bold;
            width: 100px;
            color: #555;
        }
        
        .trozas-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 12px;
        }
        
        .trozas-table th {
            background-color: #e8efe0;
            color: #4a7c59;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #cbd8bf;
        }
        
        .trozas-table td {
            padding: 10px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .trozas-table tr:nth-child(even) {
            background-color: #f9fbf7;
        }
        
        .trozas-table tr:hover {
            background-color: #f0f5e9;
        }
        
        .no-data {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 20px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-secondary {
            background-color: #f5f5f5;
            color: #757575;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="<?php echo e($logo); ?>" alt="Logo" class="logo">
        </div>
        <div class="title-container">
            <h1><?php echo e($titulo); ?></h1>
            <div class="subtitle">Reporte detallado de parcelas y producción maderable</div>
        </div>
        <div class="fecha">
            Generado el: <?php echo e($fecha); ?>

        </div>
    </div>

    <div class="resumen">
        <h2>Resumen General</h2>
        <div class="stats-container">
            <div class="stat-item">
                <span class="stat-number"><?php echo e($stats['total_parcelas']); ?></span>
                <span class="stat-label">Parcelas Registradas</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo e($stats['total_trozas']); ?></span>
                <span class="stat-label">Total de Trozas</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo e($stats['total_estimaciones']); ?></span>
                <span class="stat-label">Estimaciones Realizadas</span>
            </div>
        </div>
    </div>

    <?php $__currentLoopData = $parcelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parcela): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="parcela-section">
            <div class="parcela-header">
                <div class="parcela-name">Parcela: <?php echo e($parcela->nom_parcela); ?></div>
                <div class="parcela-counts">
                    <span class="badge badge-success"><?php echo e($parcela->trozas_count); ?> Trozas</span>
                    <span class="badge badge-secondary"><?php echo e($parcela->estimaciones_count); ?> Estimaciones</span>
                </div>
            </div>
            
            <div class="parcela-details">
                <div class="detail-row">
                    <span class="detail-label">Ubicación:</span>
                    <span><?php echo e($parcela->ubicacion); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Extensión:</span>
                    <span><?php echo e($parcela->hectareas); ?> ha</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tipo de suelo:</span>
                    <span><?php echo e($parcela->tipo_suelo); ?></span>
                </div>
                
                <?php if($parcela->trozas->count() > 0): ?>
                    <h3 style="margin-top: 20px; color: #4a7c59;">Detalle de Trozas</h3>
                    <table class="trozas-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Longitud (m)</th>
                                <th>Diámetro (m)</th>
                                <th>Densidad (ton/m³)</th>
                                <th>Estimación</th>
                                <th>Volumen (m³)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $parcela->trozas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $troza): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($troza->codigo_troza); ?></td>
                                    <td><?php echo e(number_format($troza->longitud, 2)); ?></td>
                                    <td><?php echo e(number_format($troza->diametro, 1)); ?></td>
                                    <td><?php echo e(number_format($troza->densidad, 0)); ?></td>
                                    <td>
                                        <?php if($troza->estimacion): ?>
                                            <span class="badge badge-success">Realizada</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($troza->estimacion): ?>
                                            <?php echo e(number_format($troza->estimacion->calculo, 3)); ?>

                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        No hay trozas registradas en esta parcela
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(!$loop->last): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <div class="footer">
        Reporte generado por SIGMAD - Sistema Inteligente de Gestión Maderable | Página <?php echo e($loop->iteration); ?> de <?php echo e($loop->count); ?>

    </div>
</body>
</html><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/P/pdf_export_general.blade.php ENDPATH**/ ?>