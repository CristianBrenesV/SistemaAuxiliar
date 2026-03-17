<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BitacoraService;

class TerceroController extends Controller
{
    protected BitacoraService $bitacora;

    public function __construct(BitacoraService $bitacora)
    {
        $this->bitacora = $bitacora;
    }

    /**
     * Listar terceros (AUX5)
     */
    public function index(Request $request)
    {
        $perPage = 10;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $terceros = DB::select('
            SELECT IdTercero, Identificacion, Nombre, TipoTercero, Email, Telefono, Estado
            FROM catalogoterceros
            ORDER BY IdTercero DESC
            LIMIT ? OFFSET ?
        ', [$perPage, $offset]);

        $total = DB::select('SELECT COUNT(*) as total FROM catalogoterceros')[0]->total;

        $terceros = new \Illuminate\Pagination\LengthAwarePaginator(
            $terceros,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $this->bitacora->registrar('Consulta de terceros', ['pagina' => $page]);

        return view('terceros.index', compact('terceros'));
    }

    /**
     * Mostrar formulario para crear tercero
     */
    public function crear()
    {
        return view('terceros.create');
    }

    /**
     * Guardar tercero nuevo
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'identificacion' => 'required|max:20|unique:catalogoterceros,Identificacion',
            'nombre' => 'required|max:150',
            'tipo' => 'required|in:Cliente,Proveedor,Empleado,Otro',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|max:20',
            'estado' => 'required|in:1,0'
        ]);

        DB::statement('
            INSERT INTO catalogoterceros (Identificacion, Nombre, TipoTercero, Email, Telefono, Estado)
            VALUES (?, ?, ?, ?, ?, ?)
        ', [
            $request->identificacion,
            $request->nombre,
            $request->tipo,
            $request->email,
            $request->telefono,
            $request->estado
        ]);

        $id = DB::getPdo()->lastInsertId();

        $this->bitacora->registrar('Creación de tercero', [
            'id' => $id,
            'identificacion' => $request->identificacion,
            'nombre' => $request->nombre
        ]);

        return redirect()->route('terceros.index')->with('mensaje', 'Tercero creado correctamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($id)
    {
        $tercero = DB::select('SELECT * FROM catalogoterceros WHERE IdTercero = ?', [$id]);
        
        if (empty($tercero)) {
            return redirect()->route('terceros.index')->with('error', 'Tercero no encontrado');
        }

        return view('terceros.edit', ['tercero' => $tercero[0]]);
    }

    /**
     * Actualizar tercero
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'identificacion' => 'required|max:20|unique:catalogoterceros,Identificacion,' . $id . ',IdTercero',
            'nombre' => 'required|max:150',
            'tipo' => 'required|in:Cliente,Proveedor,Empleado,Otro',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|max:20',
            'estado' => 'required|in:1,0'
        ]);

        // Obtener datos anteriores para bitácora
        $anterior = DB::select('SELECT * FROM catalogoterceros WHERE IdTercero = ?', [$id]);

        DB::statement('
            UPDATE catalogoterceros 
            SET Identificacion = ?, Nombre = ?, TipoTercero = ?, Email = ?, Telefono = ?, Estado = ?
            WHERE IdTercero = ?
        ', [
            $request->identificacion,
            $request->nombre,
            $request->tipo,
            $request->email,
            $request->telefono,
            $request->estado,
            $id
        ]);

        $this->bitacora->registrar('Actualización de tercero', [
            'id' => $id,
            'antes' => $anterior[0] ?? null,
            'despues' => $request->all()
        ]);

        return redirect()->route('terceros.index')->with('mensaje', 'Tercero actualizado correctamente');
    }

    /**
     * Eliminar tercero (AUX5 - con validación de asignación)
     */
    public function eliminar($id)
    {
        // Verificar si tiene asignaciones en asientos
        $asignado = DB::select('
            SELECT COUNT(*) as total 
            FROM asientodetalletercero 
            WHERE IdTercero = ?
        ', [$id]);

        if ($asignado[0]->total > 0) {
            return redirect()->route('terceros.index')
                ->with('error', 'No se puede eliminar un registro con datos relacionados.');
        }

        // Obtener datos para bitácora
        $tercero = DB::select('SELECT * FROM catalogoterceros WHERE IdTercero = ?', [$id]);

        DB::statement('DELETE FROM catalogoterceros WHERE IdTercero = ?', [$id]);

        $this->bitacora->registrar('Eliminación de tercero', [
            'id' => $id,
            'datos_eliminados' => $tercero[0] ?? null
        ]);

        return redirect()->route('terceros.index')->with('mensaje', 'Tercero eliminado correctamente');
    }
}