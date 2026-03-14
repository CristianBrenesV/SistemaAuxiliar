@extends('layouts.principal')

@section('title', 'Contactos de Tercero')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">
        Contactos de: <span class="text-primary">{{ $tercero->Nombre }}</span>
    </h1>

    <div class="d-flex mb-3 gap-2">
        <a href="{{ route('terceros.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Terceros
        </a>
        <a href="{{ route('terceros.contactos.crear', $tercero->IdTercero) }}" class="btn btn-dark">
            <i class="bi bi-plus-circle"></i> Nuevo Contacto
        </a>
    </div>

    @if(session('mensaje'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('mensaje') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Cargo</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contactos as $contacto)
                    <tr>
                        <td>{{ $contacto->IdContacto }}</td>
                        <td><strong>{{ $contacto->NombreContacto }}</strong></td>
                        <td>{{ $contacto->Cargo ?? '—' }}</td>
                        <td>{{ $contacto->Email ?? '—' }}</td>
                        <td>{{ $contacto->Telefono ?? '—' }}</td>
                        <td>
                            @php
                                $badgeClass = match($contacto->TipoContacto) {
                                    'Principal' => 'bg-success',
                                    'Facturación' => 'bg-info text-dark',
                                    'Cobros' => 'bg-warning text-dark',
                                    'Soporte' => 'bg-primary',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $contacto->TipoContacto }}</span>
                        </td>
                        <td>
                            @if($contacto->Estado)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            <div class="btn-group" role="group">
                                <a class="btn btn-sm btn-outline-dark" 
                                   href="{{ route('terceros.contactos.editar', [$tercero->IdTercero, $contacto->IdContacto]) }}"
                                   title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminar"
                                        data-id="{{ $contacto->IdContacto }}"
                                        data-nombre="{{ $contacto->NombreContacto }}"
                                        title="Eliminar">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay contactos registrados para este tercero</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="" id="formEliminar">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ¿Realmente desea eliminar el contacto <strong id="nombreContacto"></strong>?
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
document.addEventListener('DOMContentLoaded', function() {
    var modalEliminar = document.getElementById('modalEliminar');
    modalEliminar.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var contactoId = button.getAttribute('data-id');
        var contactoNombre = button.getAttribute('data-nombre');
        
        document.getElementById('nombreContacto').textContent = contactoNombre;
        
        var form = document.getElementById('formEliminar');
        form.action = '{{ route("terceros.contactos.eliminar", [$tercero->IdTercero, ":id"]) }}'.replace(':id', contactoId);
    });
});
</script>
@endsection