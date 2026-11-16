<?php

namespace App\Rules;

use App\Support\PoliticaSenha;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Regra genérica de "senha forte": toda validação desse tipo no sistema deve usar esta
 * classe (em vez de repetir regex/min em cada FormRequest), que delega o critério para
 * App\Support\PoliticaSenha — a mesma fonte usada pelo endpoint consumido pelo frontend.
 */
class SenhaForte implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !PoliticaSenha::senhaForte($value)) {
            $fail(PoliticaSenha::mensagem());
        }
    }
}
