<?php

use App\Models\Form;
use App\Models\Question;
use App\Models\User;

test('owner can add a question to their form', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/api/forms/{$form->id}/questions", [
        'type' => 'text',
        'title' => 'What is your name?',
        'is_required' => true,
    ]);

    $response->assertCreated()->assertJsonPath('title', 'What is your name?');

    $this->assertDatabaseHas('questions', [
        'form_id' => $form->id,
        'title' => 'What is your name?',
    ]);
});

test('creating a choice question requires options', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->postJson("/api/forms/{$form->id}/questions", [
        'type' => 'radio',
        'title' => 'Pick one',
    ])->assertUnprocessable()->assertJsonValidationErrors('options');

    $this->actingAs($user)->postJson("/api/forms/{$form->id}/questions", [
        'type' => 'radio',
        'title' => 'Pick one',
        'options' => ['Yes', 'No'],
    ])->assertCreated();
});

test('non-owner cannot add a question to another users form', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($otherUser)->postJson("/api/forms/{$form->id}/questions", [
        'type' => 'text',
        'title' => 'Sneaky question',
    ])->assertForbidden();
});

test('index only returns active questions ordered', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->id]);

    $q1 = Question::factory()->create(['form_id' => $form->id, 'order' => 1]);
    $q2 = Question::factory()->create(['form_id' => $form->id, 'order' => 0]);
    Question::factory()->archived()->create(['form_id' => $form->id, 'order' => 2]);

    $response = $this->actingAs($user)->getJson("/api/forms/{$form->id}/questions");

    $response->assertOk()->assertJsonCount(2);
    expect($response->json('0.id'))->toBe($q2->id);
    expect($response->json('1.id'))->toBe($q1->id);
});

test('deleting a question archives it instead of removing it', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->id]);
    $question = Question::factory()->create(['form_id' => $form->id]);

    $this->actingAs($user)->deleteJson("/api/forms/{$form->id}/questions/{$question->id}")
        ->assertNoContent();

    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'is_archived' => true,
    ]);
});

test('owner can reorder questions', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->id]);

    $q1 = Question::factory()->create(['form_id' => $form->id, 'order' => 0]);
    $q2 = Question::factory()->create(['form_id' => $form->id, 'order' => 1]);

    $this->actingAs($user)->patchJson("/api/forms/{$form->id}/questions/reorder", [
        'order' => [
            ['id' => $q1->id, 'order' => 1],
            ['id' => $q2->id, 'order' => 0],
        ],
    ])->assertOk();

    expect($q1->fresh()->order)->toBe(1);
    expect($q2->fresh()->order)->toBe(0);
});

test('a question from another form cannot be updated through a different forms route', function () {
    $user = User::factory()->create();
    $formA = Form::factory()->create(['user_id' => $user->id]);
    $formB = Form::factory()->create(['user_id' => $user->id]);
    $question = Question::factory()->create(['form_id' => $formB->id]);

    $this->actingAs($user)->patchJson("/api/forms/{$formA->id}/questions/{$question->id}", [
        'title' => 'Hijacked',
    ])->assertNotFound();
});
