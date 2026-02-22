<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NodoRelacion extends Model
{
    protected $table = 'nodo_relaciones';

    protected $fillable = [
        'proceso_id',
        'nodo_origen_id',
        'nodo_destino_id',
        'condicion',
        'prioridad',
        'out_side',
        'out_offset',
        'bend_x','bend_y',
    ];

    protected $casts = [
        'prioridad' => 'integer',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class, 'proceso_id');
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(Nodo::class, 'nodo_origen_id');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(Nodo::class, 'nodo_destino_id');
    }
    public function updateRelacionBend(Request $r, NodoRelacion $relacion)
    {
        // Validación: coordenadas del canvas
        $data = $r->validate([
            'bend_x' => ['nullable','integer','min:-5000','max:5000'],
            'bend_y' => ['nullable','integer','min:-5000','max:5000'],
        ]);

        $relacion->bend_x = $data['bend_x'] ?? null;
        $relacion->bend_y = $data['bend_y'] ?? null;
        $relacion->save();

        return response()->json(['ok' => true]);
    }

}
