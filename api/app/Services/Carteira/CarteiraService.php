<?php

namespace App\Services\Carteira;

use App\Models\MovimentacaoModel;
use App\Models\UsuarioModel;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;


abstract class CarteiraService
{
    const TIPO_CREDITO = 'C';
    const TIPO_DEBITO  = 'D';


    abstract protected function definirTipo(): string;
    
    abstract protected function calcularNovoSaldo(float $saldo, float $valor): float;

    abstract protected function podeExecutarMovimentacao(float $novoSaldo, ?float $valor): bool;

    public function executarMovimentacao(UsuarioModel $usuarioModel, MovimentacaoModel $movimentacaoModel): void
    {   
        if ($movimentacaoModel->tipo <> $this->definirTipo()) {
            throw new InvalidArgumentException('O tipo selecionado é inválido para realizar esta operação.');
        }

        $saldo = (float) $usuarioModel->saldo;
        $valor = (float) $movimentacaoModel->valor;

        $novoSaldo = $this->calcularNovoSaldo($saldo, $valor);

        if (!$this->podeExecutarMovimentacao($novoSaldo, $valor)) {
            throw new InvalidArgumentException('Para realizar o saque, é necessário que o valor do saldo seja maior que o valor do saque solicitado.');
        }

        DB::transaction(function () use ($movimentacaoModel, $usuarioModel, $novoSaldo): void {
            $movimentacaoModel->save();
            
            $usuarioModel->saldo = (float) $novoSaldo;
            $usuarioModel->save();
        });
    }
}


class DepositoService extends CarteiraService
{
    public function definirTipo(): string {
        return self::TIPO_CREDITO;
    }


    public function calcularNovoSaldo(float $saldo, float $valor): float
    {
        return $saldo + $valor;
    }

    public function podeExecutarMovimentacao(float $novoSaldo, ?float $valor): bool
    {
        return $novoSaldo > 0;
    }
}

class SaqueService extends CarteiraService
{
    public function definirTipo(): string {
        return self::TIPO_DEBITO;
    }

    public function calcularNovoSaldo(float $saldo, float $valor): float
    {
        return $saldo - $valor;    
    }

    public function podeExecutarMovimentacao(float $novoSaldo, ?float $valor): bool
    {
        return $novoSaldo >= 0 && $valor >= 0.01;
    }
}