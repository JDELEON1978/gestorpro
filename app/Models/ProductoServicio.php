<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductoServicio extends Model
{
    protected $table = 'productos_servicios';

    protected $fillable = [
        'tipo',   // PRODUCTO | SERVICIO
        'nombre',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function expedientes(): BelongsToMany
    {
        return $this->belongsToMany(Expediente::class, 'expediente_productos_servicios', 'producto_servicio_id', 'expediente_id')
            ->withPivot(['cantidad', 'precio'])
            ->withTimestamps();
    }
}
