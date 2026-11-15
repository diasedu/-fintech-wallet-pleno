<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class AutenticarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'Informe o e-mail.',
            'email.email'       => 'Informe um e-mail válido.',
            'password.required' => 'Informe a senha.',
        ];
    }
}