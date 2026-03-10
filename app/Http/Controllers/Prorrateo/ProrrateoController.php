<?php

namespace App\Http\Controllers\Prorrateo;

use App\Http\Controllers\Controller;
use App\Models\AsientoContableEncabezado;
use App\Models\AsientoContableDetalle;
use App\Models\CentroCosto;
use App\Models\Tercero;
use App\Models\AsientoDetalleCentroCosto; 
use App\Models\AsientoDetalleTercero;
use App\Http\Requests\GuardarProrrateoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProrrateoController extends Controller
{
    public function index(Request $request)
    {
        $periodos = DB::table('periodocontable')
            ->orderBy('Anio', 'desc')
            ->orderBy('Mes', 'desc')
            ->get();
        
        $periodoAbierto = DB::table('periodocontable')
            ->where('Estado', 'Abierto')
            ->first();

        $idPeriodo = $request->get('id_periodo', $periodoAbierto->IdPeriodo ?? ($periodos->first()->IdPeriodo ?? null));

        $query = AsientoContableEncabezado::where('IdPeriodo', $idPeriodo);

        if ($request->filled('estado_id')) {
            $query->where('IdEstadoAsiento', $request->estado_id);
        }

        $asientos = $query->orderBy('Fecha', 'desc')->get();

        return view('prorrateo.index', compact('asientos', 'periodos', 'idPeriodo'));
    }

    public function obtenerDetalles($id)
    {
        $detalles = AsientoContableDetalle::where('IdAsiento', $id)->get();
        return response()->json($detalles);
    }

    public function prorratearCostos($idDetalle)
    {
        $detalle = AsientoContableDetalle::with('asiento')->findOrFail($idDetalle);
        
        if (!in_array($detalle->asiento->IdEstadoAsiento, [1, 2])) {
            return redirect()->route('asientos.index')->with('error', 'Solo se puede prorratear en estados Borrador o Pendiente.');
        }

        $centros = CentroCosto::all(); 
        
        $distribucionActual = AsientoDetalleCentroCosto::where('IdAsientoDetalle', $idDetalle)->get();

        return view('prorrateo.costos', compact('detalle', 'centros', 'distribucionActual'));
    }

    public function prorratearTerceros($idDetalle)
    {
        $detalle = AsientoContableDetalle::with('asiento.detalles')->findOrFail($idDetalle);

        if (!in_array($detalle->asiento->IdEstadoAsiento, [1, 2])) {
            return redirect()->route('asientos.index')
            ->with('error', 'Solo se puede prorratear en estados Borrador o Pendiente.');
        }

        $terceros = Tercero::where('Estado',1)->get();

        $distribucionActual = DB::table('asientodetalletercero as adt')
            ->join('catalogoterceros as t', 't.IdTercero', '=', 'adt.IdTercero')
            ->where('adt.IdAsientoDetalle', $idDetalle)
            ->where('t.Estado', 1)
            ->select(
                'adt.IdTercero',
                'adt.Monto',
                'adt.Porcentaje',
                'adt.Nota',
                't.Nombre',
                't.Identificacion'
            )
            ->get();

        $asiento = $detalle->asiento;

        return view('prorrateo.terceros', compact(
            'detalle',
            'terceros',
            'distribucionActual',
            'asiento',
            'idDetalle'
        ));
    }

    public function guardarProrrateo(GuardarProrrateoRequest $request)
    {
        try {
            DB::beginTransaction();

            $esTercero = $request->input('es_tercero') == "1"; 
            $usuarioId = session('user_id') ?? 1; 

            if ($esTercero) {
                AsientoDetalleTercero::where('IdAsientoDetalle', $request->id_detalle)->delete();
                
                foreach ($request->distribucion as $item) {
                    AsientoDetalleTercero::create([
                        'IdAsientoDetalle' => $request->id_detalle,
                        'IdTercero'        => $item['id_destino'],
                        'Monto'            => $item['monto'],
                        'Porcentaje'       => $item['porcentaje'],
                        'Nota'             => $item['nota'] ?? null,
                    ]);
                }
                $accion = "Prorrateo de Terceros realizado";
            } else {
                AsientoDetalleCentroCosto::where('IdAsientoDetalle', $request->id_detalle)->delete();
                
                foreach ($request->distribucion as $item) {
                    AsientoDetalleCentroCosto::create([
                        'IdAsientoDetalle' => $request->id_detalle,
                        'IdCentroCosto'    => $item['id_destino'], 
                        'Monto'            => $item['monto'],
                        'Porcentaje'       => $item['porcentaje'] ?? 0,
                    ]);
                }
                $accion = "Prorrateo de Centros de Costo realizado";
            }

            DB::table('bitacora')->insert([
                'IdUsuarioAccion'   => $usuarioId,
                'FechaBitacora'     => now(),
                'DescripcionAccion' => $accion . " - Línea #{$request->id_detalle}",
                'ListadoAccion'     => json_encode([
                    'modulo' => 'Prorrateo',
                    'id_detalle_asiento' => $request->id_detalle,
                    'monto_total' => array_sum(array_column($request->distribucion, 'monto')),
                    'distribucion' => $request->distribucion,
                    'ip' => request()->ip()
                ])
            ]);

            DB::commit();
            return redirect()->route('asientos.index')->with('success', 'Prorrateo guardado y registrado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error en el guardado: ' . $e->getMessage()]);
        }
    }
}   