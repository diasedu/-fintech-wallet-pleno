<?php

namespace App\Http\Controllers;

use App\Http\Requests\AutenticarRequest;
use App\Http\Requests\RegistrarUsuarioRequest;
use App\Services\AutenticaService;
use App\Services\CadastroUsuarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class Login extends Controller
{
    public function autenticar(AutenticarRequest $request): JsonResponse
    {
        $dados = $request->validated();

        $email = $dados['email'];
        $senha = $dados['password'];

        $token = AutenticaService::autenticar($email, $senha);

        if (!$token) {
            return $this->responderJSON(401, 'Credenciais inválidas');
        }

        return $this->responderJSON(200, 'Autenticação realizada com sucesso', [
            'token' => $token,
            'tipo'  => 'Bearer',
        ]);
    }

    public function registrar(RegistrarUsuarioRequest $request): JsonResponse
    {
        $dados = $request->validated();
        
        CadastroUsuarioService::cadastrar([
            'nome'     => $dados['name'],
            'email'    => $dados['email'],
            'senha'    => $dados['password'],
        ]);

        return $this->responderJSON(201, __('messages.created'));
    }

    public function logout(Request $request): \Illuminate\Http\Response
    {
        AutenticaService::encerrar($request->user());

        return response()->noContent();
    }
}
