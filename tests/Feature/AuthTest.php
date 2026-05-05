<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'New Writer',
            'email' => 'writer@gooddocs.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.user.email', 'writer@gooddocs.test');

        $this->assertDatabaseHas('users', [
            'email' => 'writer@gooddocs.test',
        ]);
    }
}
