<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class BitacoraService
{
    /**
     * Registrar acción en la bitácora
     *
     * @param string $descripcion Descripción de la acción
     * @param array $acciones Datos adicionales en JSON
     */
    public function registrar(string $descripcion, array $acciones)
    {
        try {
            // Obtener ID del usuario logueado (0 si no hay)
            $idUsuario = Auth::id() ?? 0;

            DB::statement('CALL sp_BitacoraInsertar(?, ?, ?)', [
                $idUsuario,
                $descripcion,
                json_encode($acciones)
            ]);
        } catch (Exception $e) {
            Log::error('Error al registrar bitácora: ' . $e->getMessage());
        }
    }
}
