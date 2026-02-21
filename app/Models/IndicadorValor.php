<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicadorValor extends Model
{
    protected $table = 'indicador_valores';

    protected $fillable = [
        'indicador_id',
        'expediente_id',
        'valor',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function indicador(): BelongsTo
    {
        return $this->belongsTo(Indicador::class, 'indicador_id');
    }

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }
}
