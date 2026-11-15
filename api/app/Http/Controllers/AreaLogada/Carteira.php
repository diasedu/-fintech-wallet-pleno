<?php

namespace App\Http\Controllers\AreaLogada;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositoRequest;
use App\Http\Requests\FiltroMovimentacoesRequest;
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

    /**
     * Atende o histórico (lista paginada com filtros) e o dashboard
     * (saldo atual, totais do mês e, com por_pagina=5, as últimas 5 movimentações).
     */
    public function carregarMovimentacoes(FiltroMovimentacoesRequest $request): JsonResponse
    {
        $filtros = $request->validated();

        $pagina    = (int) ($filtros['pagina'] ?? 1);
        $porPagina = (int) ($filtros['por_pagina'] ?? 10);

        try {
            $id = auth()->id();

            $usuarioModel = new UsuarioModel();
            $usuario = $usuarioModel->obterUsuario($id);

            $movimentacaoModel = new MovimentacaoModel();
            $movimentacoes = $movimentacaoModel->listarMovimentacoes($id, $filtros, $porPagina, $pagina);
            $totaisMes     = $movimentacaoModel->totalizarMesAtual($id);

        } catch (Exception $e) {
            return $this->responderJSON(500, 'Infelizmente não foi possível carregar as suas movimentações. Tente novamente mais tarde.');
        }

        return $this->responderJSON(200, '', [
            'resumo' => [
                'saldo'                => $usuario->saldo,
                'total_depositado_mes' => $totaisMes['total_depositado'],
                'total_sacado_mes'     => $totaisMes['total_sacado'],
            ],
            'movimentacoes' => $movimentacoes->items(),
            'paginacao' => [
                'pagina_atual'  => $movimentacoes->currentPage(),
                'por_pagina'    => $movimentacoes->perPage(),
                'total'         => $movimentacoes->total(),
                'ultima_pagina' => $movimentacoes->lastPage(),
            ],
        ]);
    }

}

