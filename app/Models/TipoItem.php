<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoItem extends Model
{
    protected $table = 'tipos_item';

    protected $fillable = [
        'nombre',
        'categoria', // DOCUMENTO | FORMULARIO | OPERACION
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'tipo_id');
    }
}
