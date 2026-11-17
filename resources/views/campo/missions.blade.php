@extends('layouts.uav')
@section('title', 'SIGMAD Campo · Preparar misiones')
@push('styles')
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/vendor/ol.css') }}">
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/misiones.css') }}">
@endpush
@section('content')
<div class="cmissions">
    <header class="cmissions-title">
        <div><h1>Preparar misión de campo</h1><p>Selecciona copas del análisis UAV y asígnalas a una parcela. No se modifican las predicciones originales.</p></div>
        <a href="{{ route('campo.index') }}">Volver a SIGMAD Campo</a>
    </header>
    @if(session('status'))<div class="cmissions-ok">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="cmissions-error">{{ implode(' · ', $errors->all()) }}</div>@endif
    <form action="{{ route('campo.missions.store') }}" method="POST" id="mission-form" class="cmissions-layout">
        @csrf
        <div class="cmissions-panel">
            <label>Nombre de la misión<input name="title" required maxlength="120" value="{{ old('title', 'Levantamiento piloto') }}"></label>
            <label>Parcela asignada<select name="id_parcela" required>
                <option value="">Seleccionar parcela…</option>
                @foreach($parcelas as $p)<option value="{{ $p->id_parcela }}" @selected(old('id_parcela') == $p->id_parcela)>{{ $p->nom_parcela }}</option>@endforeach
            </select></label>
            <label>Análisis completado<select name="analysis_uuid" id="mission-analysis" required>
                <option value="">Seleccionar vuelo…</option>
                @foreach($jobs as $j)<option value="{{ $j['uuid'] }}">{{ $j['label'] }}</option>@endforeach
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
        @forelse($missions as $m)
            <p><strong>{{ $m->title }}</strong> · Parcela {{ $m->id_parcela }} · {{ $m->selected_count }} copas · {{ $m->status }} · {{ $m->created_at?->format('Y-m-d') }} · <a href="{{ route('campo.mission.export', ['mission' => $m->uuid]) }}">Descargar JSON para mapa HD</a></p>
        @empty <p>Todavía no hay misiones.</p> @endforelse
    </section>
</div>
<script>window.SIGMAD_MISSIONS_BOOT = @json($jobs);</script>
<script src="{{ asset('sigmad-campo-assets/vendor/proj4.js') }}" defer></script>
<script src="{{ asset('sigmad-campo-assets/vendor/ol.js') }}" defer></script>
<script src="{{ asset('sigmad-campo-assets/misiones.js') }}" defer></script>
@endsection
