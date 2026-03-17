<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BitacoraService;

class TerceroDireccionController extends Controller
{
    protected BitacoraService $bitacora;

    public function __construct(BitacoraService $bitacora)
    {
        $this->bitacora = $bitacora;
    }

    /**
     * Listar direcciones de un tercero (AUX11)
     */
    public function index($idTercero)
    {
        // Verificar que el tercero existe
        $tercero = DB::select('SELECT IdTercero, Nombre FROM catalogoterceros WHERE IdTercero = ?', [$idTercero]);
        
        if (empty($tercero)) {
            return redirect()->route('terceros.index')->with('error', 'Tercero no encontrado');
        }

        $direcciones = DB::select('
            SELECT * FROM tercero_direcciones 
            WHERE IdTercero = ? 
            ORDER BY EsPrincipal DESC, IdDireccion DESC
        ', [$idTercero]);

        return view('terceros.direcciones.index', [
            'tercero' => $tercero[0],
            'direcciones' => $direcciones
        ]);
    }

    /**
     * Mostrar formulario para crear dirección
     */
    public function crear($idTercero)
    {
        $tercero = DB::select('SELECT IdTercero, Nombre FROM catalogoterceros WHERE IdTercero = ?', [$idTercero]);
        
        if (empty($tercero)) {
            return redirect()->route('terceros.index')->with('error', 'Tercero no encontrado');
        }

        return view('terceros.direcciones.create', ['tercero' => $tercero[0]]);
    }

    /**
     * Guardar nueva dirección
     */
    public function guardar(Request $request, $idTercero)
    {
        $request->validate([
            'alias' => 'required|max:100',
            'provincia' => 'required|max:50',
            'canton' => 'required|max:50',
            'distrito' => 'required|max:50',
            'direccion_exacta' => 'required|string',
            'es_principal' => 'sometimes|boolean',
            'estado' => 'required|in:1,0'
        ]);

        DB::beginTransaction();

        try {
            // Si esta dirección es principal, quitar principal de las demás
            if ($request->has('es_principal') && $request->es_principal) {
                DB::statement('
                    UPDATE tercero_direcciones 
                    SET EsPrincipal = 0 
                    WHERE IdTercero = ?
                ', [$idTercero]);
            }

            DB::statement('
                INSERT INTO tercero_direcciones 
                (IdTercero, Alias, Provincia, Canton, Distrito, DireccionExacta, EsPrincipal, Estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ', [
                $idTercero,
                $request->alias,
                $request->provincia,
                $request->canton,
                $request->distrito,
                $request->direccion_exacta,
                $request->has('es_principal') ? 1 : 0,
                $request->estado
            ]);

            DB::commit();

            $this->bitacora->registrar('Creación de dirección de tercero', [
                'id_tercero' => $idTercero,
                'alias' => $request->alias,
                'es_principal' => $request->has('es_principal')
            ]);

            return redirect()->route('terceros.direcciones.index', $idTercero)
                ->with('mensaje', 'Dirección creada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la dirección: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($idTercero, $idDireccion)
    {
        $tercero = DB::select('SELECT IdTercero, Nombre FROM catalogoterceros WHERE IdTercero = ?', [$idTercero]);
        $direccion = DB::select('SELECT * FROM tercero_direcciones WHERE IdDireccion = ? AND IdTercero = ?', [$idDireccion, $idTercero]);
        
        if (empty($tercero) || empty($direccion)) {
            return redirect()->route('terceros.index')->with('error', 'Registro no encontrado');
        }

        return view('terceros.direcciones.edit', [
            'tercero' => $tercero[0],
            'direccion' => $direccion[0]
        ]);
    }

    /**
     * Actualizar dirección
     */
    public function actualizar(Request $request, $idTercero, $idDireccion)
    {
        $request->validate([
            'alias' => 'required|max:100',
            'provincia' => 'required|max:50',
            'canton' => 'required|max:50',
            'distrito' => 'required|max:50',
            'direccion_exacta' => 'required|string',
            'es_principal' => 'sometimes|boolean',
            'estado' => 'required|in:1,0'
        ]);

        DB::beginTransaction();

        try {
            $anterior = DB::select('SELECT * FROM tercero_direcciones WHERE IdDireccion = ?', [$idDireccion]);

            // Si esta dirección es principal, quitar principal de las demás
            if ($request->has('es_principal') && $request->es_principal) {
                DB::statement('
                    UPDATE tercero_direcciones 
                    SET EsPrincipal = 0 
                    WHERE IdTercero = ? AND IdDireccion != ?
                ', [$idTercero, $idDireccion]);
            }

            DB::statement('
                UPDATE tercero_direcciones 
                SET Alias = ?, Provincia = ?, Canton = ?, Distrito = ?, 
                    DireccionExacta = ?, EsPrincipal = ?, Estado = ?
                WHERE IdDireccion = ?
            ', [
                $request->alias,
                $request->provincia,
                $request->canton,
                $request->distrito,
                $request->direccion_exacta,
                $request->has('es_principal') ? 1 : 0,
                $request->estado,
                $idDireccion
            ]);

            DB::commit();

            $this->bitacora->registrar('Actualización de dirección de tercero', [
                'id_direccion' => $idDireccion,
                'antes' => $anterior[0] ?? null,
                'despues' => $request->all()
            ]);

            return redirect()->route('terceros.direcciones.index', $idTercero)
                ->with('mensaje', 'Dirección actualizada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la dirección: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar dirección (AUX11 - con validación)
     */
    public function eliminar($idTercero, $idDireccion)
    {
        DB::beginTransaction();

        try {
            // Verificar si está asignada a algo (por si acaso)
            $asignada = DB::select('
                SELECT COUNT(*) as total 
                FROM asientodetalletercero_direccion 
                WHERE IdDireccion = ?
            ', [$idDireccion]);

            if ($asignada[0]->total > 0) {
                return redirect()->route('terceros.direcciones.index', $idTercero)
                    ->with('error', 'No se puede eliminar una dirección con datos relacionados.');
            }

            $direccion = DB::select('SELECT * FROM tercero_direcciones WHERE IdDireccion = ?', [$idDireccion]);

            DB::statement('DELETE FROM tercero_direcciones WHERE IdDireccion = ?', [$idDireccion]);

            DB::commit();

            $this->bitacora->registrar('Eliminación de dirección de tercero', [
                'id_direccion' => $idDireccion,
                'datos_eliminados' => $direccion[0] ?? null
            ]);

            return redirect()->route('terceros.direcciones.index', $idTercero)
                ->with('mensaje', 'Dirección eliminada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la dirección: ' . $e->getMessage());
        }
    }
}