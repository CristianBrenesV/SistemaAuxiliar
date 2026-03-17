@extends('layouts.principal')

@section('title', 'Editar Dirección')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">
        Editar Dirección para: <span class="text-primary">{{ $tercero->Nombre }}</span>
    </h1>

    <div class="d-flex mb-3 gap-2">
        <a href="{{ route('terceros.direcciones.index', $tercero->IdTercero) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Regresar a Direcciones
        </a>
        <button type="submit" class="btn btn-dark" form="formEditarDireccion">
            <i class="bi bi-save"></i> Guardar
        </button>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="background-color: #d9d9d9;">
                <div class="card-body">
                    <form id="formEditarDireccion" 
                          action="{{ route('terceros.direcciones.actualizar', [$tercero->IdTercero, $direccion->IdDireccion]) }}" 
                          method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="alias" class="form-label"><strong>Alias / Nombre</strong></label>
                            <input type="text" 
                                   class="form-control @error('alias') is-invalid @enderror" 
                                   id="alias" 
                                   name="alias" 
                                   value="{{ old('alias', $direccion->Alias) }}"
                                   maxlength="100"
                                   required>
                            @error('alias')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="provincia" class="form-label"><strong>Provincia</strong></label>
                                <input type="text" 
                                       class="form-control @error('provincia') is-invalid @enderror" 
                                       id="provincia" 
                                       name="provincia" 
                                       value="{{ old('provincia', $direccion->Provincia) }}"
                                       maxlength="50"
                                       required>
                                @error('provincia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="canton" class="form-label"><strong>Cantón</strong></label>
                                <input type="text" 
                                       class="form-control @error('canton') is-invalid @enderror" 
                                       id="canton" 
                                       name="canton" 
                                       value="{{ old('canton', $direccion->Canton) }}"
                                       maxlength="50"
                                       required>
                                @error('canton')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="distrito" class="form-label"><strong>Distrito</strong></label>
                                <input type="text" 
                                       class="form-control @error('distrito') is-invalid @enderror" 
                                       id="distrito" 
                                       name="distrito" 
                                       value="{{ old('distrito', $direccion->Distrito) }}"
                                       maxlength="50"
                                       required>
                                @error('distrito')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion_exacta" class="form-label"><strong>Dirección Exacta</strong></label>
                            <textarea class="form-control @error('direccion_exacta') is-invalid @enderror" 
                                      id="direccion_exacta" 
                                      name="direccion_exacta" 
                                      rows="2"
                                      required>{{ old('direccion_exacta', $direccion->DireccionExacta) }}</textarea>
                            @error('direccion_exacta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="es_principal" 
                                       name="es_principal" 
                                       value="1"
                                       {{ old('es_principal', $direccion->EsPrincipal) ? 'checked' : '' }}>
                                <label class="form-check-label" for="es_principal">
                                    <strong>Dirección Principal</strong>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label"><strong>Estado</strong></label>
                            <select class="form-select @error('estado') is-invalid @enderror" 
                                    id="estado" 
                                    name="estado" 
                                    required>
                                <option value="">-- Seleccione estado --</option>
                                <option value="1" {{ old('estado', $direccion->Estado) == 1 ? 'selected' : '' }}>Activa</option>
                                <option value="0" {{ old('estado', $direccion->Estado) == 0 ? 'selected' : '' }}>Inactiva</option>
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