<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'respondent_email' => ['nullable', 'email', 'max:255'],
            'answers' => ['present', 'array'],
            'answers.*.question_id' => ['required', 'integer', 'distinct'],
            'answers.*.value' => ['nullable'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $form = $this->route('form');
            $questions = $form->questions()->active()->get();
            $questionIds = $questions->pluck('id')->all();

            $answers = collect($this->input('answers', []));

            foreach ($answers as $index => $answer) {
                if (isset($answer['question_id']) && ! in_array((int) $answer['question_id'], $questionIds, true)) {
                    $validator->errors()->add("answers.{$index}.question_id", 'This question does not belong to the form.');
                }
            }

            foreach ($questions->where('is_required', true) as $question) {
                $answer = $answers->first(fn ($a) => (int) ($a['question_id'] ?? 0) === $question->id);
                $value = $answer['value'] ?? null;

                if ($value === null || $value === '' || $value === []) {
                    $validator->errors()->add('answers', "The question \"{$question->title}\" is required.");
                }
            }
        });
    }
}
