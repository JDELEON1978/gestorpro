<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaActivo extends Model
{
    protected $table = 'categorias_activos';

    protected $fillable = [
        'workspace_id',
        'parent_id',
        'codigo',
        'nombre',
        'clase_activo',
        'requiere_serie',
        'vida_util_anios',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'requiere_serie' => 'boolean',
        'activo' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
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
        return $this->hasMany(Activo::class, 'categoria_id');
    }
}
