<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BitacoraService;

class CentroCostoController extends Controller
{
    protected BitacoraService $bitacora;

    public function __construct(BitacoraService $bitacora)
    {
        $this->bitacora = $bitacora;
    }

    /**
     * Listar centros de costo (AUX6)
     */
    public function index(Request $request)
    {
        $perPage = 10;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $centros = DB::select('
            SELECT IdCentroCosto, Codigo, Nombre, Descripcion, Estado
            FROM catalogocentroscostos
            ORDER BY IdCentroCosto DESC
            LIMIT ? OFFSET ?
        ', [$perPage, $offset]);

        $total = DB::select('SELECT COUNT(*) as total FROM catalogocentroscostos')[0]->total;

        $centros = new \Illuminate\Pagination\LengthAwarePaginator(
            $centros,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $this->bitacora->registrar('Consulta de centros de costo', ['pagina' => $page]);

        return view('centroscosto.index', compact('centros'));
    }

    /**
     * Mostrar formulario para crear centro de costo
     */
    public function crear()
    {
        return view('centroscosto.create');
    }

    /**
     * Guardar centro de costo nuevo
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'codigo' => 'required|max:20|unique:catalogocentroscostos,Codigo',
            'nombre' => 'required|max:100',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:1,0'
        ]);

        DB::statement('
            INSERT INTO catalogocentroscostos (Codigo, Nombre, Descripcion, Estado)
            VALUES (?, ?, ?, ?)
        ', [
            $request->codigo,
            $request->nombre,
            $request->descripcion,
            $request->estado
        ]);

        $id = DB::getPdo()->lastInsertId();

        $this->bitacora->registrar('Creación de centro de costo', [
            'id' => $id,
            'codigo' => $request->codigo,
            'nombre' => $request->nombre
        ]);

        return redirect()->route('centroscosto.index')->with('mensaje', 'Centro de costo creado correctamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($id)
    {
        $centro = DB::select('SELECT * FROM catalogocentroscostos WHERE IdCentroCosto = ?', [$id]);
        
        if (empty($centro)) {
            return redirect()->route('centroscosto.index')->with('error', 'Centro de costo no encontrado');
        }

        return view('centroscosto.edit', ['centro' => $centro[0]]);
    }

    /**
     * Actualizar centro de costo
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'codigo' => 'required|max:20|unique:catalogocentroscostos,Codigo,' . $id . ',IdCentroCosto',
            'nombre' => 'required|max:100',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:1,0'
        ]);

        $anterior = DB::select('SELECT * FROM catalogocentroscostos WHERE IdCentroCosto = ?', [$id]);

        DB::statement('
            UPDATE catalogocentroscostos 
            SET Codigo = ?, Nombre = ?, Descripcion = ?, Estado = ?
            WHERE IdCentroCosto = ?
        ', [
            $request->codigo,
            $request->nombre,
            $request->descripcion,
            $request->estado,
            $id
        ]);

        $this->bitacora->registrar('Actualización de centro de costo', [
            'id' => $id,
            'antes' => $anterior[0] ?? null,
            'despues' => $request->all()
        ]);

        return redirect()->route('centroscosto.index')->with('mensaje', 'Centro de costo actualizado correctamente');
    }

    /**
     * Eliminar centro de costo (AUX6 - con validación de asignación)
     */
    public function eliminar($id)
    {
        // Verificar si tiene asignaciones en asientos
        $asignado = DB::select('
            SELECT COUNT(*) as total 
            FROM asientodetallecentrocosto 
            WHERE IdCentroCosto = ?
        ', [$id]);

        if ($asignado[0]->total > 0) {
            return redirect()->route('centroscosto.index')
                ->with('error', 'No se puede eliminar un registro con datos relacionados.');
        }

        $centro = DB::select('SELECT * FROM catalogocentroscostos WHERE IdCentroCosto = ?', [$id]);

        DB::statement('DELETE FROM catalogocentroscostos WHERE IdCentroCosto = ?', [$id]);

        $this->bitacora->registrar('Eliminación de centro de costo', [
            'id' => $id,
            'datos_eliminados' => $centro[0] ?? null
        ]);

        return redirect()->route('centroscosto.index')->with('mensaje', 'Centro de costo eliminado correctamente');
    }
}