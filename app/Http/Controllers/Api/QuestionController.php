<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderQuestionsRequest;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Form;
use App\Models\Question;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuestionController extends Controller
{
    private function ensureBelongsToForm(Form $form, Question $question): void
    {
        if ($question->form_id !== $form->id) {
            throw new NotFoundHttpException;
        }
    }

    public function index(Form $form)
    {
        $this->authorize('view', $form);

        $questions = $form->questions()->active()->get();

        return QuestionResource::collection($questions);
    }

    public function store(StoreQuestionRequest $request, Form $form)
    {
        $order = $request->validated('order') ?? $form->questions()->max('order') + 1;

        $data = $request->validated();
        if ($data['type'] === 'section') {
            $data['is_required'] = false;
            $data['options'] = null;
        }

        $question = $form->questions()->create([
            ...$data,
            'order' => $order,
        ]);

        return (new QuestionResource($question))->response()->setStatusCode(201);
    }

    public function update(UpdateQuestionRequest $request, Form $form, Question $question)
    {
        $this->ensureBelongsToForm($form, $question);

        $data = $request->validated();
        if (($data['type'] ?? $question->type) === 'section') {
            $data['is_required'] = false;
            $data['options'] = null;
        }

        $question->update($data);

        return new QuestionResource($question);
    }

    public function destroy(Form $form, Question $question)
    {
        $this->authorize('update', $form);
        $this->ensureBelongsToForm($form, $question);

        $question->update(['is_archived' => true]);

        return response()->noContent();
    }

    public function reorder(ReorderQuestionsRequest $request, Form $form)
    {
        foreach ($request->validated('order') as $item) {
            Question::query()
                ->where('id', $item['id'])
                ->where('form_id', $form->id)
                ->update(['order' => $item['order']]);
        }

        return QuestionResource::collection($form->questions()->active()->get());
    }
}
