<?php

namespace Tests\Feature;

use App\Models\MovimentacaoModel;
use App\Models\UsuarioModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MovimentacaoCarteiraTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioComSaldo(float $saldo): UsuarioModel
    {
        return UsuarioModel::factory()->create(['saldo' => $saldo]);
    }

    public function test_deposito_valido_atualiza_o_saldo(): void
    {
        $usuario = $this->usuarioComSaldo(100.00);
        Sanctum::actingAs($usuario);

        $response = $this->postJson('/api/area-logada/carteira/depositar', ['valor' => 50]);

        $response->assertStatus(201);
        $this->assertSame('150.00', $usuario->refresh()->saldo);
        $this->assertDatabaseHas('movimentacoes', [
            'id_usuario' => $usuario->id,
            'tipo'       => 'C',
            'valor'      => 50,
        ]);
    }

    public function test_saque_com_saldo_insuficiente_e_rejeitado_e_nao_altera_o_saldo(): void
    {
        $usuario = $this->usuarioComSaldo(50.00);
        Sanctum::actingAs($usuario);

        $response = $this->postJson('/api/area-logada/carteira/sacar', ['valor' => 100]);

        $response->assertStatus(422);
        $this->assertSame('50.00', $usuario->refresh()->saldo);
        $this->assertDatabaseCount('movimentacoes', 0);
    }

    public function test_saque_igual_ao_saldo_total_e_permitido_e_zera_o_saldo(): void
    {
        $usuario = $this->usuarioComSaldo(100.00);
        Sanctum::actingAs($usuario);

        $response = $this->postJson('/api/area-logada/carteira/sacar', ['valor' => 100]);

        $response->assertStatus(201);
        $this->assertSame('0.00', $usuario->refresh()->saldo);
    }

    public function test_valor_invalido_e_rejeitado_no_deposito_e_no_saque(): void
    {
        $usuario = $this->usuarioComSaldo(100.00);
        Sanctum::actingAs($usuario);

        $this->postJson('/api/area-logada/carteira/depositar', ['valor' => 0])->assertStatus(422);
        $this->postJson('/api/area-logada/carteira/sacar', ['valor' => -10])->assertStatus(422);

        $this->assertSame('100.00', $usuario->refresh()->saldo);
        $this->assertDatabaseCount('movimentacoes', 0);
    }

    public function test_saldo_apos_no_historico_reflete_a_sequencia_de_movimentacoes(): void
    {
        $usuario = $this->usuarioComSaldo(0);

        $movimentacaoModel = new MovimentacaoModel();
        $movimentacaoModel->gravarMovimentacao($usuario->id, 'C', 100); // saldo_apos esperado: 100
        $movimentacaoModel->gravarMovimentacao($usuario->id, 'C', 50);  // saldo_apos esperado: 150
        $movimentacaoModel->gravarMovimentacao($usuario->id, 'D', 30);  // saldo_apos esperado: 120

        $pagina = $movimentacaoModel->listarMovimentacoes($usuario->id);

        // listarMovimentacoes ordena do mais recente para o mais antigo (id desc desempata
        // quando "data" coincide no mesmo segundo, então a ordem de criação é preservada).
        $this->assertSame(['120.00', '150.00', '100.00'], $pagina->pluck('saldo_apos')->all());
    }

    public function test_depositar_e_sacar_sem_autenticacao_retornam_401(): void
    {
        $this->postJson('/api/area-logada/carteira/depositar', ['valor' => 10])->assertStatus(401);
        $this->postJson('/api/area-logada/carteira/sacar', ['valor' => 10])->assertStatus(401);
    }
}
