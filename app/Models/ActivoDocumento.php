<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoDocumento extends Model
{
    protected $table = 'activo_documentos';

    protected $fillable = [
        'activo_id',
        'user_id',
        'tipo_documento',
        'disk',
        'path',
        'original_name',
        'mime',
        'size_bytes',
        'descripcion',
    ];

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
