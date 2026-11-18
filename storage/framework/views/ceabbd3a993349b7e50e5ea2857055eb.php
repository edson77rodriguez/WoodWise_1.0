<?php $__env->startSection('title', 'Registro - SIGMAD'); ?>


<?php $__env->startPush('styles'); ?>
    <link href="<?php echo e(asset('css/WW/register.css')); ?>" rel="stylesheet">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-3 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-gradient-primary py-3">
                    <div class="text-center">
                        <h2 class="text-white mb-0 h4"><?php echo e(__('Crear nueva cuenta')); ?></h2>
                        <p class="text-white-50 mb-0 small">Únete a nuestra plataforma</p>
                    </div>
                </div>

                <div class="card-body p-4"> 
                    
                    
                    <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-3 rounded-2" role="alert">
                         <div class="d-flex align-items-center"><i class="fas fa-exclamation-circle me-2"></i><div class="small"><?php echo e(session('error')); ?></div><button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button></div>
                    </div>
                    <?php endif; ?>
                    <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-3 rounded-2" role="alert">
                         <div class="d-flex align-items-center"><i class="fas fa-check-circle me-2"></i><div class="small"><?php echo e(session('success')); ?></div><button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button></div>
                    </div>
                    <?php endif; ?>

                    <form id="register-form" method="POST" action="<?php echo e(route('register')); ?>" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <h5 class="form-section-title"><i class="fas fa-user-circle me-1"></i> Información Personal</h5>
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="nom" name="nom" placeholder="Nombre" value="<?php echo e(old('nom')); ?>" required autofocus>
                                        <label for="nom"><?php echo e(__('Nombre')); ?></label>
                                        <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control <?php $__errorArgs = ['ap'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="ap" name="ap" placeholder="Apellido Paterno" value="<?php echo e(old('ap')); ?>" required>
                                        <label for="ap"><?php echo e(__('Apellido Paterno')); ?></label>
                                        <?php $__errorArgs = ['ap'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control <?php $__errorArgs = ['am'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="am" name="am" placeholder="Apellido Materno" value="<?php echo e(old('am')); ?>" required>
                                        <label for="am"><?php echo e(__('Apellido Materno')); ?></label>
                                        <?php $__errorArgs = ['am'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="telefono" name="telefono" placeholder="Teléfono" value="<?php echo e(old('telefono')); ?>" required inputmode="tel">
                                        <label for="telefono"><?php echo e(__('Teléfono')); ?></label>
                                        <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h5 class="form-section-title"><i class="fas fa-key me-1"></i> Credenciales de Acceso</h5>
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email" name="email" placeholder="Correo Electrónico" value="<?php echo e(old('email')); ?>" required inputmode="email">
                                        <label for="email"><?php echo e(__('Correo Electrónico')); ?></label>
                                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="password" name="password" placeholder="Contraseña" required minlength="8">
                                        <label for="password"><?php echo e(__('Contraseña')); ?></label>
                                        
                                        <span class="toggle-password" data-bs-target="#password" title="Mostrar/Ocultar contraseña">
                                            <i class="fas fa-eye small"></i>
                                        </span>
                                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <small class="form-text text-muted">Mínimo 8 caracteres</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="password" class="form-control" id="password-confirm" name="password_confirmation" placeholder="Confirmar Contraseña" required minlength="8">
                                        <label for="password-confirm"><?php echo e(__('Confirmar Contraseña')); ?></label>
                                        
                                        <span class="toggle-password" data-bs-target="#password-confirm" title="Mostrar/Ocultar contraseña">
                                            <i class="fas fa-eye small"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="card border-light shadow-sm">
                                <div class="card-header bg-light py-2">
                                    <h5 class="form-section-title mb-0"><i class="fas fa-user-tag me-1"></i> Tipo de Usuario</h5>
                                </div>
                                <div class="card-body p-2 p-md-3">
                                    <label for="id_rol" class="form-label fw-bold text-muted mb-2 small">Selecciona tu tipo de usuario</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user-shield text-primary small"></i></span>
                                        
                                        <select class="form-select <?php $__errorArgs = ['id_rol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="id_rol" id="id_rol" required>
                                            <option value="" disabled <?php echo e(old('id_rol') ? '' : 'selected'); ?>>-- Selecciona un rol --</option>
                                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($role->id_rol); ?>" <?php echo e(old('id_rol') == $role->id_rol ? 'selected' : ''); ?>>
                                                    <?php echo e(ucfirst($role->nom_rol)); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <?php $__errorArgs = ['id_rol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block mt-1 small"><i class="fas fa-exclamation-circle me-1"></i><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <small class="form-text text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>Este campo determina tus permisos.</small>
                                </div>
                            </div>
                        </div>

                        
                        <div id="cedula-container" class="mb-3 <?php if(old('id_rol') != 2): ?> d-none <?php endif; ?>"> 
                            <div class="form-floating">
                                <input type="text" class="form-control <?php $__errorArgs = ['cedula'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="cedula" name="cedula" placeholder="Cédula" value="<?php echo e(old('cedula', '')); ?>" inputmode="numeric">
                                <label for="cedula"><?php echo e(__('Cédula Profesional')); ?></label>
                                <?php $__errorArgs = ['cedula'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold">
                                <i class="fas fa-user-plus me-1"></i> <?php echo e(__('Crear cuenta')); ?>

                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <p class="text-muted small">¿Ya tienes una cuenta? 
                                <a href="<?php echo e(route('login')); ?>" class="text-primary fw-bold text-decoration-none">
                                    <?php echo e(__('Inicia sesión aquí')); ?>

                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('js/WW/register.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/auth/register.blade.php ENDPATH**/ ?>