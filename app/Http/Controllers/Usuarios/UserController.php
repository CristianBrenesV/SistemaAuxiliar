<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {

    }

    // Listar usuarios
    public function index(Request $request)
    {
        $perPage = 10; // número de usuarios por página
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        // Llamamos a tu SP con límite y offset
        $usuariosArray = DB::select('CALL sp_UsuariosListar10(?, ?)', [$perPage, $offset]);

        // Contar total de registros (puedes usar otro SP que haga COUNT(*))
        $total = DB::select('SELECT COUNT(*) as total FROM usuarios')[0]->total;

        // Crear el paginador
        $usuarios = new LengthAwarePaginator(
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

        // Cifrar clave
        $key = substr('dsCNm5YzHL9xV8wPR1aXbKfT2oG3jQ7k', 0, 32);
        $nonce = random_bytes(12);
        $tag = '';
        $claveCifrada = openssl_encrypt($request->clave, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);

        // Llamar SP
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

        return back()->with('mensaje', 'Clave actualizada');
    }
}
