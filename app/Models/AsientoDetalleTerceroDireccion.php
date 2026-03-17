<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsientoDetalleTerceroDireccion extends Model
{
    protected $table = 'asientodetalletercero_direccion';
    protected $primaryKey = 'IdRelacion';
    public $timestamps = true;

    protected $fillable = [
        'IdDetalleTercero',
        'IdDireccion'
    ];

    // Relaciones
    public function detalleTercero()
    {
        return $this->belongsTo(AsientoDetalleTercero::class, 'IdDetalleTercero', 'IdDetalleTercero');
    }

    public function direccion()
    {
        return $this->belongsTo(TerceroDireccion::class, 'IdDireccion', 'IdDireccion');
    }
}