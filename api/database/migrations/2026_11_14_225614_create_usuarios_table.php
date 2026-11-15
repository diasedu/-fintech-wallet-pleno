<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();                           // id autoincrement (chave primária)
            $table->string('nome', 255);            // obrigatório, máx. 255
            $table->string('email', 255)->unique(); // obrigatório, único (cria índice)
            $table->string('senha', 255);           // obrigatório, máx. 255
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};