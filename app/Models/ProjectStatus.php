<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectStatus extends Model
{
    /**
     * Estados configurables por proyecto.
     */
    protected $fillable = [
        'project_id',
        'name',
        'slug',
        'color',
        'position',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'position'   => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'status_id');
    }
}
