<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expediente extends Model
{
    protected $table = 'expedientes';

    protected $fillable = [
        'proceso_id',
        'nodo_actual_id',
        'correlativo',
        'titulo',
        'estado',      
        'creado_por',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class, 'proceso_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpedienteItem::class, 'expediente_id');
    }
    public function nodoActual(): BelongsTo
    {
        return $this->belongsTo(Nodo::class, 'nodo_actual_id');
    }
}
