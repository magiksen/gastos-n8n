<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ApiRemovedTest extends TestCase
{
    public function test_gastos_api_endpoints_no_longer_exist(): void
    {
        config(['services.api_token' => 'token-de-prueba']);

        $this->withHeaders(['X-API-Token' => 'token-de-prueba'])
            ->getJson('/api/gastos')
            ->assertNotFound();

        $this->withHeaders(['X-API-Token' => 'token-de-prueba'])
            ->getJson('/api/gastos/vencidos')
            ->assertNotFound();
    }
}
