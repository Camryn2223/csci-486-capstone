<?php

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationInvite;

test('the first registered user becomes a chairman automatically', function () {
    $response = $this->post('/register', [
        'name' => 'First User',
        'email' => 'first@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    expect(User::count())->toBe(1);
    expect(User::first()->role)->toBe('chairman');
});

test('subsequent users require a valid invite code to register', function () {
    User::factory()->create(['role' => 'chairman']);

    $response = $this->post('/register', [
        'name' => 'Second User',
        'email' => 'second@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors(['invite_code']);
    expect(User::count())->toBe(1); 
});

test('subsequent users can register with a valid unused invite code', function () {
    $chairman = User::factory()->create(['role' => 'chairman']);
    $org = Organization::create(['name' => 'Test Org', 'chairman_id' => $chairman->id]);
    
    $invite = OrganizationInvite::create([
        'organization_id' => $org->id,
        'created_by' => $chairman->id,
        'code' => 'TESTCODE',
        'used' => false,
    ]);

    $response = $this->post('/register', [
        'name' => 'Second User',
        'email' => 'second@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'invite_code' => 'TESTCODE',
    ]);

    $response->assertRedirect('/dashboard');
    expect(User::count())->toBe(2);
    
    $user = User::where('email', 'second@example.com')->first();
    
    expect($user->role)->toBe('interviewer');
    expect($org->hasMember($user))->toBeTrue();
    expect($invite->fresh()->used)->toBeTrue();
});