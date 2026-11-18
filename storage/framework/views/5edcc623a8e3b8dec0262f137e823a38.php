

<div class="row mb-4">
    <div class="col-12">
        <div class="card welcome-card shadow-lg overflow-hidden">
            <div class="position-absolute top-0 end-0 opacity-25">
                <i class="fas fa-tree fa-10x text-white" style="transform: rotate(-15deg) translate(20px, 30px);"></i>
            </div>
            <div class="card-body p-4 position-relative">
                <div class="row align-items-center">
                    <div class="col-md-8 z-index-1">
                        
                        <h2 class="text-white mb-2">
                            <i class="fas fa-tachometer-alt me-2"></i> Panel de <?php echo e(Auth::user()->persona->rol->nom_rol); ?>

                        </h2>
                        <p class="text-white fw-bold h5 mb-3">
                            Bienvenido, <?php echo e(Auth::user()->persona->nom); ?>

                        </p>
                        <p class="text-white opacity-75 mb-0">
                            Desde aquí puedes gestionar usuarios, parcelas, especies y las operaciones del sistema.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-xl-0 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-sm mb-0 text-uppercase fw-bold opacity-7">Usuarios del Sistema</p>
                    <h2 class="fw-bolder mb-0 text-dark display-4"><?php echo e($userCount); ?></h2>
                </div>
                <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow text-center p-3">
                    
                    <i class="fas fa-user-shield fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-xl-0 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-sm mb-0 text-uppercase fw-bold opacity-7">Parcelas Registradas</p>
                    <h2 class="fw-bolder mb-0 text-dark display-4"><?php echo e($parcelaCount); ?></h2>
                </div>
                <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow text-center p-3">
                    
                    <i class="fas fa-draw-polygon fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-xl-0 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-sm mb-0 text-uppercase fw-bold opacity-7">Especies en Catálogo</p>
                    <h2 class="fw-bolder mb-0 text-dark display-4"><?php echo e($especieCount); ?></h2>
                </div>
                <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow text-center p-3">
                    
                    <i class="fas fa-seedling fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-xl-0 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-sm mb-0 text-uppercase fw-bold opacity-7">Turnos Activos</p>
                    <h2 class="fw-bolder mb-0 text-dark display-4"><?php echo e($turnosActivosCount); ?></h2>
                </div>
                <div class="icon icon-shape bg-gradient-danger text-white rounded-circle shadow text-center p-3">
                    
                    <i class="fas fa-clipboard-check fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 page-title">
                    <i class="fas fa-bolt me-2 text-primary"></i> Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <a href="<?php echo e(route('usuarios.index')); ?>" class="btn btn-action">
                            <div class="action-icon bg-primary-light">
                                
                                <i class="fas fa-user-plus text-primary"></i>
                            </div>
                            <span class="action-text">Gestionar Usuarios</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <a href="<?php echo e(route('parcelas.index')); ?>" class="btn btn-action">
                            <div class="action-icon bg-success-light">
                                
                                <i class="fas fa-layer-group text-success"></i>
                            </div>
                            <span class="action-text">Gestionar Parcelas</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <a href="<?php echo e(route('turno_cortas.index')); ?>" class="btn btn-action">
                            <div class="action-icon bg-info-light">
                                
                                <i class="fas fa-calendar-plus text-info"></i>
                            </div>
                            <span class="action-text">Planificar Corta</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <a href="<?php echo e(route('especies.index')); ?>" class="btn btn-action">
                            <div class="action-icon bg-warning-light">
                                
                                <i class="fas fa-folder-plus text-warning"></i>
                            </div>
                            <span class="action-text">Gestionar Especies</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/partials/dashboard-widgets.blade.php ENDPATH**/ ?>