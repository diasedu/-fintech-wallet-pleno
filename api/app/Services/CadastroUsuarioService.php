<?php

namespace App\Services;

use App\Models\UsuarioModel;
use Hash;


class CadastroUsuarioService
{
    public static function cadastrar(array $dados)
    {
        $dados['senha'] = self::encriptarSenha($dados['senha']);

        return UsuarioModel::create($dados);
    }

    private static function encriptarSenha(string $senha): string
    {
        return Hash::make($senha);
    }

}
