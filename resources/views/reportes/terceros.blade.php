@extends('layouts.principal')

@section('content')

<div class="container mt-4">

<div class="card shadow">

<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
<h5 class="mb-0">Reporte de Movimientos por Tercero</h5>
</div>

<div class="card-body">

<form method="GET" class="row g-3 mb-4">

<div class="col-md-3">
<label class="form-label fw-semibold">Tercero</label>
<select name="tercero_id" class="form-select form-select-sm">
<option value="">Todos los terceros</option>

@foreach($terceros as $t)

<option value="{{ $t->IdTercero }}"
{{ request('tercero_id') == $t->IdTercero ? 'selected' : '' }}>
{{ $t->Nombre }}
</option>

@endforeach

</select>
</div>


<div class="col-md-3">
<label class="form-label fw-semibold">Fecha inicio</label>
<input type="date"
name="fecha_inicio"
value="{{ request('fecha_inicio') }}"
class="form-control form-control-sm">
</div>


<div class="col-md-3">
<label class="form-label fw-semibold">Fecha fin</label>
<input type="date"
name="fecha_fin"
value="{{ request('fecha_fin') }}"
class="form-control form-control-sm">
</div>


<div class="col-md-2">
<label class="form-label fw-semibold">Estado</label>
<select name="estado_id" class="form-select form-select-sm">

<option value="">Todos</option>

<option value="1" {{ request('estado_id')==1?'selected':'' }}>
Borrador
</option>

<option value="2" {{ request('estado_id')==2?'selected':'' }}>
Pendiente
</option>

<option value="3" {{ request('estado_id')==3?'selected':'' }}>
Aprobado
</option>

</select>
</div>


<div class="col-md-1 d-flex align-items-end">
<button class="btn btn-primary btn-sm w-100">
Buscar
</button>
</div>

<div class="col-md-12">
<a href="{{ route('reportes.terceros') }}" class="btn btn-outline-secondary btn-sm">
Limpiar filtros
</a>
</div>

</form>


<div class="table-responsive">

<table class="table table-bordered table-hover table-striped align-middle">

<thead class="table-dark text-center">

<tr>
<th style="width:120px">Fecha</th>
<th style="width:100px">Asiento</th>
<th>Cuenta</th>
<th style="width:120px">Debe</th>
<th style="width:120px">Haber</th>
</tr>

</thead>

<tbody>

@forelse($movimientos as $m)

<tr>

<td class="text-center">
{{ \Carbon\Carbon::parse($m->Fecha)->format('d/m/Y') }}
</td>

<td class="text-center fw-semibold">
{{ $m->Consecutivo }}
</td>

<td>
{{ $m->CodigoCuenta }} - {{ $m->Cuenta }}
</td>

<td class="text-end">
@if($m->TipoMovimiento == 'D')
<span class="text-success fw-semibold">
{{ number_format($m->Monto,2) }}
</span>
@endif
</td>

<td class="text-end">
@if($m->TipoMovimiento == 'C')
<span class="text-danger fw-semibold">
{{ number_format($m->Monto,2) }}
</span>
@endif
</td>

</tr>

@empty

<tr>
<td colspan="5" class="text-center text-muted py-4">
No se encontraron movimientos
</td>
</tr>

@endforelse

</tbody>

</table>

</div>


<div class="d-flex justify-content-center mt-3">

{{ $movimientos->links() }}

</div>

<div class="row mt-4">

<div class="col-md-4">

<div class="alert alert-success text-center">
<strong>Total Debe</strong><br>
<span class="fs-5 fw-bold">
{{ number_format($totalDebe,2) }}
</span>
</div>

</div>

<div class="col-md-4">

<div class="alert alert-danger text-center">
<strong>Total Haber</strong><br>
<span class="fs-5 fw-bold">
{{ number_format($totalHaber,2) }}
</span>
</div>

</div>

<div class="col-md-4">

<div class="alert {{ $diferencia == 0 ? 'alert-success' : 'alert-danger' }} text-center">
<strong>Diferencia</strong><br>
<span class="fs-5 fw-bold">
{{ number_format($diferencia,2) }}
</span>
</div>

</div>

</div>

</div>

</div>

</div>

</div>

@endsection