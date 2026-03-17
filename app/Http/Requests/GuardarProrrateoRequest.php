<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarProrrateoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id_detalle'                => 'required|exists:asientocontabledetalle,IdAsientoDetalle',
            'es_tercero'                => 'required|boolean', 
            'distribucion'              => 'required|array|min:1',
            'distribucion.*.id_destino' => 'required',
            'distribucion.*.monto'      => 'required|numeric|min:0.01',
            'distribucion.*.porcentaje' => 'required|numeric',
            'distribucion.*.nota'       => 'nullable|string|max:255', 
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $idDetalle = $this->input('id_detalle');
            $distribucion = $this->input('distribucion', []);

            $detalleOriginal = \App\Models\AsientoContableDetalle::find($idDetalle);

            if (!$detalleOriginal) {
                $validator->errors()->add('id_detalle', 'La línea del asiento seleccionada no es válida.');
                return;
            }

            $sumaEnviada = array_sum(array_column($distribucion, 'monto'));

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

    public function attributes()
    {
        return [
            'distribucion.*.id_destino' => 'centro de costo/tercero',
            'distribucion.*.monto'      => 'monto de distribución',
            'distribucion.*.porcentaje' => 'porcentaje',
        ];
    }
}