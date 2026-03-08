<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsientoContableDetalle extends Model {
    protected $table = 'asientocontabledetalle';
    protected $primaryKey = 'IdAsientoDetalle'; 
    public $timestamps = false;

    public function asiento()
    {
        return $this->belongsTo(AsientoContableEncabezado::class, 'IdAsiento');
    }
}