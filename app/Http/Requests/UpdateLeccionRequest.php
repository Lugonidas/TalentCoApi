<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeccionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "titulo" => ["required", "string"],
            "descripcion" => ["required", "string"],
            "estado" => ["required", "boolean"],
            "orden" => ["required", "numeric"]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            "titulo.required" => "El título de la lección es obligatorio.",
            "titulo.string" => "El título de la lección debe ser una cadena de caracteres.",
            "descripcion.required" => "La descripción de la lección es obligatoria.",
            "descripcion.string" => "La descripción de la lección debe ser una cadena de caracteres.",
            "estado.required" => "El estado de la lección es obligatorio.",
            "estado.boolean" => "El estado de la lección debe ser verdadero o falso.",
            "orden.required" => "El orden de la lección es obligatorio.",
            "orden.numeric" => "El orden de la lección debe ser un valor numérico.",
        ];
    }
}
