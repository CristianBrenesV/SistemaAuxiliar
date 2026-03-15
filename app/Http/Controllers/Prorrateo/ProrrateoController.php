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
use App\Services\BitacoraService; 

class ProrrateoController extends Controller
{

    private $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }


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

        $asientos = $query->orderBy('Fecha', 'desc')
            ->paginate(10)
            ->withQueryString();

        $this->bitacoraService->registrar(
            "El usuario consulta listado de asientos para prorrateo",
            [
                "TipoAccion" => "CONSULTAR",
                "Elemento" => "AsientosContables",
                "Datos" => [
                    "Periodo" => $idPeriodo,
                    "EstadoFiltro" => $request->estado_id
                ]
            ]
        );

        return view('prorrateo.index', compact('asientos', 'periodos', 'idPeriodo'));
    }


    public function obtenerDetalles($id)
    {
        $detalles = DB::table('asientocontabledetalle as d')
            ->join('cuentascontables as c', 'c.IdCuenta', '=', 'd.IdCuentaContable')

            ->leftJoin('asientodetallecentrocosto as cc', 'cc.IdAsientoDetalle', '=', 'd.IdAsientoDetalle')
            ->leftJoin('asientodetalletercero as t', 't.IdAsientoDetalle', '=', 'd.IdAsientoDetalle')

            ->where('d.IdAsiento', $id)

            ->select(
                'd.IdAsientoDetalle',
                'd.IdCuentaContable',
                'c.CodigoCuenta',
                'c.Nombre',
                'd.TipoMovimiento',
                'd.Monto',
                'd.Descripcion',
                DB::raw('COUNT(DISTINCT cc.IdDetalleCC) as tieneCC'),
                DB::raw('COUNT(DISTINCT t.IdDetalleTercero) as tieneTercero')
            )

            ->groupBy(
                'd.IdAsientoDetalle',
                'd.IdCuentaContable',
                'c.CodigoCuenta',
                'c.Nombre',
                'd.TipoMovimiento',
                'd.Monto',
                'd.Descripcion'
            )

            ->get();

        $this->bitacoraService->registrar(
            "El usuario consulta detalles del asiento",
            [
                "TipoAccion" => "CONSULTAR",
                "Elemento" => "DetalleAsiento",
                "Datos" => [
                    "IdAsiento" => $id
                ]
            ]
        );

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

        $this->bitacoraService->registrar(
            "El usuario abre pantalla de prorrateo de centros de costo",
            [
                "TipoAccion" => "CONSULTAR",
                "Elemento" => "ProrrateoCentroCosto",
                "Datos" => [
                    "IdDetalleAsiento" => $idDetalle
                ]
            ]
        );

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

        $this->bitacoraService->registrar(
            "El usuario abre pantalla de prorrateo de terceros",
            [
                "TipoAccion" => "CONSULTAR",
                "Elemento" => "ProrrateoTerceros",
                "Datos" => [
                    "IdDetalleAsiento" => $idDetalle
                ]
            ]
        );

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

            $total = array_sum(array_column($request->distribucion, 'monto'));

            $montoLinea = AsientoContableDetalle::where('IdAsientoDetalle', $request->id_detalle)
                ->value('Monto');

            if (abs($total - $montoLinea) > 0.01) {
                return back()->withErrors([
                    'error' => 'El total del prorrateo no coincide con el monto de la línea.'
                ]);
            }

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
                        'Nota'             => $item['nota'] ?? null
                    ]);
                }

                $accion = "Prorrateo de Centros de Costo realizado";
            }

            $this->bitacoraService->registrar(
                $accion,
                [
                    "TipoAccion" => "ACTUALIZAR",
                    "Elemento" => "ProrrateoDetalleAsiento",
                    "Datos" => [
                        "IdDetalleAsiento" => $request->id_detalle,
                        "MontoTotal" => $total,
                        "Distribucion" => $request->distribucion
                    ]
                ]
            );


            DB::commit();

            return redirect()->route('asientos.index')
            ->with('success', 'Prorrateo guardado correctamente.');

        } catch (\Exception $e) {

            DB::rollBack();

            $this->bitacoraService->registrar(
                "Error al guardar prorrateo",
                [
                    "TipoAccion" => "ERROR",
                    "Elemento" => "Prorrateo",
                    "Datos" => [
                        "Mensaje" => $e->getMessage(),
                        "Linea" => $e->getLine()
                    ]
                ]
            );

            return back()->withErrors([
                'error' => 'Error en el guardado: ' . $e->getMessage()
            ]);
        }
    }
}