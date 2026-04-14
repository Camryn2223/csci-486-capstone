<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user with 2FA enabled is redirected to challenge during login', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'two_factor_secret' => 'secret',
        'two_factor_confirmed_at' => now(),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect('/two-factor-challenge');
    $this->assertGuest(); // Not fully authenticated yet
});

test('user can log in using a valid recovery code', function () {
    $recoveryCode = '12345-67890';
    
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'two_factor_secret' => 'secret',
        'two_factor_confirmed_at' => now(),
        'two_factor_recovery_codes' => encrypt(json_encode([$recoveryCode])),
    ]);

    // Step 1: Login
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    // Step 2: Challenge
    $response = $this->post('/two-factor-challenge', [
        'recovery_code' => $recoveryCode,
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});