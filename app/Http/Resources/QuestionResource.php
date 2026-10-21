<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'form_id' => $this->form_id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'description_color' => $this->description_color,
            'is_required' => $this->is_required,
            'order' => $this->order,
            'options' => $this->options,
            'max_selections' => $this->max_selections,
            'is_archived' => $this->is_archived,
        ];
    }
}
