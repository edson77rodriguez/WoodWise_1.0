<?php $__env->startSection('title', 'Iniciar Sesión - SIGMAD'); ?>


<?php $__env->startPush('styles'); ?>
    <link href="<?php echo e(asset('css/WW/login.css')); ?>" rel="stylesheet">
<?php $__env->stopPush(); ?>


<?php $__env->startSection('content'); ?>
<div class="container-fluid bg-light min-vh-100 d-flex align-items-center">
    <div class="container py-4 py-md-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    
                    <div class="card-header bg-gradient-primary py-3 py-md-4">
                        <div class="text-center position-relative">
                            <div class="logo-3d-container mb-3">
                                <img src="<?php echo e(asset('assets/images/SIGMAD.svg')); ?>" alt="Logo SIGMAD" 
                                     class="logo-3d img-fluid">
                            </div>
                            <h2 class="text-white mb-1 h4 animate__animated animate__fadeIn">Bienvenido a SIGMAD</h2>
                            <p class="text-white-50 mb-0 small animate__animated animate__fadeIn animate__delay-1s">Sistema Inteligente de Gestión Maderable</p>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if($errors->any()): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i> 
                                <div>Credenciales incorrectas. Por favor intente nuevamente.</div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo e(route('login')); ?>" class="needs-validation" novalidate>
                            <?php echo csrf_field(); ?>
                            
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input type="email" 
                                           class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="email" 
                                           name="email" 
                                           placeholder="Correo electrónico"
                                           value="<?php echo e(old('email')); ?>" 
                                           required 
                                           autofocus>
                                    <label for="email">
                                        <i class="fas fa-envelope me-2 text-muted"></i>Correo electrónico
                                    </label>
                                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-floating position-relative">
                                    <input type="password" 
                                           class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Contraseña"
                                           required>
                                    <label for="password">
                                        <i class="fas fa-lock me-2 text-muted"></i>Contraseña
                                    </label>
                                    
                                    
                                    <span class="position-absolute end-0 top-50 translate-middle-y me-3 toggle-password" 
                                          data-bs-target="#password"
                                          title="Mostrar/Ocultar contraseña">
                                        <i class="fas fa-eye text-primary"></i>
                                    </span>
                                    
                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="remember" 
                                           id="remember">
                                    <label class="form-check-label text-muted" for="remember">
                                        <i class="fas fa-check-circle me-1"></i>Recordar sesión
                                    </label>
                                </div>
                                <a href="<?php echo e(route('password.request')); ?>" 
                                   class="text-decoration-none text-primary fw-bold">
                                    <i class="fas fa-key me-1"></i>¿Olvidó su contraseña?
                                </a>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold">
                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                </button>
                            </div>

                            <div class="text-center mt-4">
                                <p class="text-muted">¿No tiene una cuenta? 
                                    <a href="<?php echo e(route('register')); ?>" class="text-primary fw-bold text-decoration-none">
                                        <i class="fas fa-user-plus me-1"></i>Regístrese aquí
                                    </a>
                                </p>
                            </div>

                            <div class="position-relative my-4">
                                <hr>
                                <div class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted">
                                    O continuar con
                                </div>
                            </div>

                            <div class="d-flex justify-content-center gap-3">
                                <a href="#" class="btn btn-outline-primary rounded-circle p-3 social-btn">
                                    <i class="fab fa-google"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary rounded-circle p-3 social-btn">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary rounded-circle p-3 social-btn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('js/WW/login.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/auth/login.blade.php ENDPATH**/ ?>