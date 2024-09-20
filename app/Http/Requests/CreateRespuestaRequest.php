<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRespuestaRequest extends FormRequest
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
            'id_evaluacion' => ["required", "exists:evaluaciones,id"],
            'id_estudiante' => ["required", "exists:users,id"],
            "archivo" => ["required", "file", "mimes:pdf", "max:4096"],
            'texto_respuesta' => ["required", "string"],
        ];
    }

    /**
     * Get the custom validation messages for the rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'id_evaluacion.required' => 'El campo evaluación es obligatorio.',
            'id_evaluacion.exists' => 'La evaluación seleccionada no existe.',
            'id_estudiante.required' => 'El campo estudiante es obligatorio.',
            'id_estudiante.exists' => 'El estudiante seleccionado no existe.',
            'archivo.required' => 'El campo archivo es obligatorio.',
            'archivo.file' => 'El archivo debe ser un archivo válido.',
            'archivo.mimes' => 'El archivo debe estar en formato PDF.',
            'archivo.max' => 'El archivo no debe superar los 4 MB.',
            'texto_respuesta.required' => 'El campo respuesta es obligatorio.',
            'texto_respuesta.string' => 'La respuesta debe ser una cadena de texto.',
        ];
    }
}
