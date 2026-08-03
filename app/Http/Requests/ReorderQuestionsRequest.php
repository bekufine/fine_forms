<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('form')) ?? false;
    }

    public function rules(): array
    {
        return [
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'integer', 'exists:questions,id'],
            'order.*.order' => ['required', 'integer', 'min:0'],
        ];
    }
}
