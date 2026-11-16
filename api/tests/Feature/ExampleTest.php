<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Este projeto é só API ("routes/web.php" está vazio, não há página raiz);
     * o smoke test básico aqui é o endpoint de health check.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)->assertJson(['status' => 'ok']);
    }
}
