<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFormRequest;
use App\Http\Requests\UpdateFormRequest;
use App\Http\Resources\FormResource;
use App\Models\Form;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FormController extends Controller
{
    public function index(Request $request)
    {
        $forms = $request->user()
            ->forms()
            ->withCount('responses')
            ->latest()
            ->get();

        return FormResource::collection($forms);
    }

    public function store(StoreFormRequest $request)
    {
        $form = $request->user()->forms()->create($request->validated());

        return (new FormResource($form))->response()->setStatusCode(201);
    }

    public function show(Request $request, Form $form)
    {
        $this->authorize('view', $form);

        $form->load(['questions' => fn ($query) => $query->active()])
            ->loadCount('responses');

        return new FormResource($form);
    }

    public function update(UpdateFormRequest $request, Form $form)
    {
        $form->update($request->validated());

        return new FormResource($form);
    }

    public function destroy(Request $request, Form $form)
    {
        $this->authorize('delete', $form);

        $form->delete();

        return response()->noContent();
    }

    public function showPublic(Form $form)
    {
        if (! $form->is_published) {
            throw new NotFoundHttpException;
        }

        $form->load(['questions' => fn ($query) => $query->active()]);

        return new FormResource($form);
    }
}
