@extends('layouts.login')

@section('content')
<div class="card shadow-lg p-4" style="max-width: 400px; width: 100%; background-color: #e6e6e6;">
    <div class="text-center">
        <img src="/images/logo2.png" alt="Logo" class="img-fluid mb-3" style="max-height: 150px;" />
        <h4 class="card-title mb-3">Iniciar sesión</h4>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <div class="form-group mb-3">
            <label for="usuario" class="form-label">Usuario</label>
            <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Ingrese su usuario" required>
        </div>
        <div class="form-group mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Ingrese su contraseña" required>
        </div>
        <button type="submit" class="btn btn-secondary w-100">Aceptar</button>
    </form>
</div>
@endsection
