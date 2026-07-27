<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        // Route '/' dilindungi middleware 'auth', sehingga guest
        // harus diarahkan ke halaman login (302), bukan 200.
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
