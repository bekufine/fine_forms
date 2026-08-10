<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('form')) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:text,textarea,radio,checkbox,select,scale,date,section'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_required' => ['boolean'],
            'order' => ['integer', 'min:0'],
            'options' => ['nullable', 'array', 'required_if:type,radio,checkbox,select'],
            'options.*' => ['string'],
        ];
    }
}
