<?php

use App\Models\Form;
use App\Models\Question;

test('published form is publicly visible with its active questions', function () {
    $form = Form::factory()->published()->create();
    $active = Question::factory()->create(['form_id' => $form->id]);
    Question::factory()->archived()->create(['form_id' => $form->id]);

    $response = $this->getJson("/api/public/forms/{$form->id}");

    $response->assertOk()->assertJsonCount(1, 'questions');
    expect($response->json('questions.0.id'))->toBe($active->id);
});

test('unpublished form is not publicly visible', function () {
    $form = Form::factory()->create(['is_published' => false]);

    $this->getJson("/api/public/forms/{$form->id}")->assertNotFound();
});
