<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    /**
     * MVP: columnas base para operar Kanban/List/Tabla.
     * Ajusta si tu migration tiene nombres distintos.
     */
    protected $fillable = [
        'project_id',
        'status_id',
        'title',
        'description',
        'priority',
        'assigned_to',
        'created_by',
        'due_at',
        'started_at',
        'completed_at',
        'position',
        'archived_at',
    ];

    protected $casts = [
        'due_at'       => 'datetime',
        'start_at' => 'date',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'archived_at'  => 'datetime',
        'position'     => 'integer',
        'priority'     => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
   public function files()
    {
        return $this->hasMany(TaskFile::class, 'task_id');
    }

}
