@extends('layouts.principal')

@section('title', 'Editar Tercero')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Editar Tercero</h1>

    <div class="d-flex mb-3 gap-2">
        <a href="{{ route('terceros.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Regresar
        </a>
        <button type="submit" class="btn btn-dark" form="formEditarTercero">
            <i class="bi bi-save"></i> Guardar
        </button>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="background-color: #d9d9d9;">
                <div class="card-body">
                    <form id="formEditarTercero" action="{{ route('terceros.actualizar', $tercero->IdTercero) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="id" class="form-label"><strong>ID Tercero</strong></label>
                            <input type="text" class="form-control" value="{{ $tercero->IdTercero }}" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label for="identificacion" class="form-label"><strong>Identificación</strong></label>
                            <input type="text" 
                                   class="form-control @error('identificacion') is-invalid @enderror" 
                                   id="identificacion" 
                                   name="identificacion" 
                                   value="{{ old('identificacion', $tercero->Identificacion) }}"
                                   maxlength="20"
                                   required>
                            @error('identificacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label"><strong>Nombre Completo / Razón Social</strong></label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre', $tercero->Nombre) }}"
                                   maxlength="150"
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tipo" class="form-label"><strong>Tipo</strong></label>
                            <select class="form-select @error('tipo') is-invalid @enderror" 
                                    id="tipo" 
                                    name="tipo" 
                                    required>
                                <option value="">-- Seleccione tipo --</option>
                                <option value="Cliente" {{ old('tipo', $tercero->TipoTercero) == 'Cliente' ? 'selected' : '' }}>Cliente</option>
                                <option value="Proveedor" {{ old('tipo', $tercero->TipoTercero) == 'Proveedor' ? 'selected' : '' }}>Proveedor</option>
                                <option value="Empleado" {{ old('tipo', $tercero->TipoTercero) == 'Empleado' ? 'selected' : '' }}>Empleado</option>
                                <option value="Otro" {{ old('tipo', $tercero->TipoTercero) == 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label"><strong>Email</strong> <small class="text-muted">(opcional)</small></label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $tercero->Email) }}"
                                   maxlength="100">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label"><strong>Teléfono</strong> <small class="text-muted">(opcional)</small></label>
                            <input type="text" 
                                   class="form-control @error('telefono') is-invalid @enderror" 
                                   id="telefono" 
                                   name="telefono" 
                                   value="{{ old('telefono', $tercero->Telefono) }}"
                                   maxlength="20">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label"><strong>Estado</strong></label>
                            <select class="form-select @error('estado') is-invalid @enderror" 
                                    id="estado" 
                                    name="estado" 
                                    required>
                                <option value="">-- Seleccione estado --</option>
                                <option value="1" {{ old('estado', $tercero->Estado) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado', $tercero->Estado) == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection