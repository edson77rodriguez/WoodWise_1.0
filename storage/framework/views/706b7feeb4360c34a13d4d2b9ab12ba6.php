<?php $__env->startSection('title', 'SIGMAD Campo · Mapa e inventario forestal'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('sigmad-campo-assets/vendor/ol.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('sigmad-campo-assets/campo.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<section class="campo-app" id="campo-root"><p>Preparando inventario digital…</p></section>
<script>
window.SIGMAD_FIELD_BOOT = <?php echo json_encode($campoBootstrap, 15, 512) ?>;
</script>
<script src="<?php echo e(asset('sigmad-campo-assets/vendor/proj4.js')); ?>" defer></script>
<script src="<?php echo e(asset('sigmad-campo-assets/vendor/ol.js')); ?>" defer></script>
<script src="<?php echo e(asset('sigmad-campo-assets/campo-mapa.js')); ?>" defer></script>
<script src="<?php echo e(asset('sigmad-campo-assets/campo.js')); ?>" defer></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.uav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/campo/index.blade.php ENDPATH**/ ?>