@extends('layouts.principal')

@section('title', 'Editar Centro de Costo')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Editar Centro de Costo</h1>

    <div class="d-flex mb-3 gap-2">
        <a href="{{ route('centroscosto.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Regresar
        </a>
        <button type="submit" class="btn btn-dark" form="formEditarCentro">
            <i class="bi bi-save"></i> Guardar
        </button>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="background-color: #d9d9d9;">
                <div class="card-body">
                    <form id="formEditarCentro" action="{{ route('centroscosto.actualizar', $centro->IdCentroCosto) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="id" class="form-label"><strong>ID Centro de Costo</strong></label>
                            <input type="text" class="form-control" value="{{ $centro->IdCentroCosto }}" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label for="codigo" class="form-label"><strong>Código</strong></label>
                            <input type="text" 
                                   class="form-control @error('codigo') is-invalid @enderror" 
                                   id="codigo" 
                                   name="codigo" 
                                   value="{{ old('codigo', $centro->Codigo) }}"
                                   maxlength="20"
                                   required>
                            @error('codigo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label"><strong>Nombre</strong></label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre', $centro->Nombre) }}"
                                   maxlength="100"
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label"><strong>Descripción</strong> <small class="text-muted">(opcional)</small></label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3">{{ old('descripcion', $centro->Descripcion) }}</textarea>
                            @error('descripcion')
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
                                <option value="1" {{ old('estado', $centro->Estado) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado', $centro->Estado) == 0 ? 'selected' : '' }}>Inactivo</option>
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