<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['workspace_id', 'name', 'description', 'archived'];

    public function statuses(): HasMany
    {
        return $this->hasMany(ProjectStatus::class);
    }

    protected static function booted(): void
    {
        static::created(function (Project $project) {
            $defaults = [
                ['name' => 'To do',  'slug' => 'todo',  'position' => 1, 'is_default' => true],
                ['name' => 'Doing',  'slug' => 'doing', 'position' => 2, 'is_default' => true],
                ['name' => 'Done',   'slug' => 'done',  'position' => 3, 'is_default' => true],
            ];
            $project->statuses()->createMany($defaults);
        });
    }
}
