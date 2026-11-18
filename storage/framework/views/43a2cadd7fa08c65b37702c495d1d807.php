<?php $__env->startPush('styles'); ?>
    <link href="<?php echo e(asset('css/WW/theme.css')); ?>" rel="stylesheet">
    <style>
        /* Estilos para la lista de estimaciones */
        .estimaciones-container {
            max-width: 300px;
        }
        .estimaciones-list {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 0.5rem;
            margin-top: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .estimaciones-list .list-group-item {
            background-color: rgba(255,255,255,0.9);
            border-left: 3px solid var(--emerald-500);
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .estimaciones-list .list-group-item:hover {
            background-color: var(--emerald-50);
            transform: translateX(4px);
        }
        
        /* Ajustes adicionales para el tema moderno */
        .parcela-show-container {
            background: linear-gradient(135deg, var(--forest-50) 0%, var(--forest-100) 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .parcela-stat-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
        }
        
        /* Ajustes para el contenedor del tab */
        .parcela-tab-content {
            padding: 2rem;
            background: var(--glass-bg);
            border-radius: 0 0 1rem 1rem;
        }
        
        .parcela-action-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            background: var(--emerald-50);
            color: var(--emerald-700);
            transition: all 0.3s ease;
            margin: 0 0.25rem;
        }
        
        .parcela-action-btn:hover {
            background: var(--emerald-100);
            transform: translateY(-2px);
        }
        
        /* Estilos para los badges y estados */
        .parcela-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .parcela-badge-success {
            background: var(--emerald-100);
            color: var(--emerald-700);
        }
        
        .parcela-badge-warning {
            background: #FEF3C7;
            color: #92400E;
        }
        
        .parcela-badge-info {
            background: #DBEAFE;
            color: #1E40AF;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="parcela-show-container">
    <div class="parcela-header-modern">
        <div class="parcela-header-content">
            <div class="parcela-title-section">
                <div class="parcela-icon-hero">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="parcela-title-text">
                    <h1><?php echo e($parcela->nom_parcela); ?></h1>
                    <p>Gestión detallada de recursos forestales</p>
                </div>
            </div>
            <div class="parcela-header-actions">
                <a href="<?php echo e(route('tecnico.dashboard')); ?>" class="parcela-btn-light">
                    <i class="fas fa-arrow-left me-2"></i> Regresar
                </a>
                <button class="parcela-btn-primary" data-bs-toggle="modal" data-bs-target="#addArbolModal">
                    <i class="fas fa-tree me-2"></i> Nuevo Árbol
                </button>
            </div>
        </div>
    </div>

    <div class="parcela-stats-grid">
            
            <!-- Stats Cards -->
            <div class="parcela-stat-card">
                <div class="parcela-stat-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="parcela-stat-value"><?php echo e($parcela->ubicacion); ?></div>
                <div class="parcela-stat-label">Ubicación</div>
            </div>
            
            <div class="parcela-stat-card">
                <div class="parcela-stat-icon">
                    <i class="fas fa-ruler-combined"></i>
                </div>
                <div class="parcela-stat-value"><?php echo e($parcela->extension); ?></div>
                <div class="parcela-stat-label">Área</div>
                <div class="parcela-stat-unit">hectáreas</div>
            </div>
            
            <div class="parcela-stat-card">
                <div class="parcela-stat-icon">
                    <i class="fas fa-tree"></i>
                </div>
                <div class="parcela-stat-value"><?php echo e($parcela->arboles_count ?? 0); ?></div>
                <div class="parcela-stat-label">Árboles</div>
                <div class="parcela-stat-unit">registrados</div>
            </div>

            <div class="parcela-stat-card">
                <div class="parcela-stat-icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="parcela-stat-value"><?php echo e(number_format($parcela->volumen_maderable_sum_calculo ?? 0, 4)); ?></div>
                <div class="parcela-stat-label">Volumen Total</div>
                <div class="parcela-stat-unit">metros cúbicos</div>
            </div>
            
            <!-- Tabs -->
            <div class="parcela-tabs-modern">
                <div class="parcela-tabs-header">
                    <div class="parcela-tabs-nav" role="tablist">
                        <button class="parcela-tab-btn active" id="arboles-tab" data-bs-toggle="tab" data-bs-target="#arboles" type="button" role="tab">
                            <i class="fas fa-tree"></i>
                            <span>Árboles</span>
                            <span class="parcela-tab-badge"><?php echo e($parcela->arboles_count ?? 0); ?></span>
                        </button>
                        <button class="parcela-tab-btn" id="trozas-tab" data-bs-toggle="tab" data-bs-target="#trozas" type="button" role="tab">
                            <i class="fas fa-cut"></i>
                            <span>Trozas</span>
                            <span class="parcela-tab-badge"><?php echo e($parcela->trozas_count); ?></span>
                        </button>
                        <button class="parcela-tab-btn" id="estimaciones-tab" data-bs-toggle="tab" data-bs-target="#estimaciones" type="button" role="tab">
                            <i class="fas fa-calculator"></i>
                            <span>Estimaciones</span>
                            <span class="parcela-tab-badge"><?php echo e($parcela->estimaciones_count); ?></span>
                        </button>
                        <button class="parcela-tab-btn" id="turnos-tab" data-bs-toggle="tab" data-bs-target="#turnos" type="button" role="tab">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Turnos</span>
                            <span class="parcela-tab-badge"><?php echo e($parcela->turnos_corta_count); ?></span>
                        </button>
                    </div>
                </div>

            <div class="tab-content wood-tab-content w-100" id="parcelaTabsContent">
                
                <!-- Tab de Árboles -->
                <div class="tab-pane fade show active w-100" id="arboles" role="tabpanel">
                    <?php if($parcela->arboles && $parcela->arboles->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="parcela-table-modern wood-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Especie</th>
                                    <th>Altura Total</th>
                                    <th>DAP</th>
                                    <th>Estado</th>
                                    <th>Estimaciones de Volumen</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $parcela->arboles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arbol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-4">#<?php echo e($arbol->id_arbol); ?></td>
                                    <td>
                                        <span class="text-forest-dark"><?php echo e($arbol->especie->nom_cientifico ?? 'Sin especie'); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo e(number_format($arbol->altura_total, 2)); ?> m</span>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo e(number_format($arbol->diametro_pecho, 2)); ?> m</span>
                                    </td>
                                    <td>
                                        <span class="wood-badge <?php echo e($arbol->activo ? 'wood-badge-success' : 'wood-badge-secondary'); ?>">
                                            <?php echo e($arbol->activo ? 'Activo' : 'Inactivo'); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($arbol->estimaciones1->count() > 0): ?>
                                        <div class="estimaciones-container">
                                            <span class="wood-badge bg-success mb-2">
                                                <?php echo e($arbol->estimaciones1->count()); ?> estimaciones
                                            </span>
                                            <div class="list-group estimaciones-list">
                                                <?php $__currentLoopData = $arbol->estimaciones1; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estimacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="list-group-item p-2">
                                                    <small class="d-flex justify-content-between align-items-center">
                                                        <span class="text-forest">
                                                            <i class="fas fa-calculator me-1"></i>
                                                            <?php echo e($estimacion->tipoEstimacion->nom_tipo_e); ?>

                                                        </span>
                                                        <span class="text-muted">
                                                            <?php echo e(number_format($estimacion->calculo, 4)); ?> m³
                                                        </span>
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        Fórmula: <?php echo e($estimacion->formula->nom_formula); ?>

                                                    </small>
                                                </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <span class="wood-badge bg-secondary">
                                            Sin estimaciones
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                                            <button class="btn btn-wood-action btn-view" data-bs-toggle="modal" data-bs-target="#arbolModal<?php echo e($arbol->id_arbol); ?>">
                                                <i class="fas fa-eye me-1"></i> Detalles
                                            </button>
                                            <button class="btn btn-wood-action btn-edit" data-bs-toggle="modal" data-bs-target="#editArbolModal<?php echo e($arbol->id_arbol); ?>">
                                                <i class="fas fa-edit me-1"></i> Editar
                                            </button>
                                            <button class="btn btn-wood-action btn-info" data-bs-toggle="modal" data-bs-target="#estimacionArbolModal<?php echo e($arbol->id_arbol); ?>">
                                                <i class="fas fa-calculator me-1"></i> Estimar
                                            </button>
                                            <form action="<?php echo e(route('arboles.destroy', $arbol->id_arbol)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este árbol?');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-wood-action btn-delete">
                                                    <i class="fas fa-trash me-1"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-tree fa-4x text-forest-light mb-4"></i>
                        <h5 class="text-forest-medium mb-3">No hay árboles registrados</h5>
                        <button class="btn btn-wood rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addArbolModal">
                            <i class="fas fa-plus me-2"></i> Agregar Árbol
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tab de Trozas (existente) -->
                <div class="tab-pane fade" id="trozas" role="tabpanel">
                    <?php if($parcela->trozas->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="wood-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Especie</th>
                                    <th>Medidas</th>
                                    <th>Volumen</th>
                                    <th>Densidad</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $parcela->trozas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $troza): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-4"><?php echo e($troza->id_troza); ?></td>
                                    <td>
                                        <span class="text-forest-dark"><?php echo e($troza->especie->nom_cientifico); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo e(number_format($troza->longitud, 2)); ?>m × <?php echo e(number_format($troza->diametro, 2)); ?>m</span>
                                    </td>
                                    <td>
                                        <span class="text-forest-accent fw-bold">
                                            <?php echo e($troza->estimacion ? number_format($troza->estimacion->calculo, 4) : 'N/A'); ?> m³
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo e(number_format($troza->densidad, 2)); ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                                            <button class="btn btn-wood-action btn-view" data-bs-toggle="modal" data-bs-target="#trozaModal<?php echo e($troza->id_troza); ?>">
                                                <i class="fas fa-eye me-1"></i> Detalles
                                            </button>
                                            <button class="btn btn-wood-action btn-edit" data-bs-toggle="modal" data-bs-target="#editTrozaModal<?php echo e($troza->id_troza); ?>">
                                                <i class="fas fa-edit me-1"></i> Editar
                                            </button>
                                            <form action="<?php echo e(route('trozas.destroy', $troza->id_troza)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar esta troza?');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-wood-action btn-delete">
                                                    <i class="fas fa-trash me-1"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-tree fa-4x text-forest-light mb-4"></i>
                        <h5 class="text-forest-medium mb-3">No hay trozas registradas</h5>
                        <button class="btn btn-wood rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addTrozaModal">
                            <i class="fas fa-plus me-2"></i> Agregar Troza
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tab de Estimaciones Mejorada -->
                <div class="tab-pane fade" id="estimaciones" role="tabpanel">
                    <?php if($parcela->estimaciones->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="wood-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Tipo</th>
                                    <th>Fórmula</th>
                                    <th>Elemento</th>
                                    <th>Resultado</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $parcela->estimaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estimacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-4"><?php echo e($estimacion->id_estimacion); ?></td>
                                    <td><?php echo e($estimacion->tipoEstimacion->desc_estimacion); ?></td>
                                    <td><?php echo e($estimacion->formula->nom_formula); ?></td>
                                    <td>
                                        <?php if($estimacion->id_troza): ?>
                                            <span class="wood-badge bg-warning">Troza #<?php echo e($estimacion->troza->id_troza); ?></span>
                                        <?php elseif($estimacion->id_arbol): ?>
                                            <span class="wood-badge bg-success">Árbol #<?php echo e($estimacion->arbol->id_arbol); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-forest-accent fw-bold"><?php echo e(number_format($estimacion->calculo, 4)); ?> m³</td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                                            <button class="btn btn-wood-action btn-view" data-bs-toggle="modal" data-bs-target="#estimacionModal<?php echo e($estimacion->id_estimacion); ?>">
                                                <i class="fas fa-eye me-1"></i> Detalles
                                            </button>
                                            <button class="btn btn-wood-action btn-edit" data-bs-toggle="modal" data-bs-target="#editEstimacionModal<?php echo e($estimacion->id_estimacion); ?>">
                                                <i class="fas fa-edit me-1"></i> Editar
                                            </button>
                                            <form action="<?php echo e(route('estimaciones.destroy', $estimacion->id_estimacion)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar esta estimación?');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-wood-action btn-delete">
                                                    <i class="fas fa-trash me-1"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calculator fa-4x text-forest-light mb-4"></i>
                        <h5 class="text-forest-medium mb-3">No hay estimaciones registradas</h5>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tab de Turnos (existente) -->
                <div class="tab-pane fade" id="turnos" role="tabpanel">
                    <?php if($parcela->turnosCorta->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="wood-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">Código</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $parcela->turnosCorta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $turno): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-4"><?php echo e($turno->codigo_corta); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($turno->fecha_corta)->format('d/m/Y')); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($turno->completado ? 'bg-forest-accent text-dark' : 'bg-forest-medium text-white'); ?>">
                                            <?php echo e($turno->completado ? 'Completado' : 'Pendiente'); ?>

                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-wood-action btn-view" data-bs-toggle="modal" data-bs-target="#turnoModal<?php echo e($turno->id_turno); ?>">
                                            <i class="fas fa-eye me-1"></i> Detalles
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-alt fa-4x text-forest-light mb-4"></i>
                        <h5 class="text-forest-medium mb-3">No hay turnos programados</h5>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Árbol -->
<div class="modal fade wood-modal" id="addArbolModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 wood-modal-content">
            <div class="modal-header wood-modal-header wood-bg-success text-white">
                <div class="d-flex align-items-center">
                    <div class="wood-modal-icon me-3"><i class="fas fa-tree"></i></div>
                    <div>
                        <h5 class="modal-title wood-modal-title">Nuevo Árbol</h5>
                        <p class="wood-modal-subtitle mb-0">Registrar nuevo árbol en la parcela</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 wood-modal-body">
                <form method="POST" action="<?php echo e(route('arboles.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id_parcela" value="<?php echo e($parcela->id_parcela); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="wood-form-group">
                                <label class="wood-form-label">Altura Total (m)</label>
                                <input type="number" step="0.1" name="altura_total" class="wood-form-control" required min="0.1" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wood-form-group">
                                <label class="wood-form-label">Diámetro a la Altura del Pecho (m)</label>
                                <input type="number" step="0.01" name="diametro_pecho" class="wood-form-control" required min="0.01" max="5">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="wood-form-group">
                                <label class="wood-form-label">Especie</label>
                                <select class="wood-form-select" name="id_especie" required>
                                    <option value="" selected disabled>Seleccione una especie</option>
                                    <?php $__currentLoopData = $especies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $especie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($especie->id_especie); ?>"><?php echo e($especie->nom_cientifico); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="wood-form-group">
                                <label class="wood-form-label">Observaciones</label>
                                <textarea name="observaciones" class="wood-form-control" rows="3" placeholder="Observaciones adicionales..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="wood-modal-footer mt-4">
                        <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-wood-success">
                            <i class="fas fa-save me-1"></i> Registrar Árbol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modales para Árboles -->
<?php $__currentLoopData = $parcela->arboles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arbol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <!-- Modal Detalles Árbol -->
    <div class="modal fade wood-detail-modal" id="arbolModal<?php echo e($arbol->id_arbol); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content wood-detail-card">
                <div class="modal-header wood-detail-header wood-bg-success">
                    <div class="d-flex align-items-center w-100">
                        <div class="wood-detail-icon"><i class="fas fa-tree"></i></div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="wood-detail-title mb-0">Detalles del Árbol</h5>
                            <p class="wood-detail-subtitle mb-0">#<?php echo e($arbol->id_arbol); ?> - <?php echo e($arbol->especie->nom_cientifico ?? 'Sin especie'); ?></p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="wood-detail-section">
                                <h6 class="wood-detail-section-title"><i class="fas fa-ruler-combined me-2"></i>Medidas</h6>
                                <div class="wood-detail-list">
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Altura Total:</span>
                                        <span class="wood-detail-value"><?php echo e($arbol->altura_total); ?> m</span>
                                    </div>
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">DAP:</span>
                                        <span class="wood-detail-value"><?php echo e($arbol->diametro_pecho); ?> m</span>
                                    </div>
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Estado:</span>
                                        <span class="wood-detail-badge <?php echo e($arbol->activo ? 'wood-badge-success' : 'wood-badge-secondary'); ?>">
                                            <?php echo e($arbol->activo ? 'Activo' : 'Inactivo'); ?>

                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wood-detail-section">
                                <h6 class="wood-detail-section-title"><i class="fas fa-info-circle me-2"></i>Información</h6>
                                <div class="wood-detail-list">
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Especie:</span>
                                        <span class="wood-detail-value"><?php echo e($arbol->especie->nom_cientifico ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Fecha registro:</span>
                                        <span class="wood-detail-value"><?php echo e($arbol->created_at->format('d/m/Y')); ?></span>
                                    </div>
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Estimaciones:</span>
                                        <span class="wood-detail-value"><?php echo e($arbol->estimaciones_count ?? 0); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if($arbol->observaciones): ?>
                    <div class="wood-detail-section mt-3">
                        <h6 class="wood-detail-section-title"><i class="fas fa-sticky-note me-2"></i>Observaciones</h6>
                        <p class="wood-detail-text"><?php echo e($arbol->observaciones); ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="wood-detail-footer mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Árbol -->
    <div class="modal fade wood-edit-modal" id="editArbolModal<?php echo e($arbol->id_arbol); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content wood-edit-card">
                <div class="modal-header wood-edit-header wood-bg-success">
                    <div class="d-flex align-items-center w-100">
                        <div class="wood-edit-icon"><i class="fas fa-tree"></i></div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="wood-edit-title mb-0">Editar Árbol</h5>
                            <p class="wood-edit-subtitle mb-0">#<?php echo e($arbol->id_arbol); ?></p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="<?php echo e(route('arboles.update', $arbol->id_arbol)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <input type="hidden" name="id_parcela" value="<?php echo e($parcela->id_parcela); ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Altura Total (m)</label>
                                    <input type="number" step="0.1" class="wood-form-control" name="altura_total" value="<?php echo e($arbol->altura_total); ?>" required min="0.1" max="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">DAP (m)</label>
                                    <input type="number" step="0.01" class="wood-form-control" name="diametro_pecho" value="<?php echo e($arbol->diametro_pecho); ?>" required min="0.01" max="5">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Especie</label>
                                    <select class="wood-form-select" name="id_especie" required>
                                        <?php $__currentLoopData = $especies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $especie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($especie->id_especie); ?>" <?php echo e($arbol->id_especie == $especie->id_especie ? 'selected' : ''); ?>>
                                                <?php echo e($especie->nom_cientifico); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Estado</label>
                                    <select class="wood-form-select" name="activo" required>
                                        <option value="1" <?php echo e($arbol->activo ? 'selected' : ''); ?>>Activo</option>
                                        <option value="0" <?php echo e(!$arbol->activo ? 'selected' : ''); ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Observaciones</label>
                                    <textarea name="observaciones" class="wood-form-control" rows="3"><?php echo e($arbol->observaciones); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wood-edit-footer mt-4">
                            <button type="button" class="btn btn-wood-outline me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-wood-success">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Estimación Árbol -->
    <div class="modal fade wood-modal" id="estimacionArbolModal<?php echo e($arbol->id_arbol); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 wood-modal-content">
                <div class="modal-header wood-modal-header wood-bg-warning">
                    <div class="d-flex align-items-center">
                        <div class="wood-modal-icon me-3"><i class="fas fa-calculator"></i></div>
                        <div>
                            <h5 class="modal-title wood-modal-title text-dark">Estimación para Árbol</h5>
                            <p class="wood-modal-subtitle mb-0 text-dark">#<?php echo e($arbol->id_arbol); ?> - <?php echo e($arbol->especie->nom_cientifico ?? 'Sin especie'); ?></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 wood-modal-body">
                    <form method="POST" action="<?php echo e(route('estimaciones.arbol.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id_parcela" value="<?php echo e($parcela->id_parcela); ?>">
                        <input type="hidden" name="id_arbol" value="<?php echo e($arbol->id_arbol); ?>">
                        
                        <div class="mb-3">
                            <label class="wood-form-label">Árbol Seleccionado</label>
                            <div class="wood-info-box">
                                <strong>#<?php echo e($arbol->id_arbol); ?></strong> - <?php echo e($arbol->especie->nom_cientifico ?? 'Sin especie'); ?>

                                <br>
                                <small class="text-muted">Altura: <?php echo e($arbol->altura_total); ?>m | DAP: <?php echo e($arbol->diametro_pecho); ?>m</small>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Tipo de Estimación</label>
                                    <select class="wood-form-select" name="id_tipo_e" required>
                                        <option value="" selected disabled>Seleccione un tipo</option>
                                        <?php $__currentLoopData = $tiposEstimacion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($tipo->id_tipo_e); ?>"><?php echo e($tipo->desc_estimacion); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Fórmula</label>
                                    <select class="wood-form-select" name="id_formula" required>
                                        <option value="" selected disabled>Seleccione una fórmula</option>
                                        <?php $__currentLoopData = $formulas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($formula->id_formula); ?>"><?php echo e($formula->nom_formula); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <div class="wood-form-group">
                                <label class="wood-form-label">Cálculo (m³)</label>
                                <input type="number" step="0.0001" class="wood-form-control" name="calculo" required min="0" placeholder="0.0000">
                            </div>
                        </div>
                        
                        <div class="wood-modal-footer mt-4">
                            <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-wood-warning">
                                <i class="fas fa-calculator me-1"></i> Crear Estimación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<!-- Los modales existentes para trozas, estimaciones y turnos se mantienen igual -->
<!-- ... (código existente para modales de trozas, estimaciones y turnos) ... -->

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Auto-calcular volumen basado en medidas del árbol (ejemplo básico)
        document.querySelectorAll('[id^="estimacionArbolModal"]').forEach(modal => {
            modal.addEventListener('show.bs.modal', function() {
                // Aquí puedes agregar lógica de cálculo automático si tienes fórmulas
                console.log('Modal de estimación de árbol abierto');
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/parcelas/show.blade.php ENDPATH**/ ?>