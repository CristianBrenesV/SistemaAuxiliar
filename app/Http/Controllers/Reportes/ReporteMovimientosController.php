<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteMovimientosController extends Controller
{

    public function reporteTerceros(Request $request)
    {

        $terceros = DB::table('catalogoterceros')->get();

        $query = DB::table('asientodetalletercero as t')
        ->join('asientocontabledetalle as d','d.IdAsientoDetalle','=','t.IdAsientoDetalle')
        ->join('asientocontableencabezado as a','a.IdAsiento','=','d.IdAsiento')
        ->join('catalogoterceros as te','te.IdTercero','=','t.IdTercero')
        ->join('cuentascontables as c','c.IdCuenta','=','d.IdCuentaContable');

        if($request->tercero_id){
            $query->where('t.IdTercero',$request->tercero_id);
        }

        if($request->fecha_inicio){
            $query->whereDate('a.Fecha','>=',$request->fecha_inicio);
        }

        if($request->fecha_fin){
            $query->whereDate('a.Fecha','<=',$request->fecha_fin);
        }

        if($request->estado_id){
            $query->where('a.IdEstadoAsiento',$request->estado_id);
        }

        $totalDebe = (clone $query)
        ->where('d.TipoMovimiento','D')
        ->sum('t.Monto');

        $totalHaber = (clone $query)
        ->where('d.TipoMovimiento','C')
        ->sum('t.Monto');

        $diferencia = $totalDebe - $totalHaber;

        $movimientos = $query
        ->select(
            'a.Consecutivo',
            'a.Fecha',
            'te.Nombre as Tercero',
            'c.CodigoCuenta',
            'c.Nombre as Cuenta',
            'd.TipoMovimiento',
            't.Monto'
        )
        ->orderBy('a.Fecha','desc')
        ->paginate(10);

        return view('reportes.terceros',compact(
            'movimientos',
            'terceros',
            'totalDebe',
            'totalHaber',
            'diferencia'
        ));
    }



    public function reporteCentros(Request $request)
    {

        $centros = DB::table('catalogocentroscostos')->get();

        $query = DB::table('asientodetallecentrocosto as cc')
        ->join('asientocontabledetalle as d','d.IdAsientoDetalle','=','cc.IdAsientoDetalle')
        ->join('asientocontableencabezado as a','a.IdAsiento','=','d.IdAsiento')
        ->join('catalogocentroscostos as c','c.IdCentroCosto','=','cc.IdCentroCosto')
        ->join('cuentascontables as cu','cu.IdCuenta','=','d.IdCuentaContable');

        if($request->centro_id){
            $query->where('cc.IdCentroCosto',$request->centro_id);
        }

        if($request->fecha_inicio){
            $query->whereDate('a.Fecha','>=',$request->fecha_inicio);
        }

        if($request->fecha_fin){
            $query->whereDate('a.Fecha','<=',$request->fecha_fin);
        }

        if($request->estado_id){
            $query->where('a.IdEstadoAsiento',$request->estado_id);
        }

        $totalDebe = (clone $query)
        ->where('d.TipoMovimiento','D')
        ->sum('cc.Monto');

        $totalHaber = (clone $query)
        ->where('d.TipoMovimiento','C')
        ->sum('cc.Monto');

        $diferencia = $totalDebe - $totalHaber;

        $movimientos = $query
        ->select(
            'a.Consecutivo',
            'a.Fecha',
            'c.Nombre as CentroCosto',
            'cu.CodigoCuenta',
            'cu.Nombre as Cuenta',
            'd.TipoMovimiento',
            'cc.Monto'
        )
        ->orderBy('a.Fecha','desc')
        ->paginate(10);

        return view('reportes.centros',compact(
            'movimientos',
            'centros',
            'totalDebe',
            'totalHaber',
            'diferencia'
        ));
    }
}