<?php

use App\Models\Form;
use App\Models\Question;
use App\Models\User;

test('anyone can submit a response to a published form without authentication', function () {
    $form = Form::factory()->published()->create();
    $q1 = Question::factory()->create(['form_id' => $form->id, 'type' => 'text', 'is_required' => true]);
    $q2 = Question::factory()->radio()->create(['form_id' => $form->id, 'is_required' => false]);

    $response = $this->postJson("/api/forms/{$form->id}/responses", [
        'respondent_email' => 'guest@example.com',
        'answers' => [
            ['question_id' => $q1->id, 'value' => 'John Smith'],
            ['question_id' => $q2->id, 'value' => 'Option A'],
        ],
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('responses', [
        'form_id' => $form->id,
        'respondent_email' => 'guest@example.com',
    ]);

    $this->assertDatabaseHas('answers', [
        'question_id' => $q1->id,
    ]);
});

test('submitting a response fails when a required question is missing', function () {
    $form = Form::factory()->published()->create();
    $required = Question::factory()->create(['form_id' => $form->id, 'is_required' => true]);

    $response = $this->postJson("/api/forms/{$form->id}/responses", [
        'answers' => [
            ['question_id' => $required->id, 'value' => ''],
        ],
    ]);

    $response->assertUnprocessable();
});

test('submitting a response to an unpublished form returns not found', function () {
    $form = Form::factory()->create(['is_published' => false]);
    $question = Question::factory()->create(['form_id' => $form->id]);

    $this->postJson("/api/forms/{$form->id}/responses", [
        'answers' => [
            ['question_id' => $question->id, 'value' => 'hi'],
        ],
    ])->assertNotFound();
});

test('only the form owner can list responses', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $form = Form::factory()->published()->create(['user_id' => $owner->id]);

    $this->actingAs($otherUser)->getJson("/api/forms/{$form->id}/responses")->assertForbidden();
    $this->actingAs($owner)->getJson("/api/forms/{$form->id}/responses")->assertOk();
});
