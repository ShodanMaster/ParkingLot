<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    /**
     * Create a new class instance.
     */
    public function attemptLogin(array $credentials, bool $remember = false): User
    {
        if (!Auth::attempt($credentials, $remember)) {
            throw new AuthenticationException("Invalid credentials.");
        }

        return Auth::user();
    }

}
