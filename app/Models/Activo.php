<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activo extends Model
{
    protected $table = 'activos';

    protected $fillable = [
        'workspace_id',
        'central_id',
        'ubicacion_id',
        'categoria_id',
        'parent_id',
        'responsable_user_id',
        'codigo',
        'nombre',
        'tag',
        'estado_operativo',
        'criticidad',
        'fabricante',
        'modelo',
        'numero_serie',
        'tipo_combustible',
        'proveedor_instalador',
        'fecha_fabricacion',
        'fecha_instalacion',
        'fecha_puesta_servicio',
        'potencia_nominal_kw',
        'voltaje_nominal_v',
        'corriente_nominal_a',
        'horas_operacion',
        'costo_adquisicion',
        'valor_libros',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'fecha_fabricacion' => 'date',
        'fecha_instalacion' => 'date',
        'fecha_puesta_servicio' => 'date',
        'potencia_nominal_kw' => 'decimal:2',
        'voltaje_nominal_v' => 'decimal:2',
        'corriente_nominal_a' => 'decimal:2',
        'horas_operacion' => 'decimal:2',
        'costo_adquisicion' => 'decimal:2',
        'valor_libros' => 'decimal:2',
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

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(UbicacionActivo::class, 'ubicacion_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaActivo::class, 'categoria_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_user_id');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(ActivoEvento::class, 'activo_id');
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(ActivoContacto::class, 'activo_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(ActivoDocumento::class, 'activo_id');
    }
}
