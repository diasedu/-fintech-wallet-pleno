<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class FiltroMovimentacoesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo'         => ['nullable', 'in:C,D'],
            'data_inicial' => ['nullable', 'date_format:Y-m-d'],
            'data_final'   => ['nullable', 'date_format:Y-m-d', 'after_or_equal:data_inicial'],
            'pagina'       => ['nullable', 'integer', 'min:1'],
            'por_pagina'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.in'                   => 'O tipo deve ser C (crédito) ou D (débito).',
            'data_inicial.date_format'  => 'Informe a data inicial no formato AAAA-MM-DD.',
            'data_final.date_format'    => 'Informe a data final no formato AAAA-MM-DD.',
            'data_final.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
            'pagina.integer'            => 'A página deve ser um número inteiro.',
            'pagina.min'                => 'A página deve ser de no mínimo :min.',
            'por_pagina.integer'        => 'A quantidade por página deve ser um número inteiro.',
            'por_pagina.min'            => 'A quantidade por página deve ser de no mínimo :min.',
            'por_pagina.max'            => 'A quantidade por página deve ser de no máximo :max.',
        ];
    }
}
