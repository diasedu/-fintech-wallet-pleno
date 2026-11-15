<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();                                              // id autoincrement (chave primária)
            $table->foreignId('id_usuario')->constrained('usuarios');  // obrigatório, FK -> usuarios.id
            $table->enum('tipo', ['D', 'C']);                          // obrigatório: D = débito, C = crédito
            $table->dateTime('data');                                  // obrigatório, data e hora da movimentação
            $table->decimal('valor', 15, 2);                           // obrigatório, mesmo formato de usuarios.saldo
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
};
