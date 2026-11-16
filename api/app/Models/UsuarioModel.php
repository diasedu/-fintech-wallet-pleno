<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class UsuarioModel extends Authenticatable
{
    // Permite criar/revogar tokens de API: $usuario->createToken(), ->tokens(), ->currentAccessToken()
    use HasApiTokens;
    // Permite UsuarioModel::factory(), usado pelo seeder e pelos testes.
    use HasFactory;

    protected $table = 'usuarios';
    protected $fillable = [
        'nome',
        'email',
        'senha',
    ];

    protected $hidden = [
        'senha',
    ];

    // "saldo" fica fora do $fillable de propósito: só deve mudar por regra de negócio.
    protected $casts = [
        'saldo' => 'decimal:2',
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

    public function obterUsuario(int $usuarioId): UsuarioModel
    {
        return $this->find($usuarioId);
    }
}
