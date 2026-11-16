<?php

namespace App\Support;

/**
 * Fonte única da regra de "senha forte" do sistema: tamanho mínimo e regex de complexidade.
 * Toda validação de senha forte (App\Rules\SenhaForte, outras que vierem a existir, e o
 * frontend via GET /senha/politica) deve se basear nesta classe — nunca redefinir a regra
 * em outro lugar.
 */
class PoliticaSenha
{
    public const MIN_CARACTERES = 12;


    private const REGEX_COMPLEXIDADE =
        '(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{' . self::MIN_CARACTERES . ',}';

    public static function regexPattern(): string
    {
        return self::REGEX_COMPLEXIDADE;
    }

    public static function senhaForte(string $senha): bool
    {
        return (bool) preg_match('/^' . self::REGEX_COMPLEXIDADE . '$/u', $senha);
    }

    public static function mensagem(): string
    {
        return sprintf(
            'A senha deve ter no mínimo %d caracteres e incluir letra maiúscula, letra minúscula, número e caractere especial.',
            self::MIN_CARACTERES
        );
    }

    /**
     * Enviado ao frontend (GET /senha/politica) para que o formulário de cadastro valide
     * com a mesma regra do backend, em vez de duplicar os critérios no JS.
     */
    public static function descricaoApi(): array
    {
        return [
            'min'      => self::MIN_CARACTERES,
            'pattern'  => self::regexPattern(),
            'flags'    => 'u',
            'mensagem' => self::mensagem(),
        ];
    }
}
