<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Funcion extends Model
{
    protected $table = 'funciones';

    protected $fillable = [
        'nombre',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'funcion_role', 'funcion_id', 'role_id')
            ->withTimestamps();
    }
}
