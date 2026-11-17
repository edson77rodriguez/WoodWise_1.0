@extends('layouts.uav')
@section('title', 'SIGMAD Campo · Ficha de observación')
@push('styles')
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/registros.css') }}">
@endpush
@section('content')
@php($p = $record->payload ?? [])
<main class="cr-wrap">
    <header class="cr-head">
        <div>
            <p class="cr-eyebrow">SIGMAD / Campo / Registro #{{ $record->id }}</p>
            <h1>{{ $record->tree_id ?: ($record->candidate_id ?: 'Observación sin ID') }}</h1>
            <p>Grupo {{ $record->grupo }} · {{ $parcela }} · {{ $brigadista }}</p>
        </div>
        <a class="cr-btn cr-light" href="{{ route('campo.records.index') }}">Volver al inventario</a>
    </header>
    @if(session('status'))<div class="cr-notice cr-ok">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="cr-notice cr-error">{{ implode(' · ', $errors->all()) }}</div>@endif
    <section class="cr-detail-grid">
        <article class="cr-panel">
            <div class="cr-section-head"><h2>Identificación y procedencia</h2><span class="cr-pill cr-{{ $record->review_status }}">{{ str_replace('_', ' ', $record->review_status) }}</span></div>
            <dl class="cr-data-grid">
                <div><dt>Registro</dt><dd>#{{ $record->id }}</dd></div>
                <div><dt>UUID</dt><dd class="cr-mono">{{ $record->uuid }}</dd></div>
                <div><dt>Parcela</dt><dd>{{ $parcela }}</dd></div>
                <div><dt>Brigadista</dt><dd>{{ $brigadista }}</dd></div>
                <div><dt>Grupo</dt><dd>{{ $record->grupo }}</dd></div>
                <div><dt>Árbol de campo</dt><dd>{{ $record->tree_id ?? '—' }}</dd></div>
                <div><dt>Candidato IA</dt><dd>{{ $record->candidate_id ?? '—' }}</dd></div>
                <div><dt>Localizado</dt><dd>{{ $p['localizado'] ?? '—' }}</dd></div>
                <div><dt>Especie</dt><dd>{{ ['PL'=>'Pinus lawsonii','QR'=>'Quercus rugosa','O'=>'Otra','ND'=>'No determinada'][$p['especie'] ?? ''] ?? '—' }}</dd></div>
                <div><dt>Estado del árbol</dt><dd>{{ $p['estado'] ?? '—' }}</dd></div>
                <div><dt>Misión UUID</dt><dd class="cr-mono">{{ $p['mission_uuid'] ?? '—' }}</dd></div>
                <div><dt>Detección de origen</dt><dd class="cr-mono">{{ $p['detection_ref'] ?? '—' }}</dd></div>
            </dl>
        </article>
        <article class="cr-panel">
            <h2>Mediciones dasométricas</h2>
            <dl class="cr-data-grid">
                @foreach(['dap_cm'=>'DAP a 1.30 m (cm)','altura_m'=>'Altura total (m)','conf_altura'=>'Confianza altura','r_n_m'=>'Radio norte (m)','r_s_m'=>'Radio sur (m)','r_e_m'=>'Radio este (m)','r_o_m'=>'Radio oeste (m)','dominancia'=>'Dominancia','solapamiento'=>'Solapamiento','visibilidad'=>'Visibilidad'] as $key=>$name)
                    <div><dt>{{ $name }}</dt><dd>{{ $p[$key] ?? '—' }}</dd></div>
                @endforeach
            </dl>
        </article>
        <article class="cr-panel">
            <h2>Georreferenciación y fechas</h2>
            <dl class="cr-data-grid">
                @foreach(['latitud'=>'Latitud (WGS84)','longitud'=>'Longitud (WGS84)','gps_precision_m'=>'Precisión GNSS reportada (m)','gps_fecha'=>'Fecha GPS','gps_metodo'=>'Método GPS','motivo_sin_medicion'=>'Motivo de valores faltantes'] as $key=>$name)
                    <div><dt>{{ $name }}</dt><dd>{{ $p[$key] ?? '—' }}</dd></div>
                @endforeach
                <div><dt>Fecha capturada en dispositivo</dt><dd>{{ $record->client_created_at?->format('d/m/Y H:i:s') ?? '—' }}</dd></div>
                <div><dt>Sincronización con Hostinger</dt><dd>{{ $record->synced_at?->format('d/m/Y H:i:s') ?? '—' }}</dd></div>
                <div><dt>Observaciones</dt><dd class="cr-pre">{{ $p['observaciones'] ?? '—' }}</dd></div>
            </dl>
        </article>
        <article class="cr-panel">
            <h2>Revisión científica</h2>
            <p class="cr-muted">Revisar no cambia ninguna medición enviada desde campo. Los avisos son criterios operativos, no pruebas de error.</p>
            @if(count($warnings))
                <div class="cr-warning"><strong>Puntos para revisar:</strong><ul>@foreach($warnings as $w)<li>{{ $w }}</li>@endforeach</ul></div>
            @else <div class="cr-notice cr-ok">No se activaron avisos automáticos básicos. Esto no equivale a validación científica.</div>
            @endif
            <form method="POST" action="{{ route('campo.records.review', ['record' => $record->id]) }}" class="cr-review-form">
                @csrf
                <label>Estado de revisión
                    <select name="review_status" required>
                        @foreach(['pendiente'=>'Pendiente','en_revision'=>'En revisión','validado'=>'Validado científicamente','observado'=>'Observado / requiere corrección'] as $key=>$name)
                            <option value="{{ $key }}" @selected(old('review_status', $record->review_status) === $key)>{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Nota de revisión
                    <textarea name="review_note" maxlength="1000" rows="4" placeholder="Motivo, instrumento, evidencia adicional o incidencia…">{{ old('review_note', $record->review_note) }}</textarea>
                </label>
                <button type="submit" class="cr-btn">Guardar revisión</button>
                <p class="cr-muted">Para «Observado» se requiere una nota. Los valores originales quedan intactos.</p>
            </form>
        </article>
    </section>
    <section class="cr-panel">
        <h2>Historial de revisión</h2>
        @forelse($events as $ev)
            <div class="cr-event"><strong>{{ $ev->reviewer_name }}</strong> · {{ $ev->created_at }} · {{ $ev->previous_status }} → {{ $ev->new_status }}
                @if($ev->note)<p>{{ $ev->note }}</p>@endif
            </div>
        @empty <p class="cr-muted">Aún no hay revisiones registradas.</p> @endforelse
        <details class="cr-technical"><summary>Integridad y datos originales</summary>
            <p>SHA-256 del payload enviado: <code>{{ $record->payload_sha256 }}</code></p>
            <p>La ficha no modifica el JSON original. Este hash procede de la sincronización inicial; no constituye por sí solo verificación contra campo.</p>
        </details>
    </section>
</main>
@endsection
