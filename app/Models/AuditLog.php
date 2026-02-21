<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'usuario_id',
        'entidad',
        'entidad_id',
        'accion',
        'antes_json',
        'despues_json',
        'ip',
    ];

    protected $casts = [
        'antes_json' => 'array',
        'despues_json' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
