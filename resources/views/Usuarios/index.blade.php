@extends('layouts.principal')

@section('title', 'Administración de Usuarios')

@section('content')
<h1 class="mt-4">Administración de Usuarios</h1>

<p>
    <a class="btn btn-dark" href="{{ route('usuarios.crear') }}">
        <i class="bi bi-plus-circle"></i> Nuevo
    </a>
</p>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th style="white-space: nowrap;">ID</th>
                <th style="white-space: nowrap;">Usuario</th>
                <th style="white-space: nowrap;">Nombre</th>
                <th style="white-space: nowrap;">Apellido</th>
                <th style="white-space: nowrap;">Correo Electrónico</th>
                <th style="white-space: nowrap;">Estado</th>
                <th style="white-space: nowrap;">Rol</th>
                <th style="white-space: nowrap;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $user)
                <tr>
                    <td style="white-space: nowrap;">{{ $user->IdUsuario  }}</td>
                    <td style="white-space: nowrap;">{{ $user->Usuario  }}</td>
                    <td style="white-space: nowrap;">{{ $user->NombreUsuario }}</td>
                    <td style="white-space: nowrap;">{{ $user->ApellidoUsuario  }}</td>
                    <td style="white-space: nowrap;">{{ $user->CorreoElectronico }}</td>

                    <td>
                        <form method="POST" action="{{ route('usuarios.cambiarEstado') }}" class="d-inline">
                            @csrf

                            <input type="hidden" name="IdUsuario" value="{{ $user->IdUsuario }}">

                            <select name="nuevo_estado"
                                    class="form-select form-select-sm"
                                    onchange="this.form.submit()">

                                @foreach (\App\Enums\EstadoUsuario::cases() as $Estado)
                                    <option value="{{ $Estado->value }}"
                                        {{ $Estado->value === $user->Estado ? 'selected' : '' }}>
                                        {{ $Estado->name }}
                                    </option>
                                @endforeach

                            </select>
                        </form>
                    </td>

                    <td>
                        <div class="d-flex flex-column gap-1">
                            @php
                                $roles = explode(', ', $user->Roles); // usar "Roles" como devuelve el SP
                                $badgeColor = fn($rol) => match($rol) {
                                    'Administrador' => 'badge bg-dark text-white',
                                    'Contador' => 'badge bg-secondary text-white',
                                    'Contador Jefe' => 'badge bg-light text-dark border',
                                    default => 'badge bg-light text-dark border',
                                };
                            @endphp
                            @foreach ($roles as $rol)
                                <span class="badge {{ $badgeColor($rol) }} rounded-pill px-3 py-2">{{ $rol }}</span>
                            @endforeach
                        </div>
                    </td>

                    <td style="white-space: nowrap;">
                        <a class="btn btn-sm btn-outline-dark" href="{{ route('usuarios.editar', $user->IdUsuario) }}">
                            <i class="bi bi-pencil-square"></i> Editar
                        </a>

                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('usuarios.cambiarClave', $user->IdUsuario) }}">
                            <i class="bi bi-lock"></i> Cambiar Clave
                        </a>

                        <button type="button"
                                class="btn btn-sm btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEliminar"
                                data-id="{{ $user->IdUsuario }}"
                                data-nombre="{{ $user->Usuario }}">
                            <i class="bi bi-trash3"></i> Eliminar
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Paginación --}}
<nav aria-label="Paginación de usuarios" class="mt-4">
    {{ $usuarios->links('pagination::bootstrap-5') }}
</nav>

{{-- Mensajes --}}
@if(session('mensaje'))
    <div class="alert alert-dark" role="alert">
        {{ session('mensaje') }}
    </div>
@endif

{{-- Modal de Eliminación --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('usuarios.eliminar', ':ID') }}" id="formEliminar">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEliminarLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Realmente desea eliminar el usuario <strong id="nombreUsuario"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
                    <button type="submit" class="btn btn-dark">Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        var modalEliminar = document.getElementById('modalEliminar');
        modalEliminar.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-id');
            var userName = button.getAttribute('data-nombre');

            // Actualizar nombre en el modal
            modalEliminar.querySelector('#nombreUsuario').textContent = userName;

            // Actualizar acción del formulario
            var form = modalEliminar.querySelector('#formEliminar');
            var action = form.getAttribute('action').replace(':ID', userId);
            form.setAttribute('action', action);
        });
    </script>
@endsection
