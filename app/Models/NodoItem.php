<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NodoItem extends Model
{
    protected $table = 'nodo_items';

    protected $fillable = [
        'nodo_id',
        'item_id',
        'obligatorio',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
    ];

    public function nodo(): BelongsTo
    {
        return $this->belongsTo(Nodo::class, 'nodo_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
