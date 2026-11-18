@extends('layouts.uav')
@section('title', 'SIGMAD Campo · Inventario recibido')
@push('styles')
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/vendor/ol.css') }}">
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/registros.css') }}">
@endpush
@section('content')
<main class="cr-wrap">
    <header class="cr-head">
        <div>
            <p class="cr-eyebrow">SIGMAD / Campo / Administración</p>
            <h1>Inventario forestal recibido</h1>
            <p>Datos sincronizados desde teléfonos. El estado de revisión es independiente de la recepción.</p>
        </div>
        <div class="cr-head-actions">
            <a class="cr-btn cr-light" href="{{ route('campo.index') }}">Volver a captura</a>
            <a class="cr-btn cr-light" href="{{ route('campo.missions.create') }}">Misiones</a>
        </div>
    </header>
    @if($errors->any())<div class="cr-notice cr-error">{{ implode(' · ', $errors->all()) }}</div>@endif
    <section class="cr-metrics" aria-label="Resumen filtrado">
        <div><small>Registros recibidos</small><strong>{{ number_format((int)($totals->total ?? 0)) }}</strong></div>
        <div><small>Pendientes de revisión</small><strong>{{ number_format((int)($totals->pendientes ?? 0)) }}</strong></div>
        <div><small>Validados</small><strong>{{ number_format((int)($totals->validados ?? 0)) }}</strong></div>
        <div><small>Colaboradores</small><strong>{{ number_format((int)($totals->brigadistas ?? 0)) }}</strong></div>
    </section>
    <section class="cr-panel">
        <h2>Buscar y filtrar</h2>
        <form method="GET" action="{{ route('campo.records.index') }}" class="cr-filters">
            <label>Árbol, candidato o UUID
                <input name="buscar" maxlength="80" value="{{ $filters['buscar'] ?? '' }}" placeholder="Ej. P01-023 o A012">
            </label>
            <label>Parcela
                <select name="parcela"><option value="">Todas</option>
                    @foreach($parcelas as $p)<option value="{{ $p->id_parcela }}" @selected(($filters['parcela'] ?? '') == $p->id_parcela)>{{ $p->nom_parcela }}</option>@endforeach
                </select>
            </label>
            <label>Brigadista
                <select name="brigadista"><option value="">Todos</option>
                    @foreach($brigadistas as $b)<option value="{{ $b->id }}" @selected(($filters['brigadista'] ?? '') == $b->id)>{{ $b->name }}</option>@endforeach
                </select>
            </label>
            <label>Grupo
                <select name="grupo"><option value="">Todos</option>
                    @foreach(['A'=>'A · Candidatos IA','B'=>'B · Censo independiente','C'=>'C · Remediciones','H'=>'H · Hallazgo'] as $k=>$name)<option value="{{ $k }}" @selected(($filters['grupo'] ?? '') === $k)>{{ $name }}</option>@endforeach
                </select>
            </label>
            <label>Especie
                <select name="especie"><option value="">Todas</option>
                    @foreach(['PL'=>'Pinus lawsonii','QR'=>'Quercus rugosa','O'=>'Otra','ND'=>'No determinada'] as $k=>$name)<option value="{{ $k }}" @selected(($filters['especie'] ?? '') === $k)>{{ $name }}</option>@endforeach
                </select>
            </label>
            <label>Revisión
                <select name="revision"><option value="">Todos los estados</option>
                    @foreach(['pendiente'=>'Pendiente','en_revision'=>'En revisión','validado'=>'Validado','observado'=>'Con observación'] as $k=>$name)<option value="{{ $k }}" @selected(($filters['revision'] ?? '') === $k)>{{ $name }}</option>@endforeach
                </select>
            </label>
            <label>Fecha desde
                <input type="date" name="desde" value="{{ $filters['desde'] ?? '' }}">
            </label>
            <label>Fecha hasta
                <input type="date" name="hasta" value="{{ $filters['hasta'] ?? '' }}">
            </label>
            <div class="cr-filter-actions">
                <button class="cr-btn" type="submit">Aplicar filtros</button>
                <a class="cr-btn cr-light" href="{{ route('campo.records.index') }}">Limpiar</a>
                <a class="cr-btn cr-export" href="{{ route('campo.records.export', request()->query()) }}">Exportar CSV filtrado</a>
            </div>
        </form>
    </section>
    <section class="cr-panel">
        <div class="cr-section-head"><h2>Observaciones sincronizadas</h2><span>Mostrando {{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }} de {{ $rows->total() }}</span></div>
        <div class="cr-scroll"><table class="cr-table">
            <thead><tr><th>ID</th><th>Árbol / candidato</th><th>Grupo</th><th>Parcela</th><th>Brigadista</th><th>Especie</th><th>DAP (cm)</th><th>Altura (m)</th><th>Fecha de campo</th><th>Revisión</th><th></th></tr></thead>
            <tbody>
            @forelse($rows as $r)
                @php($p = $r->payload ?? [])
                <tr>
                    <td>#{{ $r->id }}</td>
                    <td><strong>{{ $r->tree_id ?: ($r->candidate_id ?: 'Sin identificador') }}</strong><small>{{ $r->candidate_id ? 'Candidato '.$r->candidate_id : 'Registro de campo' }}</small></td>
                    <td>{{ $r->grupo }}</td>
                    <td>{{ $r->parcela_nombre ?? $r->id_parcela }}</td>
                    <td>{{ $r->brigadista_nombre ?? $r->user_id }}</td>
                    <td>{{ ['PL'=>'P. lawsonii','QR'=>'Q. rugosa','O'=>'Otra','ND'=>'Sin determinar'][$p['especie'] ?? ''] ?? '—' }}</td>
                    <td>{{ $p['dap_cm'] ?? '—' }}</td>
                    <td>{{ $p['altura_m'] ?? '—' }}</td>
                    <td>{{ $r->client_created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td><span class="cr-pill cr-{{ $r->review_status }}">{{ str_replace('_', ' ', $r->review_status) }}</span></td>
                    <td><a class="cr-details" href="{{ route('campo.records.show', ['record' => $r->id]) }}">Ver ficha</a></td>
                </tr>
            @empty
                <tr><td colspan="11" class="cr-empty">No hay observaciones recibidas con esos filtros. Los registros pendientes en el teléfono todavía no aparecen aquí.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="cr-page-nav">
            <span>Página {{ $rows->currentPage() }} de {{ $rows->lastPage() }}</span>
            <span>
                @if($rows->onFirstPage())<span class="cr-muted">Anterior</span>@else<a href="{{ $rows->previousPageUrl() }}">Anterior</a>@endif
                @if($rows->hasMorePages())<a href="{{ $rows->nextPageUrl() }}">Siguiente</a>@else<span class="cr-muted">Siguiente</span>@endif
            </span>
        </div>
    </section>
    <section class="cr-panel">
        <div class="cr-section-head"><div><h2>Mapa de observaciones GPS</h2><p>Hasta 500 registros recientes de la selección filtrada. No muestra automáticamente centros de copa IA.</p></div><span id="cr-map-count">Preparando…</span></div>
        <div id="cr-record-map" role="img" aria-label="Mapa geográfico de posiciones registradas por los brigadistas"></div>
        <p class="cr-map-note" id="cr-map-note">La posición GPS corresponde al dispositivo junto al fuste, con incertidumbre reportada. Base cartográfica online, no pertenece al mapa HD offline del brigadista.</p>
        <div id="cr-selected-record" class="cr-map-detail" hidden></div>
    </section>
</main>
<script>
window.SIGMAD_FIELD_RECORD_MAP = @json(['url' => route('campo.records.map', request()->query())]);
</script>
<script src="{{ asset('sigmad-campo-assets/vendor/ol.js') }}" defer></script>
<script src="{{ asset('sigmad-campo-assets/registros.js') }}" defer></script>
@endsection
