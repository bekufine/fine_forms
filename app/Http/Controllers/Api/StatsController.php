<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;

class StatsController extends Controller
{
    private const CHOICE_TYPES = ['radio', 'checkbox', 'select', 'scale'];

    public function show(Form $form)
    {
        $this->authorize('view', $form);

        $questions = $form->questions()->active()->with('answers')->get();

        $stats = $questions->map(function ($question) {
            $values = $question->answers->pluck('value')->filter(fn ($value) => $value !== null && $value !== '');

            $stat = [
                'question_id' => $question->id,
                'title' => $question->title,
                'type' => $question->type,
                'total_answers' => $values->count(),
            ];

            if (in_array($question->type, self::CHOICE_TYPES, true)) {
                $counts = [];

                foreach ($values as $value) {
                    foreach ((array) $value as $option) {
                        $counts[$option] = ($counts[$option] ?? 0) + 1;
                    }
                }

                $stat['counts'] = $counts;
            } else {
                $stat['values'] = $values->values()->all();
            }

            return $stat;
        });

        return response()->json([
            'form_id' => $form->id,
            'total_responses' => $form->responses()->count(),
            'questions' => $stats,
        ]);
    }
}
