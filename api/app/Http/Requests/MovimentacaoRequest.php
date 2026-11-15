<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class MovimentacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'valor' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'valor.required' => 'Informe o valor da movimentação.',
            'valor.numeric'  => 'O valor da movimentação deve ser numérico.',
            'valor.min'      => 'O valor da movimentação deve ser de no mínimo :min.',
        ];
    }
}