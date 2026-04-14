<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

test('users can view the forgot password page', function () {
    $response = $this->get('/forgot-password');

    $response->assertOk();
    $response->assertSee('Reset Password');
});

test('a user can request a password reset link', function () {
    $user = User::factory()->create();

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertSessionHas('status');
    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => $user->email,
    ]);
});

test('a user can reset their password with a valid token', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHas('status');
    expect(Hash::check('new-secure-password', $user->fresh()->password))->toBeTrue();
});