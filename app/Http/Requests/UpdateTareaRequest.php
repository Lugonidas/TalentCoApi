<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTareaRequest extends FormRequest
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
        $rules = [
            "tipo" => ["required", "string", "max:255"],
            "titulo" => ["required", "string", "max:255"],
            "descripcion" => ["required", "string"],
            "nota_maxima" => ["required", "string"],
            "fecha_inicio" => ["required", "string"],
            "fecha_fin" => ["required", "string"],
            "estado" => ["required", "boolean"],
        ];

        // Validar imagen solo si se proporciona una nueva
        if ($this->hasFile('archivo')) {
            $rules['archivo'] = ["nullable", "file", "mimes:pdf", "max:4096"];
        }

        return $rules;
    }

    /**
     * Get the custom messages for the validator errors.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'tipo.required' => 'El tipo de tarea es obligatorio.',
            'tipo.string' => 'El tipo de tarea debe ser una cadena de texto.',
            'tipo.max' => 'El tipo de tarea no puede tener más de 255 caracteres.',
            'archivo.file' => 'El archivo debe ser un archivo válido.',
            'archivo.mimes' => 'El archivo debe ser un archivo PDF.',
            'archivo.max' => 'El archivo no puede exceder los 4MB.',
            'titulo.required' => 'El título de la tarea es obligatorio.',
            'titulo.string' => 'El título debe ser una cadena de texto.',
            'titulo.max' => 'El título no puede tener más de 255 caracteres.',
            'descripcion.required' => 'La descripción de la tarea es obligatoria.',
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'nota_maxima.required' => 'La nota máxima es obligatoria.',
            'nota_maxima.string' => 'La nota máxima debe ser una cadena de texto.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.string' => 'La fecha de inicio debe ser una cadena de texto.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.string' => 'La fecha de fin debe ser una cadena de texto.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }
}
