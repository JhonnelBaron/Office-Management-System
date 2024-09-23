<?php

namespace App\Services\Account;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegistrationService
{
    public function register(array $payload)
    {
        $payload['user_type'] = $payload['user_type'] ?? 'job_order';
        $payload['status'] = $payload['status'] ?? 'pending';

        $payload['password'] = Hash::make($payload['password']);
        
        $register = User::create($payload);

        return [
            'data' => $register,
            'status' => 201,
            'message' => 'Account created successfully!'
        ];
    }
}