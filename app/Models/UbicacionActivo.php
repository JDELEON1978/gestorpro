<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UbicacionActivo extends Model
{
    protected $table = 'ubicaciones_activos';

    protected $fillable = [
        'workspace_id',
        'central_id',
        'parent_id',
        'codigo',
        'nombre',
        'tipo_ubicacion',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function central(): BelongsTo
    {
        return $this->belongsTo(CentralGeneracion::class, 'central_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function activos(): HasMany
    {
        return $this->hasMany(Activo::class, 'ubicacion_id');
    }
}
