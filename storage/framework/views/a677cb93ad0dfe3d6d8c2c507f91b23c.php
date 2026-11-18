<?php $__env->startSection('title', 'SIGMAD Campo · Inventario recibido'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('sigmad-campo-assets/vendor/ol.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('sigmad-campo-assets/registros.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('sigmad-campo-assets/registros-mapa-integrado.css')); ?>?v=0320">
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<main class="cr-wrap">
    <header class="cr-head">
        <div>
            <p class="cr-eyebrow">SIGMAD / Campo / Administración</p>
            <h1>Inventario forestal recibido</h1>
            <p>Datos sincronizados desde teléfonos. El estado de revisión es independiente de la recepción.</p>
        </div>
        <div class="cr-head-actions">
            <a class="cr-btn cr-light" href="<?php echo e(route('campo.index')); ?>">Volver a captura</a>
            <a class="cr-btn cr-light" href="<?php echo e(route('campo.missions.create')); ?>">Misiones</a>
        </div>
    </header>
    <?php if($errors->any()): ?><div class="cr-notice cr-error"><?php echo e(implode(' · ', $errors->all())); ?></div><?php endif; ?>
    <section class="cr-metrics" aria-label="Resumen filtrado">
        <div><small>Registros recibidos</small><strong><?php echo e(number_format((int)($totals->total ?? 0))); ?></strong></div>
        <div><small>Pendientes de revisión</small><strong><?php echo e(number_format((int)($totals->pendientes ?? 0))); ?></strong></div>
        <div><small>Validados</small><strong><?php echo e(number_format((int)($totals->validados ?? 0))); ?></strong></div>
        <div><small>Colaboradores</small><strong><?php echo e(number_format((int)($totals->brigadistas ?? 0))); ?></strong></div>
    </section>
    <section class="cr-panel">
        <h2>Buscar y filtrar</h2>
        <form method="GET" action="<?php echo e(route('campo.records.index')); ?>" class="cr-filters">
            <label>Árbol, candidato o UUID
                <input name="buscar" maxlength="80" value="<?php echo e($filters['buscar'] ?? ''); ?>" placeholder="Ej. P01-023 o A012">
            </label>
            <label>Parcela
                <select name="parcela"><option value="">Todas</option>
                    <?php $__currentLoopData = $parcelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id_parcela); ?>" <?php if(($filters['parcela'] ?? '') == $p->id_parcela): echo 'selected'; endif; ?>><?php echo e($p->nom_parcela); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>Brigadista
                <select name="brigadista"><option value="">Todos</option>
                    <?php $__currentLoopData = $brigadistas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b->id); ?>" <?php if(($filters['brigadista'] ?? '') == $b->id): echo 'selected'; endif; ?>><?php echo e($b->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>Grupo
                <select name="grupo"><option value="">Todos</option>
                    <?php $__currentLoopData = ['A'=>'A · Candidatos IA','B'=>'B · Censo independiente','C'=>'C · Remediciones','H'=>'H · Hallazgo']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(($filters['grupo'] ?? '') === $k): echo 'selected'; endif; ?>><?php echo e($name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>Especie
                <select name="especie"><option value="">Todas</option>
                    <?php $__currentLoopData = ['PL'=>'Pinus lawsonii','QR'=>'Quercus rugosa','O'=>'Otra','ND'=>'No determinada']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(($filters['especie'] ?? '') === $k): echo 'selected'; endif; ?>><?php echo e($name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>Revisión
                <select name="revision"><option value="">Todos los estados</option>
                    <?php $__currentLoopData = ['pendiente'=>'Pendiente','en_revision'=>'En revisión','validado'=>'Validado','observado'=>'Con observación']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(($filters['revision'] ?? '') === $k): echo 'selected'; endif; ?>><?php echo e($name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>Fecha desde
                <input type="date" name="desde" value="<?php echo e($filters['desde'] ?? ''); ?>">
            </label>
            <label>Fecha hasta
                <input type="date" name="hasta" value="<?php echo e($filters['hasta'] ?? ''); ?>">
            </label>
            <div class="cr-filter-actions">
                <button class="cr-btn" type="submit">Aplicar filtros</button>
                <a class="cr-btn cr-light" href="<?php echo e(route('campo.records.index')); ?>">Limpiar</a>
                <a class="cr-btn cr-export" href="<?php echo e(route('campo.records.export', request()->query())); ?>">Exportar CSV filtrado</a>
            </div>
        </form>
    </section>
    <section class="cr-panel">
        <div class="cr-section-head"><h2>Observaciones sincronizadas</h2><span>Mostrando <?php echo e($rows->firstItem() ?? 0); ?>–<?php echo e($rows->lastItem() ?? 0); ?> de <?php echo e($rows->total()); ?></span></div>
        <div class="cr-scroll"><table class="cr-table">
            <thead><tr><th>ID</th><th>Árbol / candidato</th><th>Grupo</th><th>Parcela</th><th>Brigadista</th><th>Especie</th><th>DAP (cm)</th><th>Altura (m)</th><th>Fecha de campo</th><th>Revisión</th><th></th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php ($p = $r->payload ?? []); ?>
                <tr>
                    <td>#<?php echo e($r->id); ?></td>
                    <td><strong><?php echo e($r->tree_id ?: ($r->candidate_id ?: 'Sin identificador')); ?></strong><small><?php echo e($r->candidate_id ? 'Candidato '.$r->candidate_id : 'Registro de campo'); ?></small></td>
                    <td><?php echo e($r->grupo); ?></td>
                    <td><?php echo e($r->parcela_nombre ?? $r->id_parcela); ?></td>
                    <td><?php echo e($r->brigadista_nombre ?? $r->user_id); ?></td>
                    <td><?php echo e(['PL'=>'P. lawsonii','QR'=>'Q. rugosa','O'=>'Otra','ND'=>'Sin determinar'][$p['especie'] ?? ''] ?? '—'); ?></td>
                    <td><?php echo e($p['dap_cm'] ?? '—'); ?></td>
                    <td><?php echo e($p['altura_m'] ?? '—'); ?></td>
                    <td><?php echo e($r->client_created_at?->format('d/m/Y H:i') ?? '—'); ?></td>
                    <td><span class="cr-pill cr-<?php echo e($r->review_status); ?>"><?php echo e(str_replace('_', ' ', $r->review_status)); ?></span></td>
                    <td><a class="cr-details" href="<?php echo e(route('campo.records.show', ['record' => $r->id])); ?>">Ver ficha</a></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="11" class="cr-empty">No hay observaciones recibidas con esos filtros. Los registros pendientes en el teléfono todavía no aparecen aquí.</td></tr>
            <?php endif; ?>
            </tbody>
        </table></div>
        <div class="cr-page-nav">
            <span>Página <?php echo e($rows->currentPage()); ?> de <?php echo e($rows->lastPage()); ?></span>
            <span>
                <?php if($rows->onFirstPage()): ?><span class="cr-muted">Anterior</span><?php else: ?><a href="<?php echo e($rows->previousPageUrl()); ?>">Anterior</a><?php endif; ?>
                <?php if($rows->hasMorePages()): ?><a href="<?php echo e($rows->nextPageUrl()); ?>">Siguiente</a><?php else: ?><span class="cr-muted">Siguiente</span><?php endif; ?>
            </span>
        </div>
    </section>
    <section class="cr-panel">
        <div class="cr-section-head"><div>
            <h2>Mapa integrado: campo + copas IA</h2>
            <p>Administración y revisión posterior al levantamiento. Las copas son únicamente las asignadas a la misión elegida.</p>
        </div><span id="cr-map-count">Preparando…</span></div>
        <div class="cr-map-toolbar">
            <label>Misión UAV para superponer
                <select id="cr-mission-select"><option value="">Solo puntos GPS de campo</option></select>
            </label>
            <button type="button" id="cr-fit-mission">Enfocar misión</button>
            <button type="button" id="cr-fit-gps">Enfocar GPS de campo</button>
        </div>
        <div class="cr-map-toggles" aria-label="Capas del mapa">
            <label><input type="checkbox" id="cr-layer-image" checked> Ortomosaico (vista previa / HD)</label>
            <label><input type="checkbox" id="cr-layer-ai" checked> Copas IA de la misión</label>
            <label><input type="checkbox" id="cr-layer-gps" checked> Árboles registrados en campo</label>
            <label><input type="checkbox" id="cr-layer-accuracy" checked> Precisión GNSS reportada</label>
        </div>
        <div class="cr-map-legend" aria-label="Simbología">
            <span><b></b> GPS medido</span>
            <span><b class="cr-ai-legend"></b> Contorno IA asignado</span>
        </div>
        <p class="cr-map-status" id="cr-map-status" role="status">Preparando visualización…</p>
        <div id="cr-record-map" role="region" aria-label="Mapa comparativo de observaciones georreferenciadas y copas asignadas por IA"></div>
        <p class="cr-map-note" id="cr-map-note">El punto GPS es la posición reportada del teléfono junto al fuste, no el centro de la copa IA. No se clasifica TP/FP/FN automáticamente.</p>
        <div id="cr-selected-record" class="cr-map-detail" hidden></div>
        <p class="cr-map-info">Mapa administrativo online; no sustituye las misiones HD offline de SIGMAD Campo. Las capas y filtros no modifican mediciones.</p>
    </section>
</main>
<script>
<?php
    $sigmadCampoMapConfig = [
        'url' => route('campo.records.map', request()->query()),
        'missions_url' => route('campo.records.layers.missions', array_filter(['parcela' => request()->query('parcela')])),
        'parcela_id' => request()->query('parcela'),
    ];
?>
window.SIGMAD_FIELD_RECORD_MAP = <?php echo json_encode($sigmadCampoMapConfig, 15, 512) ?>;
</script>
<script src="<?php echo e(asset('sigmad-campo-assets/vendor/proj4.js')); ?>" defer></script>
<script src="<?php echo e(asset('sigmad-campo-assets/vendor/ol.js')); ?>" defer></script>
<script src="<?php echo e(asset('sigmad-campo-assets/registros-mapa-integrado.js')); ?>?v=0320" defer></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.uav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/campo/registros/index.blade.php ENDPATH**/ ?>