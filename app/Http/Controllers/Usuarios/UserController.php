<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Services\BitacoraService;

class UserController extends Controller
{
    protected BitacoraService $bitacora;

    public function __construct(BitacoraService $bitacora)
    {
        $this->bitacora = $bitacora;
    }

    // Listar usuarios
    public function index(Request $request)
    {
        $perPage = 10;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        // SP paginado: trae solo los registros del límite
        $usuariosArray = DB::select('CALL sp_UsuariosListar10(?, ?)', [$perPage, $offset]);

        // SP de conteo: devuelve total de usuarios
        $total = DB::select('CALL sp_UsuariosConteo()')[0]->Total;

        // Crear el paginador
        $usuarios = new \Illuminate\Pagination\LengthAwarePaginator(
            $usuariosArray,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );

        return view('Usuarios.index', compact('usuarios'));
    }

    // Mostrar formulario para crear usuario
    public function crear()
    {
        return view('Usuarios.create');
    }

    // Guardar usuario nuevo
    public function guardar(Request $request)
    {
        $request->validate([
            'usuario' => 'required',
            'nombre_usuario' => 'required',
            'apellido_usuario' => 'required',
            'correo_electronico' => 'required|email',
            'clave' => 'required'
        ]);

        $key = substr('dsCNm5YzHL9xV8wPR1aXbKfT2oG3jQ7k', 0, 32);
        $nonce = random_bytes(12);
        $tag = '';
        $claveCifrada = openssl_encrypt($request->clave, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);

        DB::statement('CALL sp_UsuariosInsertar(?, ?, ?, ?, ?, ?, ?, ?, @resultado, @idUsuario)', [
            $request->usuario,
            $claveCifrada,
            $request->nombre_usuario,
            $request->apellido_usuario,
            $request->correo_electronico,
            $tag,
            $nonce,
            $request->estado ?? 'Activo'
        ]);

        // Registrar bitácora
        $this->bitacora->registrar('Creación de usuario', [
            'usuario' => $request->usuario,
            'correo' => $request->correo_electronico
        ]);

        return redirect()->route('usuarios.index')->with('mensaje', 'Usuario creado correctamente');
    }

    // Mostrar formulario de edición
    public function editar($id)
    {
        $usuario = DB::select('CALL sp_UsuariosListarPorIdUsuario(?)', [$id]);
        if (empty($usuario)) return redirect()->route('usuarios.index');

        $roles = DB::select('CALL sp_RolesListar()');
        return view('usuarios.edit', [
            'usuario' => $usuario[0],
            'roles' => $roles
        ]);
    }

    // Actualizar usuario existente
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'usuario' => 'required',
            'nombre_usuario' => 'required',
            'apellido_usuario' => 'required',
            'correo_electronico' => 'required|email',
            'estado' => 'required',
        ]);

        DB::statement('SET @resultado = 0;');
        DB::statement('SET @idUsuario = 0;');

        DB::statement('CALL sp_UsuariosActualizarPorIdUsuario(?,?,?,?,?,?,@resultado,@idUsuario)', [
            $id,
            $request->usuario,
            $request->nombre_usuario,
            $request->apellido_usuario,
            $request->correo_electronico,
            $request->estado
        ]);

        $resultado = DB::select('SELECT @resultado as resultado')[0];

        if ($resultado->resultado == 1) {
            $this->bitacora->registrar('Actualización de usuario', [
                'idUsuario' => $id,
                'usuario' => $request->usuario,
                'estado' => $request->estado
            ]);

            return redirect()->route('usuarios.index')
                ->with('mensaje', 'Usuario actualizado correctamente');
        }

        return back()->with('error', 'Error al actualizar usuario');
    }

    // Eliminar usuario
    public function eliminar($id)
    {
        DB::statement('SET @resultado = 0;');
        DB::statement('CALL sp_UsuariosEliminarPorIdUsuario(?, @resultado)', [$id]);
        $resultado = DB::select('SELECT @resultado as resultado')[0];

        if ($resultado->resultado == 1) {
            $this->bitacora->registrar('Eliminación de usuario', ['idUsuario' => $id]);
            return redirect()->route('usuarios.index')->with('mensaje', 'Usuario eliminado correctamente');
        }

        return back()->with('error', 'Error al eliminar usuario');
    }

    // Cambiar estado de usuario
    public function cambiarEstadoUsuario(Request $request)
    {
        $request->validate([
            'IdUsuario' => 'required|integer',
            'nuevo_estado' => 'required|in:Activo,Inactivo,Bloqueado'
        ]);

        DB::statement('CALL sp_CambiarEstadoUsuario(?, ?)', [
            $request->IdUsuario,
            $request->nuevo_estado
        ]);

        $this->bitacora->registrar('Cambio de estado de usuario', [
            'idUsuario' => $request->IdUsuario,
            'nuevo_estado' => $request->nuevo_estado
        ]);

        return back()->with('mensaje', 'Estado actualizado');
    }

    // Cambiar clave de usuario
    public function cambiarClaveUsuario(Request $request)
    {
        $id = $request->IdUsuario;
        $clave = $request->clave;

        $key = substr('dsCNm5YzHL9xV8wPR1aXbKfT2oG3jQ7k', 0, 32);
        $nonce = random_bytes(12);
        $tag = '';
        $claveCifrada = openssl_encrypt($clave, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);

        DB::statement('CALL sp_CambiarClaveUsuario(?, ?, ?, ?)', [$id, $claveCifrada, $tag, $nonce]);

        $this->bitacora->registrar('Cambio de clave de usuario', ['idUsuario' => $id]);

        return back()->with('mensaje', 'Clave actualizada');
    }
}
