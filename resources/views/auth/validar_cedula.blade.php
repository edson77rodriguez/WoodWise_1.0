@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Validación de Cédula Profesional</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('confirmar.cedula') }}">
        @csrf
        <div class="mb-3">
            <label for="cedula" class="form-label">Número de Cédula Profesional</label>
            <input type="text" id="cedula" name="cedula" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Validar y Continuar</button>
    </form>
</div>
@endsection
