<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('form')) ?? false;
    }

    public function rules(): array
    {
        $type = $this->input('type', $this->route('question')?->type);
        $optionsRequired = in_array($type, ['radio', 'checkbox', 'select'], true);

        return [
            'type' => ['sometimes', 'required', 'in:text,textarea,radio,checkbox,select,scale,date'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'is_required' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'options' => [$optionsRequired ? 'required' : 'nullable', 'array'],
            'options.*' => ['string'],
            'is_archived' => ['sometimes', 'boolean'],
        ];
    }
}
