<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MovimentacaoModel extends Model
{
    public const TIPO_DEBITO = 'D';
    public const TIPO_CREDITO = 'C';

    protected $table = 'movimentacoes';

    protected $fillable = [
        'id_usuario',
        'tipo',
        'data',
        'valor',
    ];

    protected $casts = [
        'data'  => 'datetime',
        'valor' => 'decimal:2',
        // Não é coluna da tabela: é calculado em listarMovimentacoes().
        'saldo_apos' => 'decimal:2',
    ];

    // A tabela não possui as colunas "created_at" e "updated_at".
    public $timestamps = false;

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario');
    }

    public function gravarMovimentacao(int $usuarioID, string $tipo, float $valor): void
    {
        $movimentacao = new MovimentacaoModel([
            'id_usuario' => $usuarioID,
            'tipo'       => $tipo,
            'data'       => now(),
            'valor'      => $valor,
        ]);

        $movimentacao->save();
    }

    /**
     * Lista paginada das movimentações do usuário, da mais recente para a mais antiga.
     * Filtros aceitos (todos opcionais): tipo, data_inicial e data_final (AAAA-MM-DD).
     */
    public function listarMovimentacoes(int $usuarioID, array $filtros = [], int $porPagina = 10, int $pagina = 1): LengthAwarePaginator
    {
        // O saldo após cada operação é a soma acumulada de créditos e débitos até ela.
        // Fica em uma subconsulta para ser calculado sobre TODAS as movimentações do
        // usuário: os filtros são aplicados só depois, sem alterar o acumulado.
        $comSaldo = $this->newQuery()
            ->select('id', 'tipo', 'data', 'valor')
            ->selectRaw(
                'SUM(CASE WHEN tipo = ? THEN valor ELSE -valor END) OVER (ORDER BY data, id) AS saldo_apos',
                [self::TIPO_CREDITO]
            )
            ->where('id_usuario', $usuarioID);

        $consulta = $this->newQuery()->fromSub($comSaldo, $this->getTable());

        if (!empty($filtros['tipo'])) {
            $consulta->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['data_inicial'])) {
            $consulta->where('data', '>=', Carbon::parse($filtros['data_inicial'])->startOfDay());
        }

        if (!empty($filtros['data_final'])) {
            $consulta->where('data', '<=', Carbon::parse($filtros['data_final'])->endOfDay());
        }

        return $consulta
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->paginate($porPagina, ['*'], 'pagina', $pagina);
    }

    /**
     * Total depositado (créditos) e total sacado (débitos) pelo usuário no mês atual.
     */
    public function totalizarMesAtual(int $usuarioID): array
    {
        $totais = $this->newQuery()
            ->where('id_usuario', $usuarioID)
            ->whereBetween('data', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('tipo')
            ->selectRaw('tipo, SUM(valor) AS total')
            ->pluck('total', 'tipo');

        return [
            'total_depositado' => number_format((float) ($totais[self::TIPO_CREDITO] ?? 0), 2, '.', ''),
            'total_sacado'     => number_format((float) ($totais[self::TIPO_DEBITO] ?? 0), 2, '.', ''),
        ];
    }
}
