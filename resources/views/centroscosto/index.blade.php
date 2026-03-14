@extends('layouts.principal')

@section('title', 'Administración de Centros de Costo')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Administración de Centros de Costo</h1>

    <p>
        <a class="btn btn-dark" href="{{ route('centroscosto.crear') }}">
            <i class="bi bi-plus-circle"></i> Nuevo Centro de Costo
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
                    <th style="white-space: nowrap;">Código</th>
                    <th style="white-space: nowrap;">Nombre</th>
                    <th style="white-space: nowrap;">Descripción</th>
                    <th style="white-space: nowrap;">Estado</th>
                    <th style="white-space: nowrap;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($centros as $centro)
                    <tr>
                        <td>{{ $centro->IdCentroCosto }}</td>
                        <td><strong>{{ $centro->Codigo }}</strong></td>
                        <td>{{ $centro->Nombre }}</td>
                        <td>{{ $centro->Descripcion ?? '—' }}</td>
                        <td>
                            @if($centro->Estado == 1)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            <div class="btn-group" role="group">
                                <a class="btn btn-sm btn-outline-dark" 
                                   href="{{ route('centroscosto.editar', $centro->IdCentroCosto) }}"
                                   title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminar"
                                        data-id="{{ $centro->IdCentroCosto }}"
                                        data-nombre="{{ $centro->Nombre }}"
                                        data-codigo="{{ $centro->Codigo }}"
                                        title="Eliminar">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay centros de costo registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <nav aria-label="Paginación" class="mt-4">
        {{ $centros->links('pagination::bootstrap-5') }}
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
                    ¿Realmente desea eliminar el centro de costo <strong id="nombreCentro"></strong> 
                    (Código: <span id="codigoCentro"></span>)?
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
        var centroId = button.getAttribute('data-id');
        var centroNombre = button.getAttribute('data-nombre');
        var centroCodigo = button.getAttribute('data-codigo');
        
        document.getElementById('nombreCentro').textContent = centroNombre;
        document.getElementById('codigoCentro').textContent = centroCodigo;
        
        var form = document.getElementById('formEliminar');
        form.action = '{{ route("centroscosto.eliminar", ":id") }}'.replace(':id', centroId);
    });
});
</script>
@endsection