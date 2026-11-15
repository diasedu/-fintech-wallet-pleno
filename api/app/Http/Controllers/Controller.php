<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function responderJSON(int $cod_http, string $mensagem = '', array $dados = []): JsonResponse
    {
        $resposta = [];

        if (!empty($dados)) {
            $resposta['data'] = $dados;
        }

        if (!empty($mensagem)) {
            $resposta['message'] = $mensagem;
        }

        return response()->json($resposta)->setStatusCode($cod_http);
    }
}
