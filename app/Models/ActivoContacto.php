<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoContacto extends Model
{
    protected $table = 'activo_contactos';

    protected $fillable = [
        'activo_id',
        'tipo_contacto',
        'nombre',
        'cargo',
        'empresa',
        'telefono',
        'email',
        'notas',
        'principal',
    ];

    protected $casts = [
        'principal' => 'boolean',
    ];

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }
}
