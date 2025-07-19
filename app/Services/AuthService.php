<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data): User
    {
        return User::create([
            'phone' => $data['phone'],
            'name' => $data['name'],
            'address' => $data['address'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function login(array $credentials): ?string
    {
        if (!$token = auth('api')->attempt([
            'phone' => $credentials['phone'],
            'password' => $credentials['password']
        ])) {
            return null;
        }
        return $token;
    }
}
