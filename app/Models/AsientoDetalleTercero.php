<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsientoDetalleTercero extends Model {
    protected $table = 'asientodetalletercero';
    protected $primaryKey = 'IdDetalleTercero';
    public $timestamps = false;

    protected $fillable = [
        'IdAsientoDetalle',
        'IdTercero',
        'Monto',
        'Porcentaje',
        'Nota'
    ];
}
