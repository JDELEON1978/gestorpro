<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /**
     * Campos permitidos para asignación masiva.
     * IMPORTANTE:
     * - 'slug' existe en DB y lo estás usando
     */
    protected $fillable = [
        'workspace_id',
        'name',
        'slug',
        'description',
        'archived',
    ];

    /**
     * Un Project pertenece a un Workspace.
     * Esto es lo que te faltaba y por eso tronaba:
     * "Call to undefined relationship [workspace] on model [Project]"
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Estados/columnas configurables por proyecto (Kanban).
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(ProjectStatus::class);
    }

    /**
     * Tareas dentro del proyecto.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Al crear un proyecto se crean columnas default.
     * Nota: cada estado pertenece únicamente a ESTE proyecto.
     */
    protected static function booted(): void
    {
        static::created(function (Project $project) {
            $defaults = [
                ['name' => 'To do', 'slug' => 'todo',  'color' => '#6B7280', 'position' => 1, 'is_default' => true],
                ['name' => 'Doing', 'slug' => 'doing', 'color' => '#2563EB', 'position' => 2, 'is_default' => true],
                ['name' => 'Done',  'slug' => 'done',  'color' => '#16A34A', 'position' => 3, 'is_default' => true],
            ];

            $project->statuses()->createMany($defaults);
        });
    }
}
