<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogoItem extends Model
{
    protected $table = 'catalogo_items';

    protected $fillable = [
        'catalogo_id',
        'codigo',
        'valor',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function catalogo(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'catalogo_id');
    }
}
