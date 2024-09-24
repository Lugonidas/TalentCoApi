<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateNotasEstudiantesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "id_evaluacion" => ["required", "string"],
            "id_estudiante" => ["required", "string"],
            "nota" => ["required", "string"],
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'id_evaluacion.required' => 'La evaluación es obligatoria.',
            'id_estudiante.required' => 'El estudiante es obligatorio.',
            'nota.required' => 'La nota es obligatoria.',
            'nota.string' => 'La nota debe ser una cadena de texto.',
        ];
    }
}
