<?php

namespace App\Http\Requests;    

use Illuminate\Foundation\Http\FormRequest;

class CreateTareaRequest extends FormRequest
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
            "id_docente" => ["required", "exists:users,id"],
            "id_curso" => ["required", "exists:cursos,id"],
            "tipo" => ["required", "string", "max:255"],
            "titulo" => ["required", "string", "max:255"],
            "descripcion" => ["required", "string"],
            "archivo" => ["required", "file", "mimes:pdf", "max:4096"],
            "nota_maxima" => ["required", "numeric", "min:0"],
            "fecha_inicio" => ["required", "date"],
            "fecha_fin" => ["required", "date", "after_or_equal:fecha_inicio"],
            "estado" => ["required", "boolean"],
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
            "docente_id.required" => "El campo docente es obligatorio.",
            "docente_id.exists" => "El docente seleccionado no existe.",
            "curso_id.required" => "El campo curso es obligatorio.",
            "curso_id.exists" => "El curso seleccionado no existe.",
            "tipo.required" => "El campo tipo es obligatorio.",
            "tipo.string" => "El campo tipo debe ser una cadena de texto.",
            "tipo.max" => "El campo tipo no debe exceder los 255 caracteres.",
            "titulo.required" => "El campo título es obligatorio.",
            "titulo.string" => "El campo título debe ser una cadena de texto.",
            "titulo.max" => "El campo título no debe exceder los 255 caracteres.",
            "descripcion.required" => "El campo descripción es obligatorio.",
            "descripcion.string" => "El campo descripción debe ser una cadena de texto.",
            "archivo.required" => "El campo archivo es obligatorio.",
            "archivo.file" => "El campo archivo debe ser un archivo.",
            "archivo.mimes" => "El archivo debe ser un archivo PDF.",
            "nota_maxima.required" => "El campo nota máxima es obligatorio.",
            "nota_maxima.numeric" => "El campo nota máxima debe ser un número.",
            "nota_maxima.min" => "El campo nota máxima debe ser mayor o igual a 0.",
            "fecha_inicio.required" => "El campo fecha de inicio es obligatorio.",
            "fecha_inicio.date" => "El campo fecha de inicio debe ser una fecha válida.",
            "fecha_fin.required" => "El campo fecha de fin es obligatorio.",
            "fecha_fin.date" => "El campo fecha de fin debe ser una fecha válida.",
            "fecha_fin.after_or_equal" => "La fecha de fin debe ser igual o posterior a la fecha de inicio.",
            "estado.required" => "El campo estado es obligatorio.",
            "estado.boolean" => "El campo estado debe ser verdadero o falso.",
        ];
    }
}
