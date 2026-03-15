<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tercero extends Model {
    protected $table = 'catalogoterceros';
    protected $primaryKey = 'IdTercero';
    public $timestamps = false;

    // Relación con direcciones
    public function direcciones()
    {
        return $this->hasMany(TerceroDireccion::class, 'IdTercero', 'IdTercero');
    }

    // Relación con contactos
    public function contactos()
    {
        return $this->hasMany(TerceroContacto::class, 'IdTercero', 'IdTercero');
    }

    // Dirección principal
    public function direccionPrincipal()
    {
        return $this->hasOne(TerceroDireccion::class, 'IdTercero', 'IdTercero')
            ->where('EsPrincipal', 1);
    }
}
