<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NodoRelacion extends Model
{
    protected $table = 'nodo_relaciones';

    protected $fillable = [
        'proceso_id',
        'nodo_origen_id',
        'nodo_destino_id',
        'condicion',
        'prioridad',
    ];

    protected $casts = [
        'prioridad' => 'integer',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class, 'proceso_id');
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(Nodo::class, 'nodo_origen_id');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(Nodo::class, 'nodo_destino_id');
    }

}
