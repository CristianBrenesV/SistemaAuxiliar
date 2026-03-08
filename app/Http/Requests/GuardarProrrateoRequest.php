<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarProrrateoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize()
    {
        // Si ya tienes autenticación, cámbialo a: return auth()->check();
        return true;
    }

    /**
     * Reglas de validación para los datos recibidos.
     */
    public function rules()
    {
        return [
            'id_detalle'                => 'required|exists:asientocontabledetalle,IdAsientoDetalle',
            'es_tercero'                => 'required|boolean', // Vital para la lógica del controlador
            'distribucion'              => 'required|array|min:1',
            'distribucion.*.id_destino' => 'required',
            'distribucion.*.monto'      => 'required|numeric|min:0.01',
            'distribucion.*.porcentaje' => 'required|numeric',
            'distribucion.*.nota'       => 'nullable|string|max:255', // Permite guardar la nota de forma segura
        ];
    }

    /**
     * Validación personalizada para asegurar que la suma coincida con el total de la línea.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $idDetalle = $this->input('id_detalle');
            $distribucion = $this->input('distribucion', []);

            // 1. Buscamos el detalle original en la base de datos
            $detalleOriginal = \App\Models\AsientoContableDetalle::find($idDetalle);

            if (!$detalleOriginal) {
                // Este error técnicamente lo atrapa 'exists', pero es una red de seguridad
                $validator->errors()->add('id_detalle', 'La línea del asiento seleccionada no es válida.');
                return;
            }

            // 2. Sumamos los montos enviados desde el cliente
            $sumaEnviada = array_sum(array_column($distribucion, 'monto'));

            // 3. Validación de integridad financiera (Margen de 0.01 por redondeo decimal)
            // Usamos abs() para comparar la diferencia absoluta
            if (abs($sumaEnviada - $detalleOriginal->Monto) > 0.01) {
                $totalFormateado = number_format($sumaEnviada, 2);
                $originalFormateado = number_format($detalleOriginal->Monto, 2);

                $validator->errors()->add(
                    'total', 
                    "Error de cuadre: El total distribuido (₡{$totalFormateado}) no coincide con el monto original de la línea (₡{$originalFormateado})."
                );
            }
        });
    }

    /**
     * Personalización de los nombres de los atributos para mensajes de error claros.
     */
    public function attributes()
    {
        return [
            'distribucion.*.id_destino' => 'centro de costo/tercero',
            'distribucion.*.monto'      => 'monto de distribución',
            'distribucion.*.porcentaje' => 'porcentaje',
        ];
    }
}