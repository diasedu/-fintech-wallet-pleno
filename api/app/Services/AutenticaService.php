<?php

namespace App\Services;

use App\Models\UsuarioModel;
use Hash;

class AutenticaService
{
    /**
     * Confere as credenciais e, se válidas, emite um token de API (Sanctum).
     * Retorna o token em texto puro (só é possível lê-lo neste momento) ou null.
     */
    public static function autenticar(string $email, string $senha): ?string
    {
        $usuario = UsuarioModel::where('email', $email)->first();

        if (!$usuario || !Hash::check($senha, $usuario->senha)) {
            return null;
        }

        return $usuario->createToken('web')->plainTextToken;
    }

    /**
     * Revoga o token usado na requisição atual.
     */
    public static function encerrar(UsuarioModel $usuario): void
    {
        $usuario->currentAccessToken()->delete();
    }
}
