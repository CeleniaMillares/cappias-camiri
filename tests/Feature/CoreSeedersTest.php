<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_seeders_can_be_run_more_than_once(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('roles', 5);
        $this->assertDatabaseCount('usuarios', 4);
        $this->assertDatabaseCount('socios', 1);
        $this->assertDatabaseCount('categorias', 5);
        $this->assertDatabaseCount('parametros', 8);
        $this->assertDatabaseCount('ferias', 2);
    }
}
