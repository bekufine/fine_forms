<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResponseRequest;
use App\Http\Resources\ResponseResource;
use App\Models\Form;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResponseController extends Controller
{
    public function index(Form $form)
    {
        $this->authorize('view', $form);

        $responses = $form->responses()
            ->with('answers')
            ->latest('submitted_at')
            ->get();

        return ResponseResource::collection($responses);
    }

    public function store(StoreResponseRequest $request, Form $form)
    {
        if (! $form->is_published) {
            throw new NotFoundHttpException;
        }

        $response = DB::transaction(function () use ($request, $form) {
            $response = $form->responses()->create([
                'respondent_email' => $request->validated('respondent_email'),
                'submitted_at' => now(),
            ]);

            foreach ($request->validated('answers') as $answer) {
                $response->answers()->create([
                    'question_id' => $answer['question_id'],
                    'value' => $answer['value'] ?? null,
                ]);
            }

            return $response;
        });

        return (new ResponseResource($response->load('answers')))->response()->setStatusCode(201);
    }
}
