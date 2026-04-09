<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoEventoEvidencia extends Model
{
    protected $table = 'activo_evento_evidencias';

    protected $fillable = [
        'activo_evento_id',
        'user_id',
        'disk',
        'path',
        'original_name',
        'mime',
        'size_bytes',
        'descripcion',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(ActivoEvento::class, 'activo_evento_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
