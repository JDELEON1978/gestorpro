<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proceso extends Model
{
    protected $table = 'procesos';

    protected $fillable = [
        'codigo',
        'nombre',
        'version',
        'estado',
        'descripcion',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function nodos(): HasMany
    {
        return $this->hasMany(Nodo::class, 'proceso_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'proceso_id');
    }

    public function expedientes(): HasMany
    {
        return $this->hasMany(Expediente::class, 'proceso_id');
    }

    public function relaciones(): HasMany
    {
        return $this->hasMany(NodoRelacion::class, 'proceso_id');
    }
}
