<?php

use App\Models\User;

test('a user can update their profile information', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $response = $this->actingAs($user)->put('/user/profile-information', [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);

    $response->assertSessionHasNoErrors();
    expect($user->fresh()->name)->toBe('New Name');
    expect($user->fresh()->email)->toBe('new@example.com');
});

test('a user can update their password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'oldpassword',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasNoErrors();
    
    // Test that they can login with the new password
    $this->post('/logout');
    $loginResponse = $this->post('/login', [
        'email' => $user->email,
        'password' => 'newpassword123',
    ]);
    
    $loginResponse->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('a user cannot update their password without providing the correct current password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'wrongpassword',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasErrorsIn('updatePassword', ['current_password']);
});