<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Catalogo extends Model
{
    protected $table = 'catalogos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CatalogoItem::class, 'catalogo_id');
    }
}
