<?php

namespace Database\Factories;

use App\Models\UsuarioModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<UsuarioModel>
 */
class UsuarioModelFactory extends Factory
{
    protected $model = UsuarioModel::class;

    public function definition(): array
    {
        return [
            'nome'  => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'senha' => Hash::make('Senha-Forte123!'),
        ];
    }
}
