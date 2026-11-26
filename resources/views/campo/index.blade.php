@extends('layouts.uav')

@section('title', 'SIGMAD Campo · Mapa e inventario forestal')

@push('styles')
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/vendor/ol.css') }}">
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/campo.css') }}?v=0600">
@endpush

@section('content')
<section class="campo-app" id="campo-root"><p>Preparando inventario digital…</p></section>
<script>
window.SIGMAD_FIELD_BOOT = @json($campoBootstrap);
</script>
<script src="{{ asset('sigmad-campo-assets/vendor/proj4.js') }}" defer></script>
<script src="{{ asset('sigmad-campo-assets/vendor/ol.js') }}" defer></script>
<script src="{{ asset('sigmad-campo-assets/campo-raster-integridad.js') }}?v=0600" defer></script>
<script src="{{ asset('sigmad-campo-assets/campo-mapa.js') }}?v=0600" defer></script>
<script src="{{ asset('sigmad-campo-assets/campo.js') }}?v=0600" defer></script>
@endsection
