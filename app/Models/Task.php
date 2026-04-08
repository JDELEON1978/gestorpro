<?php

namespace App\Models;

use App\Models\TaskActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;



class Task extends Model
{
    protected $table = 'tasks';

    protected $fillable = [
        'project_id',
        'expediente_id',
        'nodo_id',

        // ✅ USAR ESTE como status real del Kanban
        'project_status_id',
        'status_id',

        'title',
        'description',
        'priority',

        'start_at',
        'due_at',

        'assignee_id',   // OJO: tu tabla tiene assignee_id
        'assigned_to',   // si piensas eliminarlo luego, ok, pero no mezcles
        'created_by',

        'position',
        'started_at',
        'completed_at',
        'archived_at',
        'parent_task_id',
        'from_nodo_id',
        
    ];

    protected $casts = [
        'priority'     => 'integer',
        'position'     => 'integer',

        'start_at'     => 'datetime',
        'due_at'       => 'datetime',      // si tu DB es datetime, cámbialo a 'datetime'

        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'archived_at'  => 'datetime',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class, 'task_id');
    }

    public function logActivity(string $event, array $meta = [], ?int $userId = null): TaskActivity
        {
            return TaskActivity::create([
                'task_id' => $this->id,
                'user_id' => $userId ?? Auth::id(),
                'event'   => $event,
                'meta'    => empty($meta) ? null : $meta,
            ]);
        }


    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function nodo(): BelongsTo
    {
        return $this->belongsTo(Nodo::class, 'nodo_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * ✅ Estado real del tablero: project_statuses
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'project_status_id');
    }

    /**
     * ✅ Tu tabla trae assignee_id, úsalo como principal.
     * Si luego quieres eliminar assigned_to, lo hacemos limpio.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
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