@extends('layouts.principal')

@section('title', 'Editar Usuario')

@section('content')
<h1 class="mt-4">Editar Usuario</h1>

<div class="d-flex mb-3 gap-2">
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Regresar
    </a>

    <button type="submit" class="btn btn-dark" form="formEditarUsuario">
        <i class="bi bi-save"></i> Guardar
    </button>

    <button type="button" class="btn btn-secondary" onclick="autogenerarClave()">
        <i class="bi bi-key"></i> Autogenerar clave
    </button>
</div>

<div class="container mt-3 d-flex">
    <div class="card shadow-sm border-0 w-50" style="background-color: #d9d9d9;">
        <div class="card-body">

             <form id="formEditarUsuario" action="{{ route('usuarios.actualizar', $usuario->IdUsuario) }}" method="POST" onsubmit="return validarClave()">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="id_usuario" class="form-label"><strong>ID Usuario</strong></label>
                    <input type="text" name="id_usuario" id="id_usuario" class="form-control" value="{{ $usuario->IdUsuario }}" readonly>
                </div>

                <div class="mb-3">
                    <label for="usuario" class="form-label"><strong>Usuario</strong></label>
                    <input type="text" name="usuario" id="usuario" class="form-control" value="{{ old('usuario', $usuario->Usuario) }}">
                    @error('usuario')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="nombre_usuario" class="form-label"><strong>Nombre</strong></label>
                    <input type="text" name="nombre_usuario" id="nombre_usuario" class="form-control" value="{{ old('nombre_usuario', $usuario->NombreUsuario) }}">
                    @error('nombre_usuario')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="apellido_usuario" class="form-label"><strong>Apellido</strong></label>
                    <input type="text" name="apellido_usuario" id="apellido_usuario" class="form-control" value="{{ old('apellido_usuario', $usuario->ApellidoUsuario) }}">
                    @error('apellido_usuario')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="correo_electronico" class="form-label"><strong>Correo Electrónico</strong></label>
                    <input type="email" name="correo_electronico" id="correo_electronico" class="form-control" value="{{ old('correo_electronico', $usuario->CorreoElectronico) }}">
                    @error('correo_electronico')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- CONTRASEÑA -->
                <div class="mb-3 position-relative">
                    <label for="password" class="form-label"><strong>Clave</strong></label>
                    <div class="input-group">
                        <input type="password" name="password" id="Input_NuevaClave" class="form-control">
                        <span class="input-group-text" onclick="togglePassword('Input_NuevaClave')">
                            <i class="bi bi-eye-slash" id="icon_Input_NuevaClave"></i>
                        </span>
                    </div>
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- CONFIRMAR CONTRASEÑA -->
                <div class="mb-3 position-relative">
                    <label for="password_confirmation" class="form-label"><strong>Confirmar Clave</strong></label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="Input_ConfirmarClave" class="form-control">
                        <span class="input-group-text" onclick="togglePassword('Input_ConfirmarClave')">
                            <i class="bi bi-eye-slash" id="icon_Input_ConfirmarClave"></i>
                        </span>
                    </div>
                    <span class="text-danger" id="errorConfirmacion"></span>
                </div>

                <!-- ESTADO -->
                <div class="mb-3">
                    <label for="estado" class="form-label"><strong>Estado</strong></label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">-- Seleccione estado --</option>
                        <option value="Activo" {{ old('estado', $usuario->Estado) == 'Activo' ? 'selected' : '' }}>Activo</option>
                        <option value="Inactivo" {{ old('estado', $usuario->Estado) == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                        <option value="Bloqueado" {{ old('estado', $usuario->Estado) == 'Bloqueado' ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                    @error('estado')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

            </form>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/CrearUsuario.js') }}"></script>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = document.getElementById('icon_' + id);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    }
}

function validarClave() {
    const clave = document.getElementById('Input_NuevaClave').value;
    const confirmar = document.getElementById('Input_ConfirmarClave').value;
    const error = document.getElementById('errorConfirmacion');

    if (clave !== confirmar) {
        error.textContent = 'Las contraseñas no coinciden';
        return false;
    }
    error.textContent = '';
    return true;
}

// Autogenerar clave simple
function autogenerarClave() {
    const clave = Math.random().toString(36).slice(-8);
    document.getElementById('Input_NuevaClave').value = clave;
    document.getElementById('Input_ConfirmarClave').value = clave;
}
</script>
@endsection
