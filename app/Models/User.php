<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- Relaciones (deben quedar DENTRO de la clase) ---

    public function ownedWorkspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'owner_user_id');
    }
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Rol::class, 'role_user', 'user_id', 'role_id')
            ->withTimestamps();
    }

        public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'user_workspace')
            ->withPivot('role')
            ->withTimestamps();
    }

}
