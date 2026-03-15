@extends('layouts.principal')

@section('title', 'Agregar Contacto')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">
        Agregar Contacto para: <span class="text-primary">{{ $tercero->Nombre }}</span>
    </h1>

    <div class="d-flex mb-3 gap-2">
        <a href="{{ route('terceros.contactos.index', $tercero->IdTercero) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Regresar a Contactos
        </a>
        <button type="submit" class="btn btn-dark" form="formCrearContacto">
            <i class="bi bi-save"></i> Guardar
        </button>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="background-color: #d9d9d9;">
                <div class="card-body">
                    <form id="formCrearContacto" 
                          action="{{ route('terceros.contactos.guardar', $tercero->IdTercero) }}" 
                          method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nombre_contacto" class="form-label"><strong>Nombre del Contacto</strong></label>
                            <input type="text" 
                                   class="form-control @error('nombre_contacto') is-invalid @enderror" 
                                   id="nombre_contacto" 
                                   name="nombre_contacto" 
                                   value="{{ old('nombre_contacto') }}"
                                   maxlength="100"
                                   required>
                            @error('nombre_contacto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cargo" class="form-label"><strong>Cargo / Rol</strong> <small class="text-muted">(opcional)</small></label>
                            <input type="text" 
                                   class="form-control @error('cargo') is-invalid @enderror" 
                                   id="cargo" 
                                   name="cargo" 
                                   value="{{ old('cargo') }}"
                                   maxlength="100"
                                   placeholder="Ej: Gerente de Ventas">
                            @error('cargo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label"><strong>Email</strong> <small class="text-muted">(opcional)</small></label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
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
                                   value="{{ old('telefono') }}"
                                   maxlength="20">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tipo_contacto" class="form-label"><strong>Tipo de Contacto</strong></label>
                            <select class="form-select @error('tipo_contacto') is-invalid @enderror" 
                                    id="tipo_contacto" 
                                    name="tipo_contacto" 
                                    required>
                                <option value="">-- Seleccione tipo --</option>
                                <option value="Principal" {{ old('tipo_contacto') == 'Principal' ? 'selected' : '' }}>Principal</option>
                                <option value="Facturación" {{ old('tipo_contacto') == 'Facturación' ? 'selected' : '' }}>Facturación</option>
                                <option value="Cobros" {{ old('tipo_contacto') == 'Cobros' ? 'selected' : '' }}>Cobros</option>
                                <option value="Soporte" {{ old('tipo_contacto') == 'Soporte' ? 'selected' : '' }}>Soporte</option>
                                <option value="Otro" {{ old('tipo_contacto') == 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('tipo_contacto')
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
                                <option value="1" {{ old('estado') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Inactivo</option>
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