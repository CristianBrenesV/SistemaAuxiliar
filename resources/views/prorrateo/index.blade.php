@extends('layouts.principal')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h1 class="h3 text-dark"><i class="bi bi-calculator me-2"></i>Prorrateo de Asientos</h1>
        <span class="badge bg-primary fs-6 shadow-sm">Periodo ID: {{ $idPeriodo ?? 'N/A' }}</span>
    </div>

    {{-- Filtros: Estado y Periodo --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body bg-white rounded border">
            <form method="get" action="{{ route('asientos.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Periodo Contable</label>
                    <select name="id_periodo" class="form-select form-select-sm border-2" onchange="this.form.submit()">
                        @foreach($periodos as $p)
                            <option value="{{ $p->IdPeriodo }}" {{ (isset($idPeriodo) && $idPeriodo == $p->IdPeriodo) ? 'selected' : '' }}>
                                {{ $p->Anio }} - Mes {{ $p->Mes }} ({{ $p->Estado }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Filtrar por Estado</label>
                    <select name="estado_id" class="form-select form-select-sm border-2">
                        <option value="">-- Todos los Estados --</option>
                        <option value="1" {{ request('estado_id') == 1 ? 'selected' : '' }}>Borrador</option>
                        <option value="2" {{ request('estado_id') == 2 ? 'selected' : '' }}>Pendiente de Aprobar</option>
                        <option value="3" {{ request('estado_id') == 3 ? 'selected' : '' }}>Aprobado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-dark w-100 shadow-sm">
                        <i class="bi bi-filter"></i> Aplicar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover table-bordered align-middle bg-white mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="width: 150px;">Consecutivo</th>
                    <th style="width: 120px;">Fecha</th>
                    <th>Referencia</th>
                    <th style="width: 150px;">Estado</th>
                    <th class="text-center" style="width: 180px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($asientos as $a)
                <tr>
                    <td class="fw-bold text-primary">{{ $a->Consecutivo }}</td>
                    <td>{{ \Carbon\Carbon::parse($a->Fecha)->format('d/m/Y') }}</td>
                    <td class="text-truncate" style="max-width: 300px;">{{ $a->Referencia }}</td>
                    <td>
                        @php
                            $badgeClass = match((int)$a->IdEstadoAsiento) {
                                1 => "bg-secondary",
                                2 => "bg-warning text-dark",
                                3 => "bg-info text-dark",
                                4 => "bg-success",
                                default => "bg-light text-dark border"
                            };
                            $puedeProrratear = in_array($a->IdEstadoAsiento, [1, 2]);
                        @endphp
                        <span class="badge {{ $badgeClass }} px-3">
                            @if($a->IdEstadoAsiento == 1) Borrador 
                            @elseif($a->IdEstadoAsiento == 2) Pendiente 
                            @elseif($a->IdEstadoAsiento == 3) Aprobado 
                            @else Estado {{ $a->IdEstadoAsiento }} @endif
                        </span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary shadow-sm" 
                                onclick="cargarDetalle({{ $a->IdAsiento }}, {{ $puedeProrratear ? 'true' : 'false' }})"
                                data-bs-toggle="collapse" 
                                data-bs-target="#det-{{ $a->IdAsiento }}">
                            <i class="bi bi-eye"></i> Ver Detalle
                        </button>
                    </td>
                </tr>

                {{-- Fila Colapsable de Detalles --}}
                <tr class="collapse" id="det-{{ $a->IdAsiento }}">
                    <td colspan="5" class="bg-light p-3">
                        <div class="card card-body border-0 shadow-sm p-0 overflow-hidden">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-secondary">
                                    <tr class="small text-uppercase">
                                        <th class="ps-3">Cuenta Contable</th>
                                        <th class="text-center">Tipo</th>
                                        <th class="text-end">Monto</th>
                                        <th class="ps-3">Descripción</th>
                                        <th class="text-center" style="width: 160px;">Prorrateo</th>
                                    </tr>
                                </thead>
                                <tbody id="detalle-{{ $a->IdAsiento }}" class="small">
                                    <tr><td colspan="5" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando líneas...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        No se encontraron asientos para los filtros aplicados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function cargarDetalle(idAsiento, puedeProrratear) {
        const contenedor = document.getElementById(`detalle-${idAsiento}`);
        if (contenedor.innerHTML.trim() !== "" && !contenedor.innerHTML.includes("Cargando")) return;

        try {
            const response = await fetch(`/asientos/${idAsiento}/detalles`);
            const lineas = await response.json();

            let html = "";
            lineas.forEach(linea => {
                const badgeColor = linea.TipoMovimiento === 'D' ? 'text-primary' : 'text-info';
                const montoFormatted = new Intl.NumberFormat('es-CR', { style: 'currency', currency: 'CRC' }).format(linea.Monto);
                
                let botones = '<span class="text-muted small">N/A</span>';
                if (puedeProrratear) {
                    botones = `
                        <div class="btn-group btn-group-xs shadow-sm">
                            <a href="/prorrateo/costos/${linea.IdAsientoDetalle}" class="btn btn-outline-primary py-0" title="Centros de Costo">
                                <i class="bi bi-diagram-3"></i> CC
                            </a>
                            <a href="/prorrateo/terceros/${linea.IdAsientoDetalle}" class="btn btn-outline-success py-0" title="Asignar Terceros">
                                <i class="bi bi-person"></i> T
                            </a>
                        </div>`;
                }

                html += `
                    <tr>
                        <td class="ps-3 fw-bold">${linea.IdCuentaContable}</td>
                        <td class="text-center fw-bold ${badgeColor}">${linea.TipoMovimiento}</td>
                        <td class="text-end fw-bold">${montoFormatted}</td>
                        <td class="ps-3 text-muted">${linea.Descripcion || 'Sin descripción'}</td>
                        <td class="text-center">${botones}</td>
                    </tr>`;
            });
            contenedor.innerHTML = html || '<tr><td colspan="5" class="text-center">No hay líneas en este asiento.</td></tr>';
        } catch (error) {
            contenedor.innerHTML = '<tr><td colspan="5" class="text-danger text-center"><i class="bi bi-exclamation-triangle"></i> Error al conectar con el servidor.</td></tr>';
        }
    }
</script>

<style>
    .btn-group-xs > .btn {
        padding: 0.15rem 0.4rem;
        font-size: 0.75rem;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.02);
    }
</style>
@endsection