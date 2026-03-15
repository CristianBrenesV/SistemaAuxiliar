@extends('layouts.principal')

@section('title', 'Direcciones de Tercero')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">
        Direcciones de: <span class="text-primary">{{ $tercero->Nombre }}</span>
    </h1>

    <div class="d-flex mb-3 gap-2">
        <a href="{{ route('terceros.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Terceros
        </a>
        <a href="{{ route('terceros.direcciones.crear', $tercero->IdTercero) }}" class="btn btn-dark">
            <i class="bi bi-plus-circle"></i> Nueva Dirección
        </a>
    </div>

    @if(session('mensaje'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('mensaje') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Alias</th>
                    <th>Provincia</th>
                    <th>Cantón</th>
                    <th>Distrito</th>
                    <th>Dirección Exacta</th>
                    <th>Principal</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($direcciones as $direccion)
                    <tr>
                        <td>{{ $direccion->IdDireccion }}</td>
                        <td><strong>{{ $direccion->Alias }}</strong></td>
                        <td>{{ $direccion->Provincia }}</td>
                        <td>{{ $direccion->Canton }}</td>
                        <td>{{ $direccion->Distrito }}</td>
                        <td>{{ $direccion->DireccionExacta }}</td>
                        <td class="text-center">
                            @if($direccion->EsPrincipal)
                                <span class="badge bg-success">Sí</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            @if($direccion->Estado)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            <div class="btn-group" role="group">
                                <a class="btn btn-sm btn-outline-dark" 
                                   href="{{ route('terceros.direcciones.editar', [$tercero->IdTercero, $direccion->IdDireccion]) }}"
                                   title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminar"
                                        data-id="{{ $direccion->IdDireccion }}"
                                        data-alias="{{ $direccion->Alias }}"
                                        title="Eliminar">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No hay direcciones registradas para este tercero</td>
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
                    ¿Realmente desea eliminar la dirección <strong id="aliasDireccion"></strong>?
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
        var direccionId = button.getAttribute('data-id');
        var direccionAlias = button.getAttribute('data-alias');
        
        document.getElementById('aliasDireccion').textContent = direccionAlias;
        
        var form = document.getElementById('formEliminar');
        form.action = '{{ route("terceros.direcciones.eliminar", [$tercero->IdTercero, ":id"]) }}'.replace(':id', direccionId);
    });
});
</script>
@endsection