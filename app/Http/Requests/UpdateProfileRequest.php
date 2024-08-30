<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as PasswordRules;
use Illuminate\Validation\Rule;


class UpdateProfileRequest extends FormRequest
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

        // Obtén el ID del usuario de la ruta de la URL
        $userId = $this->route('id');

        return [
            "nombre" => ["nullable", "string"],
            "email" => ["nullable", "email", Rule::unique('users')->ignore($userId)],
            "apellido" => ["nullable", "string"],
            "usuario" => ["nullable", "string", Rule::unique('users')->ignore($userId)],
            "direccion" => ["nullable", "string"],
            "imagen" => ["nullable", "string"],
        ];
    }

    public function messages(): array
    {
        return [
/*             'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ser una dirección de correo electrónico válida.',
            'email.unique' => 'El correo electrónico ya está en uso.',
            'apellido.required' => 'El apellido es obligatorio.',
            'usuario.required' => 'El nombre de usuario es obligatorio.',
            'usuario.unique' => 'El nombre de usuario ya está en uso.',
            'direccion.required' => 'La dirección es obligatoria.',
            'imagen.required' => 'La imagen es obligatoria.', */
        ];
    }
}
