<?php

namespace App\Http\Controllers\AreaLogada;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositoRequest;
use App\Http\Requests\MovimentacaoRequest;
use App\Models\MovimentacaoModel;
use App\Models\UsuarioModel;
use App\Services\Carteira\DepositoService;
use App\Services\Carteira\CarteiraService;
use App\Services\Carteira\SaqueService;
use Exception;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class Carteira extends Controller
{
    public function depositar(MovimentacaoRequest $request): JsonResponse
    {
        $deposito = $request->validated();

        $valor = (float) $deposito['valor'];

        try {
            $id = auth()->id();

            $usuarioModel = new UsuarioModel();
            $usuario = $usuarioModel->obterUsuario($id);

            if (!$usuario) {
                return $this->responderJSON(404, 'Não localizamos o cadastro para o seu usuáiro. Entre em contato com o administrador do sistema.');
            }

            $movimentacaoModel = new MovimentacaoModel([
                'valor' => $valor,
                'data'  => now(),
                'tipo'  => CarteiraService::TIPO_CREDITO,
                'id_usuario' => $usuario->id
            ]);
    

    
            $depositoService = new DepositoService();
            $depositoService->executarMovimentacao($usuario, $movimentacaoModel);

        } catch (InvalidArgumentException $e) {
            return $this->responderJSON(422, $e->getMessage());
        } catch (Exception $e) {
            return $this->responderJSON(500, 'Infelizmente não foi possível realizar a sua movimentação. Tente novamente mais tarde.');
        }

        return $this->responderJSON(201, 'Movimentação realizada com sucesso');
    }

    public function sacar(MovimentacaoRequest $request): JsonResponse
    {
        $deposito = $request->validated();

        $valor = (float) $deposito['valor'];

        try {
            $id = auth()->id();

            $usuarioModel = new UsuarioModel();
            $usuario = $usuarioModel->obterUsuario($id);

            if (!$usuario) {
                return $this->responderJSON(404, 'Não localizamos o cadastro para o seu usuáiro. Entre em contato com o administrador do sistema.');
            }

            $movimentacaoModel = new MovimentacaoModel([
                'valor' => $valor,
                'data'  => now(),
                'tipo'  => CarteiraService::TIPO_DEBITO,
                'id_usuario' => $usuario->id
            ]);
    
            $saqueService = new SaqueService();
            $saqueService->executarMovimentacao($usuario, $movimentacaoModel);

        } catch (InvalidArgumentException $e) {
            return $this->responderJSON(422, $e->getMessage());
        } catch (Exception $e) {
            return $this->responderJSON(500, 'Infelizmente não foi possível realizar a sua movimentação. Tente novamente mais tarde.');
        }

        return $this->responderJSON(201, 'Movimentação realizada com sucesso');
    }
    
}
