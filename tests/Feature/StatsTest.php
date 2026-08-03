<?php

use App\Models\Form;
use App\Models\Question;
use App\Models\User;

test('stats aggregate answers per question', function () {
    $user = User::factory()->create();
    $form = Form::factory()->published()->create(['user_id' => $user->id]);
    $choice = Question::factory()->radio()->create(['form_id' => $form->id]);
    $text = Question::factory()->create(['form_id' => $form->id, 'type' => 'text']);

    foreach (['Option A', 'Option A', 'Option B'] as $value) {
        $this->postJson("/api/forms/{$form->id}/responses", [
            'answers' => [
                ['question_id' => $choice->id, 'value' => $value],
                ['question_id' => $text->id, 'value' => "note: {$value}"],
            ],
        ])->assertCreated();
    }

    $response = $this->actingAs($user)->getJson("/api/forms/{$form->id}/stats");

    $response->assertOk()
        ->assertJsonPath('total_responses', 3);

    $questions = collect($response->json('questions'));
    $choiceStat = $questions->firstWhere('question_id', $choice->id);
    $textStat = $questions->firstWhere('question_id', $text->id);

    expect($choiceStat['counts'])->toBe(['Option A' => 2, 'Option B' => 1]);
    expect($textStat['values'])->toHaveCount(3);
});

test('non-owner cannot view stats', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($otherUser)->getJson("/api/forms/{$form->id}/stats")->assertForbidden();
});
