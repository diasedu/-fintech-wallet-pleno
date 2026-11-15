<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class UsuarioModel extends Authenticatable
{
    // Permite criar/revogar tokens de API: $usuario->createToken(), ->tokens(), ->currentAccessToken()
    use HasApiTokens;

    protected $table = 'usuarios';
    protected $fillable = [
        'nome',
        'email',
        'senha',
    ];

    protected $hidden = [
        'senha',
    ];

    public $timestamps = false;

    // A coluna da senha é "senha" (e não "password").
    public function getAuthPasswordName(): string
    {
        return 'senha';
    }

    // A tabela não possui a coluna "remember_token".
    public function getRememberTokenName(): string
    {
        return '';
    }
}
