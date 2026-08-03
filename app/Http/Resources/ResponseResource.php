<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'form_id' => $this->form_id,
            'respondent_email' => $this->respondent_email,
            'submitted_at' => $this->submitted_at,
            'answers' => $this->whenLoaded('answers', function () {
                return $this->answers->map(fn ($answer) => [
                    'id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'value' => $answer->value,
                ]);
            }),
        ];
    }
}
