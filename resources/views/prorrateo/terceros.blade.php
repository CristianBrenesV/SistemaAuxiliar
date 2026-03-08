@extends('layouts.principal')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mt-4 text-white">Prorrateo por Terceros</h2>
        <a href="{{ route('asientos.index') }}" class="btn btn-outline-light mt-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
    
    {{-- Info del Asiento --}}
    <div class="card bg-dark text-white border-secondary mb-4 shadow">
        <div class="card-header border-secondary bg-dark d-flex justify-content-between">
            <span><i class="bi bi-person-badge me-1"></i> Asiento: <strong>{{ $asiento->Consecutivo }}</strong></span>
            <span class="text-muted small">Ref: {{ $asiento->Referencia }}</span>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label class="fw-bold text-success">1. Seleccione la Línea del Asiento:</label>
                    <select id="select_detalle" class="form-select bg-secondary text-white border-0 mt-1" onchange="actualizarMontoObjetivo()">
                        <option value="" data-monto="0">-- Seleccione una cuenta para prorratear --</option>
                        @foreach($asiento->detalles as $det)
                            <option value="{{ $det->IdAsientoDetalle }}" data-monto="{{ $det->Monto }}">
                                {{ $det->IdCuentaContable }} - ₡{{ number_format($det->Monto, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 text-end">
                    <h4 class="mb-0 text-info">Monto Línea: <span id="display_monto_objetivo">₡0.00</span></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Formulario de Entrada --}}
        <div class="col-md-4">
            <div class="card bg-dark text-white border-secondary h-100 shadow">
                <div class="card-header border-secondary text-success fw-bold">2. Asignar Tercero</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Buscar Tercero</label>
                        <select id="select_tercero" class="form-select bg-secondary text-white border-0">
                            <option value="">-- Seleccione un Tercero --</option>
                            @foreach($terceros as $t)
                                <option value="{{ $t->IdTercero }}">
                                    {{ $t->Identificacion }} | {{ $t->Nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto Prorrateado</label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary text-white border-0">₡</span>
                            <input type="number" id="input_monto" class="form-control bg-secondary text-white border-0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Referencia / Nota (Opcional)</label>
                        <textarea id="input_nota" class="form-control bg-secondary text-white border-0" rows="2"></textarea>
                    </div>
                    <button type="button" onclick="agregarLinea()" class="btn btn-success w-100 shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Agregar a la Lista
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabla de Distribución --}}
        <div class="col-md-8">
            <form action="{{ route('prorrateo.guardar') }}" method="POST">
                @csrf
                <input type="hidden" name="es_tercero" value="1">
                <input type="hidden" name="id_detalle" id="hidden_id_detalle">
                
                <div class="card bg-dark text-white border-secondary shadow">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Identificación / Nombre</th>
                                        <th class="text-end">Monto</th>
                                        <th class="text-end">Porcentaje</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_prorrateo">
                                    <tr><td colspan="4" class="text-center text-muted py-4">Sin terceros asignados</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer border-secondary bg-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-start">
                                <span class="text-muted">Pendiente: </span>
                                <span id="diferencia_pendiente" class="fw-bold text-warning">₡0.00</span>
                            </div>
                            <div class="text-end">
                                <span class="me-2">Total Asignado:</span>
                                <span id="total_asignado" class="h5 mb-0 text-success">₡0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" id="btn_guardar" class="btn btn-primary btn-lg px-5 shadow" disabled>
                        <i class="bi bi-save me-1"></i> Guardar Distribución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let lineas = [];
    let montoObjetivo = 0;

    function actualizarMontoObjetivo() {
        const select = document.getElementById('select_detalle');
        const selectedOption = select.options[select.selectedIndex];
        
        montoObjetivo = parseFloat(selectedOption.getAttribute('data-monto')) || 0;
        document.getElementById('hidden_id_detalle').value = select.value;
        document.getElementById('display_monto_objetivo').innerText = `₡${montoObjetivo.toLocaleString('es-CR', {minimumFractionDigits: 2})}`;
        
        lineas = []; 
        renderizarTabla();
    }

    function agregarLinea() {
        const selectTercero = document.getElementById('select_tercero');
        const monto = parseFloat(document.getElementById('input_monto').value);
        const nota = document.getElementById('input_nota').value;

        if (!montoObjetivo || montoObjetivo <= 0) {
            alert("Seleccione primero una línea de asiento.");
            return;
        }
        if (!selectTercero.value || isNaN(monto) || monto <= 0) {
            alert("Seleccione un tercero y un monto válido.");
            return;
        }

        const sumaActual = lineas.reduce((acc, curr) => acc + curr.monto, 0);
        // Margen de error de 0.01 para precisión de punto flotante
        if ((sumaActual + monto) > (montoObjetivo + 0.01)) {
            alert("La suma de prorrateos excede el monto de la línea.");
            return;
        }

        lineas.push({
            id: selectTercero.value,
            nombre: selectTercero.options[selectTercero.selectedIndex].text,
            monto: monto,
            nota: nota
        });

        document.getElementById('input_monto').value = "";
        document.getElementById('input_nota').value = "";
        renderizarTabla();
    }

    function renderizarTabla() {
        const tbody = document.getElementById('tabla_prorrateo');
        let html = "";
        let suma = 0;

        lineas.forEach((l, index) => {
            suma += l.monto;
            const porcentaje = (l.monto / montoObjetivo) * 100;
            
            html += `
                <tr>
                    <td>
                        <div class="fw-bold">${l.nombre}</div>
                        <div class="small text-muted">${l.nota}</div>
                        <input type="hidden" name="distribucion[${index}][id_destino]" value="${l.id}">
                        <input type="hidden" name="distribucion[${index}][nota]" value="${l.nota}">
                    </td>
                    <td class="text-end fw-bold">₡${l.monto.toLocaleString('es-CR', {minimumFractionDigits: 2})} 
                        <input type="hidden" name="distribucion[${index}][monto]" value="${l.monto}">
                    </td>
                    <td class="text-end text-muted small">
                        ${porcentaje.toFixed(2)}%
                        <input type="hidden" name="distribucion[${index}][porcentaje]" value="${porcentaje.toFixed(2)}">
                    </td>
                    <td class="text-center">
                        <button type="button" onclick="eliminar(${index})" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>`;
        });

        tbody.innerHTML = html || '<tr><td colspan="4" class="text-center text-muted py-4">Sin terceros asignados</td></tr>';
        
        const diferencia = montoObjetivo - suma;
        document.getElementById('total_asignado').innerText = `₡${suma.toLocaleString('es-CR', {minimumFractionDigits: 2})}`;
        document.getElementById('diferencia_pendiente').innerText = `₡${diferencia.toLocaleString('es-CR', {minimumFractionDigits: 2})}`;
        
        // Habilitar botón solo si la diferencia es 0 (margen 0.01)
        document.getElementById('btn_guardar').disabled = (Math.abs(diferencia) > 0.01 || montoObjetivo === 0);
    }

    function eliminar(index) {
        lineas.splice(index, 1);
        renderizarTabla();
    }
</script>
@endpush