<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    private $key;

    public function __construct()
    {
        // Key AES-256 exacta
        $this->key = substr('dsCNm5YzHL9xV8wPR1aXbKfT2oG3jQ7k', 0, 32);
    }

    private function verificarClaveAESGCM(string $inputPassword, string $claveCifrada, string $tag, string $nonce): bool
    {
        $decrypted = openssl_decrypt(
            $claveCifrada,
            'aes-256-gcm',
            $this->key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        return hash_equals($decrypted ?? '', $inputPassword);
    }

    private function obtenerUsuario(string $usuario): ?object
    {
        $result = DB::select('CALL sp_VerificarCredencial(?)', [$usuario]);
        return !empty($result) ? $result[0] : null;
    }

    private function registrarIntentoFallido(string $usuario): int
    {
        DB::statement('SET @resultado = 0');
        DB::statement('CALL sp_RegistrarIntentoFallido(?, @resultado)', [$usuario]);
        $result = DB::select('SELECT @resultado as resultado');
        return $result[0]->resultado ?? 0;
    }

    private function reiniciarIntentos(string $usuario): void
    {
        DB::statement('CALL sp_ReiniciarIntentos(?)', [$usuario]);
    }

    public function showLogin()
    {
        // Si ya hay sesión activa, redirige al principal
        if (session()->has('user_id')) {
            return redirect('/principal');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required',
            'password' => 'required'
        ]);

        $usuario = $request->usuario;
        $user = $this->obtenerUsuario($usuario);

        if ($user) {
            if ($this->verificarClaveAESGCM($request->password, $user->ClaveCifrada, $user->TagAutenticacion, $user->Nonce)) {
                $this->reiniciarIntentos($usuario);

                // Guardamos sesión
                session([
                    'user_id' => $user->IdUsuario,
                    'user_name' => $user->NombreUsuario . ' ' . $user->ApellidoUsuario,
                    'nombre_usuario' => $user->NombreUsuario,
                    'apellido_usuario' => $user->ApellidoUsuario,
                    'usuario' => $user->Usuario
                ]);

                return redirect('/principal');
            } else {
                $this->registrarIntentoFallido($usuario);
            }
        }

        return back()->with('error', 'Usuario o contraseña incorrectos');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}
