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
                    <label class="fw-bold text-success">Línea del Asiento seleccionada:</label>
                    <select id="select_detalle" class="form-select bg-secondary text-white border-0 mt-1" disabled>
                        <option value="{{ $detalle->IdAsientoDetalle }}" data-monto="{{ $detalle->Monto }}">
                            {{ $detalle->IdCuentaContable }} - ₡{{ number_format($detalle->Monto, 2) }}
                        </option>
                    </select>
                </div>
                <div class="col-md-6 text-end">
                    <h4 class="mb-0 text-info">Monto a Distribuir: <span id="display_monto_objetivo">₡{{ number_format($detalle->Monto, 2) }}</span></h4>
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
                        <label class="form-label">Tercero</label>
                        <select id="select_tercero" class="form-select bg-secondary text-white border-0">
                            <option value="">-- Seleccione --</option>
                            @foreach($terceros as $t)
                                <option value="{{ $t->IdTercero }}">
                                    {{ $t->Identificacion }} | {{ $t->Nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto</label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary text-white border-0">₡</span>
                            <input type="number" id="input_monto" class="form-control bg-secondary text-white border-0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nota (Opcional)</label>
                        <textarea id="input_nota" class="form-control bg-secondary text-white border-0" rows="2"></textarea>
                    </div>
                        <button type="button" id="btn_ejecutar_agregado" class="btn btn-success w-100 shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Agregar a la Lista
                        </button>
                </div>
            </div>
        </div>

        {{-- Tabla de Distribución --}}
        <div class="col-md-8">
            {{-- Bloque de errores agregado para consistencia --}}
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
                <input type="hidden" name="es_tercero" value="1">
                <input type="hidden" name="id_detalle" value="{{ $detalle->IdAsientoDetalle }}">
                
                <div class="card bg-dark text-white border-secondary shadow">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tercero</th>
                                        <th>Nota</th> {{-- Columna Nota separada --}}
                                        <th class="text-end">Monto</th>
                                        <th class="text-end">Porcentaje</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_prorrateo">
                                    <tr><td colspan="5" class="text-center text-muted py-4">Agregue terceros para comenzar</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer border-secondary bg-dark">
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
                    <button type="submit" id="btn_guardar" class="btn btn-success btn-lg px-5 shadow" disabled>
                        <i class="bi bi-save me-1"></i> Guardar Prorrateo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const GestorProrrateo = {
        lineas: [],
        montoObjetivo: parseFloat("{{ $detalle->Monto }}"),

        init: function() {
            console.log("Iniciando Gestor... Monto objetivo:", this.montoObjetivo);
            
            const btn = document.getElementById('btn_ejecutar_agregado');
            if (btn) {
                btn.onclick = () => this.agregar();
                console.log("Evento vinculado al botón");
            } else {
                console.error("No se encontró el botón con ID: btn_ejecutar_agregado");
            }

            const iniciales = @json($distribucionActual ?? []);
            if (iniciales.length > 0) {
                this.lineas = iniciales.map(i => ({
                    id: i.IdTercero,
                    nombre: (i.Identificacion ?? '') + " | " + (i.Nombre ?? 'Tercero'),
                    monto: parseFloat(i.Monto),
                    nota: i.Nota || ""
                }));
            }
            this.render();
        },

        agregar: function() {
            console.log("Botón presionado...");
            const select = document.getElementById('select_tercero');
            const inputMonto = document.getElementById('input_monto');
            const inputNota = document.getElementById('input_nota');

            const id = select.value;
            const monto = parseFloat(inputMonto.value);
            const nota = inputNota.value;

            if (!id) return alert("Seleccione un Tercero");
            const existe = this.lineas.some(l => l.id == id);
            if (existe) {
                alert("Este tercero ya fue agregado al prorrateo.");
                return;
            }

            if (isNaN(monto) || monto <= 0) return alert("Monto no válido");

            const sumaActual = this.lineas.reduce((acc, curr) => acc + curr.monto, 0);
            
            if ((sumaActual + monto) > (this.montoObjetivo + 0.01)) {
                alert("Supera el monto total de la línea");
                return;
            }

            this.lineas.push({
                id: id,
                nombre: select.options[select.selectedIndex].text.trim(),
                monto: monto,
                nota: nota
            });

            inputMonto.value = "";
            inputNota.value = "";
            select.value = "";
            this.render();
        },

        render: function() {
            const tbody = document.getElementById('tabla_prorrateo');
            let html = "";
            let sumaTotal = 0;

            this.lineas.forEach((l, index) => {
                sumaTotal += l.monto;
                const porcentaje = (l.monto / this.montoObjetivo) * 100;
                html += `
                    <tr>
                        <td><b>${l.nombre}</b><input type="hidden" name="distribucion[${index}][id_destino]" value="${l.id}"></td>
                        <td class="small">${l.nota}<input type="hidden" name="distribucion[${index}][nota]" value="${l.nota}"></td>
                        <td class="text-end">₡${l.monto.toFixed(2)}<input type="hidden" name="distribucion[${index}][monto]" value="${l.monto}"></td>
                        <td class="text-end">${porcentaje.toFixed(2)}%<input type="hidden" name="distribucion[${index}][porcentaje]" value="${porcentaje.toFixed(2)}"></td>
                        <td class="text-center">
                            <button type="button" onclick="GestorProrrateo.eliminar(${index})" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            });

            tbody.innerHTML = html || '<tr><td colspan="5" class="text-center py-4">Sin datos</td></tr>';
            
            const diferencia = this.montoObjetivo - sumaTotal;
            document.getElementById('total_asignado').innerText = "₡" + sumaTotal.toFixed(2);
            const pendienteElemento = document.getElementById('diferencia_pendiente');

            pendienteElemento.innerText = "₡" + Math.max(0, diferencia).toFixed(2);

            if (Math.abs(diferencia) > 0.01) {
                pendienteElemento.classList.remove("text-success");
                pendienteElemento.classList.add("text-warning");
            } else {
                pendienteElemento.classList.remove("text-warning");
                pendienteElemento.classList.add("text-success");
            }
            document.getElementById('btn_guardar').disabled = (Math.abs(diferencia) > 0.01);
        },

        eliminar: function(index) {
            this.lineas.splice(index, 1);
            this.render();
        }
    };

    document.addEventListener('DOMContentLoaded', () => GestorProrrateo.init());
</script>
@endpush