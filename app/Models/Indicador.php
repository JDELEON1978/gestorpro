<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicador extends Model
{
    protected $table = 'indicadores';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'formula',
        'frecuencia',
    ];

    public function valores(): HasMany
    {
        return $this->hasMany(IndicadorValor::class, 'indicador_id');
    }
}
