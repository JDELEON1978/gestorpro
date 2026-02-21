<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariableControl extends Model
{
    protected $table = 'variables_control';

    protected $fillable = [
        'clave',
        'valor',
        'tipo',   // string | int | bool | json
        'scope',  // GLOBAL | PROCESO
        'proceso_id',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class, 'proceso_id');
    }
}
