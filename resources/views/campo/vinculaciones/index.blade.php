@extends('layouts.uav')
@section('title', 'SIGMAD Campo · Vinculación científica')
@push('styles')
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/vendor/ol.css') }}">
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/registros.css') }}">
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/vinculaciones-v04.css') }}?v=0400">
@endpush
@section('content')
<main class="cr-wrap v04-page">
    <header class="cr-head">
        <div>
            <p class="cr-eyebrow">SIGMAD / Campo / Validación científica</p>
            <h1>Vinculación de copas IA y árboles de campo</h1>
            <p>Revisión posterior al levantamiento. Cada decisión conserva evidencia, responsable e historial.</p>
        </div>
        <div class="cr-head-actions">
            <a class="cr-btn cr-light" href="{{ route('campo.records.index') }}">Inventario forestal</a>
            <a class="cr-btn cr-light" href="{{ route('campo.missions.create') }}">Misiones UAV</a>
        </div>
    </header>
    @if(session('status'))<div class="cr-notice cr-ok">{{ session('status') }}</div>@endif
    @if($errors->any())
        <div class="cr-notice cr-error" role="alert"><strong>Revisa la decisión antes de guardar:</strong>
            <ul>@foreach($errors->all() as $msg)<li>{{ $msg }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="cr-panel" aria-label="Selección de misión">
        <form method="GET" action="{{ route('campo.links.index') }}" class="v04-choose-mission">
            <label for="v04-mission-picker">Misión UAV</label>
            <select id="v04-mission-picker" name="mission" required>
                <option value="">Seleccionar misión…</option>
                @foreach($missions as $item)
                    <option value="{{ $item->uuid }}" @selected($mission && $mission->uuid === $item->uuid)>
                        {{ $item->title }} · {{ $item->selected_count }} copas · Parcela {{ $item->id_parcela }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="cr-btn">Cargar revisión</button>
        </form>
        <p class="v04-hint">Se trabaja sobre la instantánea original de copas seleccionadas para esta misión; no sobre las predicciones completas del análisis.</p>
    </section>

    @if($mission)
        <section class="v04-kpis" aria-label="Resumen de decisiones">
            <div><span>Copas asignadas</span><strong>{{ $stats['total'] }}</strong></div>
            <div><span>Sin evaluar</span><strong>{{ $stats['sin_evaluar'] }}</strong></div>
            <div><span>Confirmadas</span><strong>{{ $stats['confirmada'] }}</strong></div>
            <div><span>Dudosas</span><strong>{{ $stats['dudosa'] }}</strong></div>
            <div><span>Sin correspondencia documentada</span><strong>{{ $stats['sin_correspondencia'] }}</strong></div>
        </section>

        <section class="cr-panel v04-map-panel">
            <div class="cr-section-head">
                <div>
                    <h2>Seleccionar sobre el ortomosaico</h2>
                    <p>Haz clic en un contorno turquesa y después en una posición GPS azul. También puedes utilizar los selectores del formulario.</p>
                </div>
                <span id="v04-map-counter">Preparando el mapa…</span>
            </div>
            <div class="v04-map-toolbar">
                <label><input type="checkbox" id="v04-image-visible" checked> Ortomosaico</label>
                <label><input type="checkbox" id="v04-crowns-visible" checked> Copas IA</label>
                <label><input type="checkbox" id="v04-gps-visible" checked> GPS campo</label>
                <label><input type="checkbox" id="v04-precision-visible" checked> Precisión GNSS</label>
                <button type="button" id="v04-fit-mission">Enfocar misión</button>
                <button type="button" id="v04-fit-gps">Enfocar GPS</button>
            </div>
            <div id="v04-map"
                data-mission-url="{{ route('campo.mission.data', ['mission' => $mission->uuid]) }}"
                data-points-url="{{ route('campo.links.points', ['mission' => $mission->uuid]) }}"
                data-initial-crown="{{ $selectedCrown }}"
                aria-label="Mapa de copas IA y observaciones GPS para vinculación manual" role="region"></div>
            <p class="v04-map-feedback" id="v04-map-feedback" role="status">Cargando datos geoespaciales…</p>
            <p class="v04-hint">Un GPS fuera del mosaico puede ser una prueba o un problema de georreferenciación. No declares FN o FP con base en distancia sin corroboración.</p>
        </section>

        <section class="v04-review-grid">
            <article class="cr-panel" id="v04-form">
                <h2>Registrar correspondencia</h2>
                <p class="v04-hint">Se registra la evaluación de una copa de la misión. Una ficha de campo solo puede quedar vinculada a una copa dentro de esta misión.</p>
                <form method="POST" action="{{ route('campo.links.store') }}" class="v04-form">
                    @csrf
                    <input type="hidden" name="mission_uuid" value="{{ $mission->uuid }}">
                    <label for="v04-crown-select">Copa de IA <span>*</span></label>
                    <select id="v04-crown-select" name="operational_id" required>
                        <option value="">Seleccionar una copa…</option>
                        @foreach($crowns as $c)
                            @php($existing = $links->get($c['id']))
                            <option value="{{ $c['id'] }}"
                                data-decision="{{ $existing->decision ?? '' }}"
                                data-record="{{ $existing->field_observation_id ?? '' }}"
                                data-evidence="{{ $existing->evidence_type ?? '' }}"
                                data-note="{{ $existing->justification ?? '' }}"
                                @selected(old('operational_id', $selectedCrown) === $c['id'])>
                                {{ $c['id'] }} · {{ $existing ? str_replace('_',' ', $existing->decision) : 'Sin evaluar' }}
                            </option>
                        @endforeach
                    </select>
                    <label for="v04-observation-select">Árbol de campo</label>
                    <select id="v04-observation-select" name="field_observation_id">
                        <option value="">Sin árbol vinculado</option>
                        @foreach($records as $r)
                            <option value="{{ $r->id }}" @selected((string)old('field_observation_id', '') === (string)$r->id)>
                                #{{ $r->id }} · {{ $r->tree_id ?: ($r->candidate_id ?: 'Sin ID') }} · Grupo {{ $r->grupo }} · {{ $r->payload['especie'] ?? 'ND' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="v04-hint">La lista contiene registros de la misma parcela (A, B y H). Las remediciones C no representan nuevos individuos.</p>
                    <label for="v04-decision-select">Decisión de revisión <span>*</span></label>
                    <select id="v04-decision-select" name="decision" required>
                        <option value="">Seleccionar…</option>
                        <option value="confirmada" @selected(old('decision') === 'confirmada')>Confirmada: mismo individuo con evidencia</option>
                        <option value="dudosa" @selected(old('decision') === 'dudosa')>Dudosa: correspondencia no concluyente</option>
                        <option value="sin_correspondencia" @selected(old('decision') === 'sin_correspondencia')>Sin correspondencia documentada</option>
                    </select>
                    <label for="v04-evidence-select">Evidencia principal <span>*</span></label>
                    <select id="v04-evidence-select" name="evidence_type" required>
                        <option value="revision_mapa" @selected(old('evidence_type') === 'revision_mapa')>Revisión de ortomosaico y vecinos</option>
                        <option value="campo_independiente" @selected(old('evidence_type') === 'campo_independiente')>Verificación independiente en campo</option>
                        <option value="fotografia" @selected(old('evidence_type') === 'fotografia')>Fotografía de apoyo</option>
                        <option value="croquis" @selected(old('evidence_type') === 'croquis')>Croquis / identificación relativa</option>
                        <option value="otra" @selected(old('evidence_type') === 'otra')>Otra evidencia identificable</option>
                    </select>
                    <label for="v04-justification">Justificación técnica <span>*</span></label>
                    <textarea id="v04-justification" name="justification" rows="4" minlength="15" maxlength="2000"
                        placeholder="Describe cómo verificaste la identidad: referencias de copa, vecinos, ubicación del fuste, fotografía o croquis…" required>{{ old('justification') }}</textarea>
                    <label class="v04-confirm">
                        <input type="checkbox" name="verified_context" value="1" required>
                        <span>Revisé la evidencia; entiendo que esta asociación NO genera automáticamente TP, FP ni FN.</span>
                    </label>
                    <button class="cr-btn" type="submit">Guardar decisión con historial</button>
                    <p id="v04-context-warning" class="v04-warning" hidden></p>
                </form>
            </article>
            <article class="cr-panel">
                <div class="cr-section-head"><h2>Copas de la misión</h2>
                    <a class="cr-details" href="{{ route('campo.links.export', ['mission' => $mission->uuid]) }}">Exportar CSV</a>
                </div>
                <div class="v04-crown-list">
                    @forelse($crowns as $c)
                        @php($entry = $links->get($c['id']))
                        <button type="button" class="v04-crown-row" data-crown="{{ $c['id'] }}" data-decision="{{ $entry->decision ?? 'sin_evaluar' }}">
                            <span><strong>{{ $c['id'] }}</strong><small>{{ $c['model'] }}{{ $c['requires_review'] ? ' · Revisar IA' : '' }}</small></span>
                            <span class="v04-state v04-state-{{ $entry->decision ?? 'sin_evaluar' }}">{{ $entry ? str_replace('_', ' ', $entry->decision) : 'Sin evaluar' }}</span>
                            @if($entry && $entry->field_observation_id)<small>Campo #{{ $entry->field_observation_id }}</small>@endif
                        </button>
                    @empty
                        <p class="v04-hint">Esta misión no tiene copas elegibles para vinculación.</p>
                    @endforelse
                </div>
                <p class="v04-hint">«Sin evaluar» y «sin correspondencia documentada» no son automáticamente falsos positivos.</p>
            </article>
        </section>

        <section class="cr-panel">
            <h2>Historial auditable reciente</h2>
            @forelse($history as $h)
                <div class="cr-event">
                    <strong>{{ $h->operational_id }}</strong> · {{ $h->reviewer_name }} · {{ $h->created_at }}
                    <div>{{ $h->previous_decision ? str_replace('_',' ', $h->previous_decision) : 'Primera revisión' }} → <strong>{{ str_replace('_',' ', $h->new_decision) }}</strong>
                        @if($h->new_observation_id) · Campo #{{ $h->new_observation_id }} @endif</div>
                    <p>{{ $h->justification }}</p>
                </div>
            @empty
                <p class="cr-muted">Todavía no hay decisiones para esta misión. No hay asociaciones automáticas.</p>
            @endforelse
            <p class="v04-hint">Se muestran los últimos 25 eventos. El historial completo permanece en la tabla field_crown_link_events.</p>
        </section>
    @else
        <section class="cr-panel"><h2>Comencemos con una misión</h2>
            <p>Elige un levantamiento del selector superior para revisar sus copas seleccionadas y los árboles medidos de la misma parcela.</p>
            <p class="v04-hint">Las mediciones originales, los polígonos IA y los registros offline no se modifican.</p>
        </section>
    @endif
</main>
@if($mission)
<script src="{{ asset('sigmad-campo-assets/vendor/proj4.js') }}" defer></script>
<script src="{{ asset('sigmad-campo-assets/vendor/ol.js') }}" defer></script>
<script src="{{ asset('sigmad-campo-assets/vinculaciones-v04.js') }}?v=0400" defer></script>
@endif
@endsection
