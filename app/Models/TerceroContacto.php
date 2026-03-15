<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerceroContacto extends Model
{
    protected $table = 'tercero_contactos';
    protected $primaryKey = 'IdContacto';
    public $timestamps = true;

    protected $fillable = [
        'IdTercero',
        'NombreContacto',
        'Cargo',
        'Email',
        'Telefono',
        'TipoContacto',
        'Estado'
    ];

    // Relación con Tercero
    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'IdTercero', 'IdTercero');
    }
}