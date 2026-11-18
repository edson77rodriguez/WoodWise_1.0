<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Validación de Cédula Profesional</h2>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('confirmar.cedula')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="cedula" class="form-label">Número de Cédula Profesional</label>
            <input type="text" id="cedula" name="cedula" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Validar y Continuar</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/auth/validar_cedula.blade.php ENDPATH**/ ?>