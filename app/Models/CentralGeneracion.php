<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentralGeneracion extends Model
{
    protected $table = 'centrales_generacion';

    protected $fillable = [
        'workspace_id',
        'codigo',
        'nombre',
        'tipo_central',
        'capacidad_mw',
        'empresa_operadora',
        'ubicacion_referencia',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'capacidad_mw' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function ubicaciones(): HasMany
    {
        return $this->hasMany(UbicacionActivo::class, 'central_id');
    }

    public function activos(): HasMany
    {
        return $this->hasMany(Activo::class, 'central_id');
    }
}
