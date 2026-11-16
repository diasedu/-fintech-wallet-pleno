<?php

namespace App\Http\Requests;
use App\Rules\SenhaForte;
use Illuminate\Foundation\Http\FormRequest;
class RegistrarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'confirmed', new SenhaForte()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Informe o nome.',
            'email.required'     => 'Informe o e-mail.',
            'email.email'        => 'Informe um e-mail válido.',
            'email.unique'       => 'Este e-mail já está cadastrado.',
            'password.required'  => 'Informe a senha.',
            'password.confirmed' => 'A confirmação de senha não coincide.',
        ];
    }
}