<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
    ];

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id')
            ->withTimestamps();
    }

    public function funciones(): BelongsToMany
    {
        return $this->belongsToMany(Funcion::class, 'funcion_role', 'role_id', 'funcion_id')
            ->withTimestamps();
    }
}
