<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsientoDetalleCentroCosto extends Model
{
    protected $table = 'asientodetallecentrocosto'; 
    protected $primaryKey = 'IdDetalleCC';
    public $timestamps = false; 

    protected $fillable = [
        'IdAsientoDetalle',
        'IdCentroCosto',
        'Monto',
        'Porcentaje',
        'Nota'
    ];
}
