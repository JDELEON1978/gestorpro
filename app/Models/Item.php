<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'proceso_id',
        'nombre',
        'categoria', // DOCUMENTO | FORMULARIO | OPERACION
        'tipo_id',
        'requiere_evidencia',
        'activo',
    ];

    protected $casts = [
        'requiere_evidencia' => 'boolean',
        'activo' => 'boolean',
    ];
    
    public function nodos()
    {
        return $this->belongsToMany(Nodo::class, 'nodo_items', 'item_id', 'nodo_id')
            ->withPivot(['obligatorio'])
            ->withTimestamps();
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class, 'proceso_id');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoItem::class, 'tipo_id');
    }

    public function instancias(): HasMany
    {
        return $this->hasMany(ExpedienteItem::class, 'item_id');
    }
}
