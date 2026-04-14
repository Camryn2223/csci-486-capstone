<?php

use App\Models\Organization;
use App\Models\User;

test('guests are redirected to login when accessing the dashboard', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('guests are redirected to login when accessing organizations', function () {
    $response = $this->get(route('organizations.index'));
    $response->assertRedirect('/login');
});

test('guests are redirected to login when trying to access user settings', function () {
    $response = $this->get(route('two-factor.show'));
    $response->assertRedirect('/login');
});

test('guests are redirected to login when trying to view an organizations private members list', function () {
    $chairman = User::factory()->create(['role' => 'chairman']);
    $org = Organization::create(['name' => 'Acme Corp', 'chairman_id' => $chairman->id]);

    $response = $this->get(route('organizations.members', $org));
    
    // Will redirect to login because of the 'auth' middleware on the route group
    $response->assertRedirect('/login');
});