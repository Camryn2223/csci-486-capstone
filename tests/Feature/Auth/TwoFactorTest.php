<?php

use App\Models\User;

test('users can view the two factor settings page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('two-factor.show'));

    $response->assertOk();
    $response->assertSee('Two-Factor Authentication');
});

test('users can enable two factor authentication generating a secret', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->actingAs($user)
        ->from(route('two-factor.show'))
        ->post(route('two-factor.store'), [
            'password' => 'password123',
        ]);

    $response->assertRedirect(route('two-factor.show'));
    expect($user->fresh()->two_factor_secret)->not->toBeNull();
    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

test('users cannot confirm two factor authentication with an invalid code', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);
    
    // Enable 2FA first
    $this->actingAs($user)
        ->from(route('two-factor.show'))
        ->post(route('two-factor.store'), [
            'password' => 'password123',
        ]);

    // Try to confirm with fake code
    $response = $this->actingAs($user)
        ->from(route('two-factor.show'))
        ->post(route('two-factor.confirm'), [
            'code' => '000000',
        ]);

    // Simply assert a redirect back (validation failure or custom catch redirect)
    $response->assertRedirect();
    
    // The most important assertion: The database state did not change
    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

test('users can regenerate recovery codes', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);
    
    // Enable 2FA
    $this->actingAs($user)
        ->from(route('two-factor.show'))
        ->post(route('two-factor.store'), [
            'password' => 'password123',
        ]);
    
    $initialCodes = $user->fresh()->two_factor_recovery_codes;

    $response = $this->actingAs($user)
        ->from(route('two-factor.show'))
        ->put(route('two-factor.regenerate'), [
            'password' => 'password123',
        ]);

    $response->assertRedirect(route('two-factor.show'));
    expect($user->fresh()->two_factor_recovery_codes)->not->toBe($initialCodes);
});

test('users can disable two factor authentication', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
        'two_factor_secret' => 'secret',
        'two_factor_confirmed_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->from(route('two-factor.show'))
        ->delete(route('two-factor.destroy'), [
            'password' => 'password123',
        ]);

    $response->assertRedirect(route('two-factor.show'));
    expect($user->fresh()->two_factor_secret)->toBeNull();
});