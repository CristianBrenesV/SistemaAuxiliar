@extends('layouts.principal')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mt-4 text-dark fw-bold">Prorrateo por Centros de Costo</h2>
        <a href="{{ route('asientos.index') }}" class="btn btn-outline-secondary mt-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
    
    {{-- Info del Asiento --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <span><i class="bi bi-info-circle me-1"></i> Asiento: <strong>{{ $detalle->asiento->Consecutivo }}</strong></span>
            <span class="text-muted small">Ref: {{ $detalle->asiento->Referencia }}</span>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label class="fw-bold text-muted">Línea del Asiento seleccionada:</label>
                    <select id="select_detalle" class="form-select">
                        <option value="{{ $detalle->IdAsientoDetalle }}" data-monto="{{ $detalle->Monto }}" selected>
                            Cuenta: {{ $detalle->IdCuentaContable }} - Monto: ₡{{ number_format($detalle->Monto, 2) }}
                        </option>
                    </select>
                </div>
                <div class="col-md-6 text-end">
                    <h4 class="mb-0 text-dark fw-bold">Monto a Distribuir: <span id="display_monto_objetivo">₡{{ number_format($detalle->Monto, 2) }}</span></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Formulario de Entrada --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light fw-bold">1. Asignar Centro de Costo</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Centro de Costo</label>
                        <select id="select_centro" class="form-select">
                            <option value="">-- Seleccione un Centro --</option>
                            @foreach($centros as $c)
                                <option value="{{ $c->IdCentroCosto }}">{{ $c->Codigo }} - {{ $c->Nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto</label>
                        <div class="input-group">
                            <span class="form-control">₡</span>
                            <input type="number" id="input_monto" class="form-control bg-secondary text-white border-0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nota / Descripción (Opcional)</label>
                        <textarea id="input_nota" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="button" onclick="agregarLinea()" class="btn btn-dark w-100 shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Agregar a la Lista
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabla de Distribución --}}
        <div class="col-md-8">
            {{-- Mostramos errores de validación del Form Request si existen --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('prorrateo.guardar') }}" method="POST">
                @csrf
                <input type="hidden" name="es_tercero" value="0">
                <input type="hidden" name="id_detalle" id="hidden_id_detalle" value="{{ $detalle->IdAsientoDetalle }}">
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Centro de Costo</th>
                                    <th>Nota</th>
                                    <th class="text-end">Monto</th>
                                    <th class="text-end">Porcentaje</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_prorrateo">
                                <tr><td colspan="5" class="text-center text-muted py-4">Agregue centros de costo para comenzar</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-start">
                                <span class="text-muted">Pendiente: </span>
                                <span id="diferencia_pendiente" class="fw-bold text-warning">₡{{ number_format($detalle->Monto, 2) }}</span>
                            </div>
                            <div class="text-end">
                                <span class="me-2">Total Asignado:</span>
                                <span id="total_asignado" class="h5 mb-0 text-success">₡0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" id="btn_guardar" class="btn btn-dark btn-lg px-5 shadow" disabled>
                        <i class="bi bi-save me-1"></i> Guardar Distribución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let lineas = [];
    const montoObjetivo = parseFloat("{{ $detalle->Monto }}");

    window.onload = function() {
        const existentes = @json($distribucionActual ?? []);

        if (existentes && existentes.length > 0) {
            lineas = existentes.map(d => {

                const option = document.querySelector(`#select_centro option[value="${d.IdCentroCosto}"]`);

                return {
                    id: d.IdCentroCosto,
                    nombre: option ? option.text : `ID: ${d.IdCentroCosto}`,
                    monto: parseFloat(d.Monto),
                    nota: d.Nota ? d.Nota : "" 
                };
            });
        }
        renderizarTabla();
    };

    function agregarLinea() {
        const selectC = document.getElementById('select_centro');
        const inputMonto = document.getElementById('input_monto');
        const monto = parseFloat(inputMonto.value);
        const nota = document.getElementById('input_nota').value;

        if (!selectC.value) {
            alert("Debe seleccionar un Centro de Costo.");
            return;
        }

        const existe = lineas.some(l => l.id == selectC.value);
        if (existe) {
            alert("Este Centro de Costo ya fue agregado al prorrateo.");
            return;
        }

        if (isNaN(monto) || monto <= 0) {
            alert("Ingrese un monto válido mayor a cero.");
            return;
        }

        const sumaActual = lineas.reduce((acc, curr) => acc + curr.monto, 0);
        
        if ((sumaActual + monto) > (montoObjetivo + 0.01)) {
            alert(`No puede exceder el total de la línea. Restante: ₡${(montoObjetivo - sumaActual).toFixed(2)}`);
            return;
        }

        lineas.push({
            id: selectC.value,
            nombre: selectC.options[selectC.selectedIndex].text,
            monto: monto,
            nota: nota
        });

        inputMonto.value = "";
        document.getElementById('input_nota').value = "";
        selectC.value = "";
        
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
                        ${l.nombre} 
                        <input type="hidden" name="distribucion[${index}][id_destino]" value="${l.id}">
                    </td>
                    <td class="small text-muted">
                        ${l.nota}
                        <input type="hidden" name="distribucion[${index}][nota]" value="${l.nota}">
                    </td>
                    <td class="text-end fw-bold">
                        ₡${l.monto.toLocaleString('es-CR', {minimumFractionDigits: 2})} 
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

        tbody.innerHTML = html || '<tr><td colspan="5" class="text-center text-muted py-4">No hay centros asignados</td></tr>';
        
        const diferencia = montoObjetivo - suma;
        document.getElementById('total_asignado').innerText = `₡${suma.toLocaleString('es-CR', {minimumFractionDigits: 2})}`;
        const pendienteElemento = document.getElementById('diferencia_pendiente');

        pendienteElemento.innerText = `₡${Math.max(0, diferencia).toLocaleString('es-CR', {minimumFractionDigits: 2})}`;

        if (Math.abs(diferencia) > 0.01) {
            pendienteElemento.classList.remove("text-success");
            pendienteElemento.classList.add("text-warning");
        } else {
            pendienteElemento.classList.remove("text-warning");
            pendienteElemento.classList.add("text-success");
        }
        
        document.getElementById('btn_guardar').disabled = (Math.abs(diferencia) > 0.01);
    }

    function eliminar(index) {
        lineas.splice(index, 1);
        renderizarTabla();
    }
</script>
@endsection