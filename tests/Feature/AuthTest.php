<?php

use App\Models\User;

test('user can register and is logged in', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
    ]);

    $response->assertCreated();
    $this->assertAuthenticated();
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk();
    $this->assertAuthenticatedAs($user);
});

test('user cannot login with incorrect credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable();
    $this->assertGuest();
});

test('authenticated user can fetch their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/user');

    $response->assertOk()->assertJsonPath('user.id', $user->id);
});

test('user can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/api/logout')->assertNoContent();
    $this->assertGuest('web');
});
