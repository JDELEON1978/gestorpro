<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpedienteItemReviewRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'accion' => ['required','in:aprobar,rechazar'],
            'observaciones' => ['nullable','string'],
            'rechazado_regresar_a_nodo_id' => ['nullable','integer','exists:nodos,id'],
        ];
    }
}