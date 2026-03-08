<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarProrrateoRequest extends FormRequest
{
    // 1. Determina si el usuario tiene permiso de hacer esto
    public function authorize()
    {
        return true; // Cambiar a auth()->check() si usas login
    }

    // 2. Las reglas que deben cumplir los datos
    public function rules()
    {
        return [
            'id_detalle'   => 'required|exists:AsientoContableDetalle,IdAsientoDetalle',
            'distribucion' => 'required|array|min:1',
            'distribucion.*.id_destino' => 'required',
            'distribucion.*.monto'      => 'required|numeric|min:0.01',
            'distribucion.*.porcentaje' => 'required|numeric',
        ];
    }

    // 3. Validación personalizada: ¡Aquí ocurre la magia de la suma!
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $idDetalle = $this->input('id_detalle');
            $distribucion = $this->input('distribucion', []);

            // 1. Buscamos el monto real que tiene la línea en la DB
            $detalleOriginal = \App\Models\AsientoContableDetalle::find($idDetalle);

            if (!$detalleOriginal) {
                $validator->errors()->add('id_detalle', 'La línea del asiento no existe.');
                return;
            }

            // 2. Sumamos lo que el usuario envió en la tabla de la vista
            $sumaEnviada = array_sum(array_column($distribucion, 'monto'));

            // 3. Comparamos (usamos un margen de error de 0.01 por los decimales)
            if (abs($sumaEnviada - $detalleOriginal->Monto) > 0.01) {
                $validator->errors()->add(
                    'total', 
                    "La suma de la distribución (₡" . number_format($sumaEnviada, 2) . 
                    ") no coincide con el monto de la línea (₡" . number_format($detalleOriginal->Monto, 2) . ")."
                );
            }
        });
    }
}