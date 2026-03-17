@extends('layouts.principal')

@section('title', 'Administración de Terceros')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Administración de Terceros</h1>

    <p>
        <a class="btn btn-dark" href="{{ route('terceros.crear') }}">
            <i class="bi bi-plus-circle"></i> Nuevo Tercero
        </a>
    </p>

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
                    <th style="white-space: nowrap;">ID</th>
                    <th style="white-space: nowrap;">Identificación</th>
                    <th style="white-space: nowrap;">Nombre</th>
                    <th style="white-space: nowrap;">Tipo</th>
                    <th style="white-space: nowrap;">Email</th>
                    <th style="white-space: nowrap;">Teléfono</th>
                    <th style="white-space: nowrap;">Estado</th>
                    <th style="white-space: nowrap;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($terceros as $tercero)
                    <tr>
                        <td>{{ $tercero->IdTercero }}</td>
                        <td>{{ $tercero->Identificacion }}</td>
                        <td>{{ $tercero->Nombre }}</td>
                        <td>
                            @php
                                $badgeClass = match($tercero->TipoTercero) {
                                    'Cliente' => 'bg-success',
                                    'Proveedor' => 'bg-warning text-dark',
                                    'Empleado' => 'bg-info text-dark',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $tercero->TipoTercero }}</span>
                        </td>
                        <td>{{ $tercero->Email ?? '—' }}</td>
                        <td>{{ $tercero->Telefono ?? '—' }}</td>
                        <td>
                            @if($tercero->Estado == 1)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            <div class="btn-group" role="group">
                                <a class="btn btn-sm btn-outline-dark" 
                                   href="{{ route('terceros.editar', $tercero->IdTercero) }}"
                                   title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <a class="btn btn-sm btn-outline-info" 
                                   href="{{ route('terceros.direcciones.index', $tercero->IdTercero) }}"
                                   title="Direcciones">
                                    <i class="bi bi-geo-alt"></i>
                                </a>

                                <a class="btn btn-sm btn-outline-primary" 
                                   href="{{ route('terceros.contactos.index', $tercero->IdTercero) }}"
                                   title="Contactos">
                                    <i class="bi bi-person-lines-fill"></i>
                                </a>

                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminar"
                                        data-id="{{ $tercero->IdTercero }}"
                                        data-nombre="{{ $tercero->Nombre }}"
                                        title="Eliminar">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay terceros registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <nav aria-label="Paginación" class="mt-4">
        {{ $terceros->links('pagination::bootstrap-5') }}
    </nav>
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
                    ¿Realmente desea eliminar el tercero <strong id="nombreTercero"></strong>?
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
        var terceroId = button.getAttribute('data-id');
        var terceroNombre = button.getAttribute('data-nombre');
        
        document.getElementById('nombreTercero').textContent = terceroNombre;
        
        var form = document.getElementById('formEliminar');
        form.action = '{{ route("terceros.eliminar", ":id") }}'.replace(':id', terceroId);
    });
});
</script>
@endsection