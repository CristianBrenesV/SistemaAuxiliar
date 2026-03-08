<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsientoContableEncabezado extends Model {
    protected $table = 'asientocontableencabezado';
    protected $primaryKey = 'IdAsiento';
    public $timestamps = false;

    public function detalles() {
        return $this->hasMany(AsientoContableDetalle::class, 'IdAsiento', 'IdAsiento');
    }
}