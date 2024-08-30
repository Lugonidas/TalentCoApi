<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateArchivoLeccionRequest extends FormRequest
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
            'nombre' => 'required|string',
            'id_leccion' => 'required|exists:lecciones,id',
            'tipo' => 'required|string',
            'ubicacion' => 'required|file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,flv,pdf|max:51200',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del archivo es requerido.',
            'id_leccion.required' => 'El ID de la lección es requerido.',
            'id_leccion.exists' => 'La lección especificada no existe.',
            'tipo.required' => 'El tipo de archivo es requerido.',
            'ubicacion.required' => 'El archivo es requerido.',
            'ubicacion.file' => 'Debe subir un archivo válido.',
            'ubicacion.mimes' => 'El archivo debe ser un PDF, imagen (JPEG, PNG) o video (MP4).',
            'ubicacion.max' => 'El archivo no debe superar los 20MB.',
        ];
    }
}
