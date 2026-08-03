<?php

use App\Models\Form;
use App\Models\User;

test('guest cannot access forms', function () {
    $this->getJson('/api/forms')->assertUnauthorized();
});

test('authenticated user can create a form', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/forms', [
        'title' => 'Customer Feedback',
        'description' => 'Tell us what you think',
    ]);

    $response->assertCreated()
        ->assertJsonPath('title', 'Customer Feedback')
        ->assertJsonPath('user_id', $user->id);

    $this->assertDatabaseHas('forms', [
        'title' => 'Customer Feedback',
        'user_id' => $user->id,
    ]);
});

test('user only sees their own forms in the index', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Form::factory()->count(2)->create(['user_id' => $user->id]);
    Form::factory()->count(3)->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson('/api/forms');

    $response->assertOk()->assertJsonCount(2);
});

test('owner can view their form', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->getJson("/api/forms/{$form->id}")
        ->assertOk()
        ->assertJsonPath('id', $form->id);
});

test('non-owner cannot view another users form', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($otherUser)->getJson("/api/forms/{$form->id}")
        ->assertForbidden();
});

test('owner can update their form', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->putJson("/api/forms/{$form->id}", [
        'title' => 'Updated title',
        'is_published' => true,
    ])->assertOk()->assertJsonPath('title', 'Updated title');

    expect($form->fresh()->is_published)->toBeTrue();
});

test('non-owner cannot update another users form', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($otherUser)->putJson("/api/forms/{$form->id}", [
        'title' => 'Hacked title',
    ])->assertForbidden();
});

test('owner can delete their form', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->deleteJson("/api/forms/{$form->id}")->assertNoContent();

    $this->assertDatabaseMissing('forms', ['id' => $form->id]);
});

test('non-owner cannot delete another users form', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($otherUser)->deleteJson("/api/forms/{$form->id}")->assertForbidden();

    $this->assertDatabaseHas('forms', ['id' => $form->id]);
});
