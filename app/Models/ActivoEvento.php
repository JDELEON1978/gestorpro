<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivoEvento extends Model
{
    protected $table = 'activo_eventos';

    protected $fillable = [
        'activo_id',
        'user_id',
        'tipo_evento',
        'fecha_evento',
        'resultado',
        'horas_operacion',
        'valor_medicion',
        'unidad_medicion',
        'costo',
        'proximo_evento_programado',
        'descripcion',
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
        'horas_operacion' => 'decimal:2',
        'valor_medicion' => 'decimal:4',
        'costo' => 'decimal:2',
        'proximo_evento_programado' => 'date',
    ];

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(ActivoEventoEvidencia::class, 'activo_evento_id');
    }
}
