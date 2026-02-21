<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nodo extends Model
{
    protected $table = 'nodos';

    protected $fillable = [
        'proceso_id',
        'nombre',
        'tipo_nodo',   // inicio | actividad | decision | fin | conector
        'orden',
        'sla_horas',
        'activo',
    ];

    protected $casts = [
        'orden'  => 'integer',
        'sla_horas' => 'integer',
        'activo' => 'boolean',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class, 'proceso_id');
    }

    public function salientes(): HasMany
    {
        return $this->hasMany(NodoRelacion::class, 'nodo_origen_id');
    }

    public function entrantes(): HasMany
    {
        return $this->hasMany(NodoRelacion::class, 'nodo_destino_id');
    }

    public function plantillaItems(): HasMany
    {
        return $this->hasMany(NodoItem::class, 'nodo_id');
    }
}
