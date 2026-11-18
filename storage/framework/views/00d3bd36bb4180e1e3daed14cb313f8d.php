<?php $__env->startSection('title', 'SIGMAD Campo · Preparar misiones'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('sigmad-campo-assets/vendor/ol.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('sigmad-campo-assets/misiones.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<div class="cmissions">
    <header class="cmissions-title">
        <div><h1>Preparar misión de campo</h1><p>Selecciona copas del análisis UAV y asígnalas a una parcela. No se modifican las predicciones originales.</p></div>
        <a href="<?php echo e(route('campo.index')); ?>">Volver a SIGMAD Campo</a>
    </header>
    <?php if(session('status')): ?><div class="cmissions-ok"><?php echo e(session('status')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="cmissions-error"><?php echo e(implode(' · ', $errors->all())); ?></div><?php endif; ?>
    <form action="<?php echo e(route('campo.missions.store')); ?>" method="POST" id="mission-form" class="cmissions-layout">
        <?php echo csrf_field(); ?>
        <div class="cmissions-panel">
            <label>Nombre de la misión<input name="title" required maxlength="120" value="<?php echo e(old('title', 'Levantamiento piloto')); ?>"></label>
            <label>Parcela asignada<select name="id_parcela" required>
                <option value="">Seleccionar parcela…</option>
                <?php $__currentLoopData = $parcelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id_parcela); ?>" <?php if(old('id_parcela') == $p->id_parcela): echo 'selected'; endif; ?>><?php echo e($p->nom_parcela); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select></label>
            <label>Análisis completado<select name="analysis_uuid" id="mission-analysis" required>
                <option value="">Seleccionar vuelo…</option>
                <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($j['uuid']); ?>"><?php echo e($j['label']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select></label>
            <p id="mission-status">Elige un análisis. Toca las copas para asignarlas a la misión.</p>
            <div class="cmissions-legend"><span>◯ Copa disponible</span><span>● Copa seleccionada</span></div>
            <div class="cmissions-count"><strong id="selected-count">0</strong> árboles seleccionados (máximo 200)</div>
            <div id="selected-list" class="cmissions-selected"></div>
            <div id="selected-inputs"></div>
            <button type="button" id="clear-mission" class="cmissions-secondary">Limpiar selección</button>
            <button type="submit" id="save-mission">Guardar misión para brigadistas</button>
            <p class="cmissions-hint">Los técnicos asignados a esta parcela podrán descargar la misión. En esta versión aún no existen asignaciones individuales por brigadista.</p>
        </div>
        <div class="cmissions-mapwrap"><div id="mission-map" aria-label="Mapa de selección de copas"></div></div>
    </form>
    <section class="cmissions-existing"><h2>Misiones recientes</h2>
        <?php $__empty_1 = true; $__currentLoopData = $missions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <p><strong><?php echo e($m->title); ?></strong> · Parcela <?php echo e($m->id_parcela); ?> · <?php echo e($m->selected_count); ?> copas · <?php echo e($m->status); ?> · <?php echo e($m->created_at?->format('Y-m-d')); ?> · <a href="<?php echo e(route('campo.mission.export', ['mission' => $m->uuid])); ?>">Descargar JSON para mapa HD</a></p>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <p>Todavía no hay misiones.</p> <?php endif; ?>
    </section>
</div>
<script>window.SIGMAD_MISSIONS_BOOT = <?php echo json_encode($jobs, 15, 512) ?>;</script>
<script src="<?php echo e(asset('sigmad-campo-assets/vendor/proj4.js')); ?>" defer></script>
<script src="<?php echo e(asset('sigmad-campo-assets/vendor/ol.js')); ?>" defer></script>
<script src="<?php echo e(asset('sigmad-campo-assets/misiones.js')); ?>" defer></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.uav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/campo/missions.blade.php ENDPATH**/ ?>