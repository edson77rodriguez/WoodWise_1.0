@extends('layouts.uav')

@section('title', 'SIGMAD Campo · Captura forestal')

@push('styles')
<link rel="stylesheet" href="{{ asset('campo/campo.css') }}">
@endpush

@section('content')
<section class="campo-app" id="campo-root"><p>Preparando inventario digital…</p></section>
<script>
window.SIGMAD_FIELD_BOOT = @json($campoBootstrap);
</script>
<script src="{{ asset('campo/campo.js') }}" defer></script>
@endsection
