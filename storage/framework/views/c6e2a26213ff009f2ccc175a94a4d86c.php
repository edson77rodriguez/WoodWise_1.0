<?php $__env->startSection('title', 'SIGMAD Campo · Ficha de observación'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('sigmad-campo-assets/registros.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<?php ($p = $record->payload ?? []); ?>
<main class="cr-wrap">
    <header class="cr-head">
        <div>
            <p class="cr-eyebrow">SIGMAD / Campo / Registro #<?php echo e($record->id); ?></p>
            <h1><?php echo e($record->tree_id ?: ($record->candidate_id ?: 'Observación sin ID')); ?></h1>
            <p>Grupo <?php echo e($record->grupo); ?> · <?php echo e($parcela); ?> · <?php echo e($brigadista); ?></p>
        </div>
        <a class="cr-btn cr-light" href="<?php echo e(route('campo.records.index')); ?>">Volver al inventario</a>
    </header>
    <?php if(session('status')): ?><div class="cr-notice cr-ok"><?php echo e(session('status')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="cr-notice cr-error"><?php echo e(implode(' · ', $errors->all())); ?></div><?php endif; ?>
    <section class="cr-detail-grid">
        <article class="cr-panel">
            <div class="cr-section-head"><h2>Identificación y procedencia</h2><span class="cr-pill cr-<?php echo e($record->review_status); ?>"><?php echo e(str_replace('_', ' ', $record->review_status)); ?></span></div>
            <dl class="cr-data-grid">
                <div><dt>Registro</dt><dd>#<?php echo e($record->id); ?></dd></div>
                <div><dt>UUID</dt><dd class="cr-mono"><?php echo e($record->uuid); ?></dd></div>
                <div><dt>Parcela</dt><dd><?php echo e($parcela); ?></dd></div>
                <div><dt>Brigadista</dt><dd><?php echo e($brigadista); ?></dd></div>
                <div><dt>Grupo</dt><dd><?php echo e($record->grupo); ?></dd></div>
                <div><dt>Árbol de campo</dt><dd><?php echo e($record->tree_id ?? '—'); ?></dd></div>
                <div><dt>Candidato IA</dt><dd><?php echo e($record->candidate_id ?? '—'); ?></dd></div>
                <div><dt>Localizado</dt><dd><?php echo e($p['localizado'] ?? '—'); ?></dd></div>
                <div><dt>Especie</dt><dd><?php echo e(['PL'=>'Pinus lawsonii','QR'=>'Quercus rugosa','O'=>'Otra','ND'=>'No determinada'][$p['especie'] ?? ''] ?? '—'); ?></dd></div>
                <div><dt>Estado del árbol</dt><dd><?php echo e($p['estado'] ?? '—'); ?></dd></div>
                <div><dt>Misión UUID</dt><dd class="cr-mono"><?php echo e($p['mission_uuid'] ?? '—'); ?></dd></div>
                <div><dt>Detección de origen</dt><dd class="cr-mono"><?php echo e($p['detection_ref'] ?? '—'); ?></dd></div>
            </dl>
        </article>
        <article class="cr-panel">
            <h2>Mediciones dasométricas</h2>
            <dl class="cr-data-grid">
                <?php $__currentLoopData = ['dap_cm'=>'DAP a 1.30 m (cm)','altura_m'=>'Altura total (m)','conf_altura'=>'Confianza altura','r_n_m'=>'Radio norte (m)','r_s_m'=>'Radio sur (m)','r_e_m'=>'Radio este (m)','r_o_m'=>'Radio oeste (m)','dominancia'=>'Dominancia','solapamiento'=>'Solapamiento','visibilidad'=>'Visibilidad']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><dt><?php echo e($name); ?></dt><dd><?php echo e($p[$key] ?? '—'); ?></dd></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </dl>
        </article>
        <article class="cr-panel">
            <h2>Georreferenciación y fechas</h2>
            <dl class="cr-data-grid">
                <?php $__currentLoopData = ['latitud'=>'Latitud (WGS84)','longitud'=>'Longitud (WGS84)','gps_precision_m'=>'Precisión GNSS reportada (m)','gps_fecha'=>'Fecha GPS','gps_metodo'=>'Método GPS','motivo_sin_medicion'=>'Motivo de valores faltantes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><dt><?php echo e($name); ?></dt><dd><?php echo e($p[$key] ?? '—'); ?></dd></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div><dt>Fecha capturada en dispositivo</dt><dd><?php echo e($record->client_created_at?->format('d/m/Y H:i:s') ?? '—'); ?></dd></div>
                <div><dt>Sincronización con Hostinger</dt><dd><?php echo e($record->synced_at?->format('d/m/Y H:i:s') ?? '—'); ?></dd></div>
                <div><dt>Observaciones</dt><dd class="cr-pre"><?php echo e($p['observaciones'] ?? '—'); ?></dd></div>
            </dl>
        </article>
        <article class="cr-panel">
            <h2>Revisión científica</h2>
            <p class="cr-muted">Revisar no cambia ninguna medición enviada desde campo. Los avisos son criterios operativos, no pruebas de error.</p>
            <?php if(count($warnings)): ?>
                <div class="cr-warning"><strong>Puntos para revisar:</strong><ul><?php $__currentLoopData = $warnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($w); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
            <?php else: ?> <div class="cr-notice cr-ok">No se activaron avisos automáticos básicos. Esto no equivale a validación científica.</div>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('campo.records.review', ['record' => $record->id])); ?>" class="cr-review-form">
                <?php echo csrf_field(); ?>
                <label>Estado de revisión
                    <select name="review_status" required>
                        <?php $__currentLoopData = ['pendiente'=>'Pendiente','en_revision'=>'En revisión','validado'=>'Validado científicamente','observado'=>'Observado / requiere corrección']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php if(old('review_status', $record->review_status) === $key): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>
                <label>Nota de revisión
                    <textarea name="review_note" maxlength="1000" rows="4" placeholder="Motivo, instrumento, evidencia adicional o incidencia…"><?php echo e(old('review_note', $record->review_note)); ?></textarea>
                </label>
                <button type="submit" class="cr-btn">Guardar revisión</button>
                <p class="cr-muted">Para «Observado» se requiere una nota. Los valores originales quedan intactos.</p>
            </form>
        </article>
    </section>
    <section class="cr-panel">
        <h2>Historial de revisión</h2>
        <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="cr-event"><strong><?php echo e($ev->reviewer_name); ?></strong> · <?php echo e($ev->created_at); ?> · <?php echo e($ev->previous_status); ?> → <?php echo e($ev->new_status); ?>

                <?php if($ev->note): ?><p><?php echo e($ev->note); ?></p><?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <p class="cr-muted">Aún no hay revisiones registradas.</p> <?php endif; ?>
        <details class="cr-technical"><summary>Integridad y datos originales</summary>
            <p>SHA-256 del payload enviado: <code><?php echo e($record->payload_sha256); ?></code></p>
            <p>La ficha no modifica el JSON original. Este hash procede de la sincronización inicial; no constituye por sí solo verificación contra campo.</p>
        </details>
    </section>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.uav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/campo/registros/show.blade.php ENDPATH**/ ?>