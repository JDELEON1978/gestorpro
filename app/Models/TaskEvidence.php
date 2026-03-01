<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskEvidence extends Model
{
    // Si tu tabla se llama EXACTAMENTE "task_evidences", puedes omitir esto.
    // Lo dejo explícito para evitar errores.
    protected $table = 'task_evidences';

    protected $fillable = [
        'task_id',
        'nodo_item_id',
        'estado',
        'disk',
        'path',
        'original_name',
        'size_bytes',
        'uploaded_by',
    ];

    protected $casts = [
        'task_id'      => 'integer',
        'nodo_item_id' => 'integer',
        'size_bytes'   => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    // Opcional (solo si tienes el modelo NodoItem)
     public function nodoItem(): BelongsTo
     {
         return $this->belongsTo(NodoItem::class, 'nodo_item_id');
     }

    // Opcional (si quieres relacionar al usuario que subió)
     public function uploader(): BelongsTo
     {
         return $this->belongsTo(User::class, 'uploaded_by');
     }
}