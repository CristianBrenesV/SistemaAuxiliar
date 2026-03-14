<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BitacoraService;

class TerceroContactoController extends Controller
{
    protected BitacoraService $bitacora;

    public function __construct(BitacoraService $bitacora)
    {
        $this->bitacora = $bitacora;
    }

    /**
     * Listar contactos de un tercero (AUX12)
     */
    public function index($idTercero)
    {
        $tercero = DB::select('SELECT IdTercero, Nombre FROM catalogoterceros WHERE IdTercero = ?', [$idTercero]);
        
        if (empty($tercero)) {
            return redirect()->route('terceros.index')->with('error', 'Tercero no encontrado');
        }

        $contactos = DB::select('
            SELECT * FROM tercero_contactos 
            WHERE IdTercero = ? 
            ORDER BY IdContacto DESC
        ', [$idTercero]);

        return view('terceros.contactos.index', [
            'tercero' => $tercero[0],
            'contactos' => $contactos
        ]);
    }

    /**
     * Mostrar formulario para crear contacto
     */
    public function crear($idTercero)
    {
        $tercero = DB::select('SELECT IdTercero, Nombre FROM catalogoterceros WHERE IdTercero = ?', [$idTercero]);
        
        if (empty($tercero)) {
            return redirect()->route('terceros.index')->with('error', 'Tercero no encontrado');
        }

        return view('terceros.contactos.create', ['tercero' => $tercero[0]]);
    }

    /**
     * Guardar nuevo contacto
     */
    public function guardar(Request $request, $idTercero)
    {
        $request->validate([
            'nombre_contacto' => 'required|max:100',
            'cargo' => 'nullable|max:100',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|max:20',
            'tipo_contacto' => 'required|in:Principal,Facturación,Cobros,Soporte,Otro',
            'estado' => 'required|in:1,0'
        ]);

        DB::statement('
            INSERT INTO tercero_contactos 
            (IdTercero, NombreContacto, Cargo, Email, Telefono, TipoContacto, Estado)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ', [
            $idTercero,
            $request->nombre_contacto,
            $request->cargo,
            $request->email,
            $request->telefono,
            $request->tipo_contacto,
            $request->estado
        ]);

        $id = DB::getPdo()->lastInsertId();

        $this->bitacora->registrar('Creación de contacto de tercero', [
            'id_contacto' => $id,
            'id_tercero' => $idTercero,
            'nombre_contacto' => $request->nombre_contacto
        ]);

        return redirect()->route('terceros.contactos.index', $idTercero)
            ->with('mensaje', 'Contacto creado correctamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($idTercero, $idContacto)
    {
        $tercero = DB::select('SELECT IdTercero, Nombre FROM catalogoterceros WHERE IdTercero = ?', [$idTercero]);
        $contacto = DB::select('SELECT * FROM tercero_contactos WHERE IdContacto = ? AND IdTercero = ?', [$idContacto, $idTercero]);
        
        if (empty($tercero) || empty($contacto)) {
            return redirect()->route('terceros.index')->with('error', 'Registro no encontrado');
        }

        return view('terceros.contactos.edit', [
            'tercero' => $tercero[0],
            'contacto' => $contacto[0]
        ]);
    }

    /**
     * Actualizar contacto
     */
    public function actualizar(Request $request, $idTercero, $idContacto)
    {
        $request->validate([
            'nombre_contacto' => 'required|max:100',
            'cargo' => 'nullable|max:100',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|max:20',
            'tipo_contacto' => 'required|in:Principal,Facturación,Cobros,Soporte,Otro',
            'estado' => 'required|in:1,0'
        ]);

        $anterior = DB::select('SELECT * FROM tercero_contactos WHERE IdContacto = ?', [$idContacto]);

        DB::statement('
            UPDATE tercero_contactos 
            SET NombreContacto = ?, Cargo = ?, Email = ?, Telefono = ?, TipoContacto = ?, Estado = ?
            WHERE IdContacto = ?
        ', [
            $request->nombre_contacto,
            $request->cargo,
            $request->email,
            $request->telefono,
            $request->tipo_contacto,
            $request->estado,
            $idContacto
        ]);

        $this->bitacora->registrar('Actualización de contacto de tercero', [
            'id_contacto' => $idContacto,
            'antes' => $anterior[0] ?? null,
            'despues' => $request->all()
        ]);

        return redirect()->route('terceros.contactos.index', $idTercero)
            ->with('mensaje', 'Contacto actualizado correctamente');
    }

    /**
     * Eliminar contacto (AUX12 - con validación)
     */
    public function eliminar($idTercero, $idContacto)
    {
        $contacto = DB::select('SELECT * FROM tercero_contactos WHERE IdContacto = ?', [$idContacto]);

        DB::statement('DELETE FROM tercero_contactos WHERE IdContacto = ?', [$idContacto]);

        $this->bitacora->registrar('Eliminación de contacto de tercero', [
            'id_contacto' => $idContacto,
            'datos_eliminados' => $contacto[0] ?? null
        ]);

        return redirect()->route('terceros.contactos.index', $idTercero)
            ->with('mensaje', 'Contacto eliminado correctamente');
    }
}