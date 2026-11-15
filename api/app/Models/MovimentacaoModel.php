<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
