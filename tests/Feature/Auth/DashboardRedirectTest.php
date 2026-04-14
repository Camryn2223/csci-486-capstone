<?php

use App\Models\User;
use App\Models\Organization;

test('a user with no organizations sees the standard dashboard', function () {
    $user = User::factory()->create(['role' => 'interviewer']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Welcome, ' . $user->name);
});

test('a user with exactly one organization is automatically redirected to the organization dashboard', function () {
    $chairman = User::factory()->create(['role' => 'chairman']);
    $org = Organization::create([
        'name' => 'Acme Corp',
        'chairman_id' => $chairman->id,
    ]);
    $org->members()->attach($chairman->id);

    $user = User::factory()->create(['role' => 'interviewer']);
    $org->members()->attach($user->id);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('organizations.show', $org));
});

test('a user with multiple organizations sees the standard dashboard', function () {
    $chairman = User::factory()->create(['role' => 'chairman']);

    $org1 = Organization::create([
        'name' => 'Acme Corp',
        'chairman_id' => $chairman->id,
    ]);
    $org1->members()->attach($chairman->id);

    $org2 = Organization::create([
        'name' => 'Globex Corp',
        'chairman_id' => $chairman->id,
    ]);
    $org2->members()->attach($chairman->id);

    $user = User::factory()->create(['role' => 'interviewer']);
    $org1->members()->attach($user->id);
    $org2->members()->attach($user->id);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Welcome, ' . $user->name);
});