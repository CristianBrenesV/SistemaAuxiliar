<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerceroDireccion extends Model
{
    protected $table = 'tercero_direcciones';
    protected $primaryKey = 'IdDireccion';
    public $timestamps = true;

    protected $fillable = [
        'IdTercero',
        'Alias',
        'Provincia',
        'Canton',
        'Distrito',
        'DireccionExacta',
        'EsPrincipal',
        'Estado'
    ];

    // Relación con Tercero
    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'IdTercero', 'IdTercero');
    }
}