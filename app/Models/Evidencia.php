<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidencia extends Model
{
    protected $table = 'evidencias';

    protected $fillable = [
        'expediente_item_id',
        'archivo_path',
        'mime_type',
        'tamano_bytes',
        'hash_sha256',
        'subido_por',
    ];

    public function expedienteItem(): BelongsTo
    {
        return $this->belongsTo(ExpedienteItem::class, 'expediente_item_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}
