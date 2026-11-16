<?php

namespace Database\Seeders;

use App\Models\UsuarioModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Cria um usuário "seed": login conhecido para usar em dev/demo sem passar pelo
     * cadastro manual. "firstOrCreate" evita duplicar o registro se o seeder rodar de novo.
     *
     * Login: demo@gmail.com / Demo@Senha123! (domínio real: a validação de login
     * exige e-mail com DNS válido — ver App\Http\Requests\AutenticarRequest).
     */
    public function run(): void
    {
        UsuarioModel::firstOrCreate(
            ['email' => 'demo@gmail.com'],
            [
                'nome'  => 'Usuário Demo',
                'senha' => Hash::make('Demo@Senha123!'),
            ]
        );
    }
}
