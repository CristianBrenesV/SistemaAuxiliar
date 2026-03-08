<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentroCosto extends Model {
    protected $table = 'catalogocentroscostos';
    protected $primaryKey = 'IdCentroCosto';
    public $timestamps = false;
}
