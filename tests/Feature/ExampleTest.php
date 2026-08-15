<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Sanity test: homepage load tanpa error dan return 200.
     *
     * Sebelumnya test ini broken karena HomeController query ke beberapa
     * tabel yang tidak ada di sqlite :memory: default. Sekarang pakai
     * RefreshDatabase untuk migrate sebelum test jalan.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
