<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCursoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "titulo" => ["required", "string", "max:255"],
            "imagen" => ["required", "url"],
            "fecha_inicio" => ["required", "date"],
            "fecha_fin" => ["required", "date", "after_or_equal:fecha_inicio"],
            "estado" => ["required", "boolean"],
            "descripcion" => ["required", "string"],
            "duracion" => ["required", "numeric", "min:1"],
            "id_categoria" => ["required", "exists:categorias,id"],
        ];;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'El título del curso es obligatorio.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'duracion.required' => 'La duración es obligatoria.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
            'id_categoria.required' => 'La categoría es obligatoria.',
            'titulo.string' => 'El título del curso debe ser una cadena de caracteres.',
            'fecha_inicio.date' => 'La fecha de inicio del curso debe ser una fecha válida.',
            'fecha_fin.date' => 'La fecha de finalización del curso debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de finalización del curso debe ser posterior o igual a la fecha de inicio.',
            'estado.boolean' => 'El estado del curso debe ser verdadero o falso.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser de tipo jpeg, png, jpg, gif o svg.',
            'imagen.max' => 'La imagen no debe superar los 2048 kilobytes.',
            'descripcion.string' => 'La descripción del curso debe ser una cadena de caracteres.',
            'duracion.numeric' => 'La duración del curso debe ser un valor numérico.',
            'duracion.min' => 'La duración del curso debe ser al menos 1.',
            'id_categoria.exists' => 'La categoría seleccionada no existe.',
        ];
    }
}
