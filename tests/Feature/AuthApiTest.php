<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_user()
    {
        $response = $this->postJson('/api/register', [
            'phone' => '79990001122',
            'name' => 'Тестовый Пользователь',
            'address' => 'Тестовый адрес',
            'email' => 'testuser1@example.com',
            'password' => 'secret123',
        ]);
        $response->assertStatus(201)
            ->assertJson(['message' => 'Пользователь зарегистрирован']);
        $this->assertDatabaseHas('users', ['phone' => '79990001122']);
    }

    public function test_login_user()
    {
        $this->postJson('/api/register', [
            'phone' => '79990001123',
            'name' => 'Тестовый Пользователь',
            'address' => 'Тестовый адрес',
            'email' => 'testuser2@example.com',
            'password' => 'secret123',
        ]);
        $response = $this->postJson('/api/login', [
            'phone' => '79990001123',
            'password' => 'secret123',
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'token_type', 'expires_in']);
    }
}
