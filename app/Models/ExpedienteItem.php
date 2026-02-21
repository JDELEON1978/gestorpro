<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpedienteItem extends Model
{
    protected $table = 'expediente_items';

    protected $fillable = [
        'expediente_id',
        'item_id',
        'nodo_id',
        'estado', // pendiente | entregado | revisado | rechazado | aprobado
        'entregado_en',
        'revisado_en',
        'recibido_por',
        'revisado_por',
        'aprobado',
        'rechazado_regresar_a_nodo_id',
        'observaciones',
    ];

    protected $casts = [
        'entregado_en' => 'datetime',
        'revisado_en' => 'datetime',
        'aprobado' => 'boolean',
    ];

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function nodo(): BelongsTo
    {
        return $this->belongsTo(Nodo::class, 'nodo_id');
    }

    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function regresarANodo(): BelongsTo
    {
        return $this->belongsTo(Nodo::class, 'rechazado_regresar_a_nodo_id');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(Evidencia::class, 'expediente_item_id');
    }
}
