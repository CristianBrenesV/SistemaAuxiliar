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
use Illuminate\Http\Request; // Importante añadir esta
use Illuminate\Support\Facades\DB;

class ProrrateoController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtener todos los periodos para el selector
        $periodos = DB::table('periodocontable')
            ->orderBy('Anio', 'desc')
            ->orderBy('Mes', 'desc')
            ->get();
        
        // 2. REQUISITO AUX7/8: Buscar el periodo abierto actual por defecto
        $periodoAbierto = DB::table('periodocontable')
            ->where('Estado', 'Abierto')
            ->first();

        // Si el usuario no ha filtrado, usamos el "Abierto", si no hay abierto, el primero de la lista
        $idPeriodo = $request->get('id_periodo', $periodoAbierto->IdPeriodo ?? ($periodos->first()->IdPeriodo ?? null));

        // 3. Iniciar consulta de asientos
        $query = AsientoContableEncabezado::where('IdPeriodo', $idPeriodo);

        // 4. Filtro opcional por estado (desde el formulario del index)
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

    // Los métodos prorratearCostos y prorratearTerceros ahora reciben el ID de la LÍNEA 
    // para cumplir con "Junto a cada línea"
    public function prorratearCostos($idDetalle)
    {
        $detalle = AsientoContableDetalle::with('asiento')->findOrFail($idDetalle);
        
        // REQUISITO: Validar estado antes de entrar a la vista
        if (!in_array($detalle->asiento->IdEstadoAsiento, [1, 2])) {
            return redirect()->route('asientos.index')->with('error', 'Solo se puede prorratear en estados Borrador o Pendiente.');
        }

        $centros = CentroCosto::all(); 
        return view('prorrateo.costos', compact('detalle', 'centros'));
    }

    public function prorratearTerceros($idDetalle)
    {
        $detalle = AsientoContableDetalle::with('asiento')->findOrFail($idDetalle);

        if (!in_array($detalle->asiento->IdEstadoAsiento, [1, 2])) {
            return redirect()->route('asientos.index')->with('error', 'Solo se puede prorratear en estados Borrador o Pendiente.');
        }

        $terceros = Tercero::all();
        return view('prorrateo.terceros', compact('detalle', 'terceros'));
    }

    public function guardarProrrateo(GuardarProrrateoRequest $request)
    {
        try {
            DB::beginTransaction();
            $esTercero = $request->has('es_tercero'); 
            // Asegúrate de tener el ID del usuario en sesión
            $usuarioId = session('user_id') ?? 1; 

            if ($esTercero) {
                AsientoDetalleTercero::where('IdAsientoDetalle', $request->id_detalle)->delete();
                foreach ($request->distribucion as $item) {
                    AsientoDetalleTercero::create([
                        'IdAsientoDetalle' => $request->id_detalle,
                        'IdTercero'        => $item['id_destino'],
                        'Monto'            => $item['monto'],
                        'Porcentaje'       => $item['porcentaje'],
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
                        'Porcentaje'       => $item['porcentaje'],
                    ]);
                }
                $accion = "Prorrateo de Centros de Costo realizado";
            }

            // --- REQUISITO: MANEJO DE BITÁCORAS ---
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