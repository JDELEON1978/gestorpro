<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpedienteTransitionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nodo_destino_id' => ['required','integer','exists:nodos,id'],
            'motivo' => ['nullable','string','max:255'],
        ];
    }
}