<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResponseRequest;
use App\Http\Resources\ResponseResource;
use App\Models\Form;
use App\Models\Response;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResponseController extends Controller
{
    private function ensureBelongsToForm(Form $form, Response $response): void
    {
        if ($response->form_id !== $form->id) {
            throw new NotFoundHttpException;
        }
    }

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

    public function export(Form $form)
    {
        $this->authorize('view', $form);

        $form->load(['questions' => fn ($query) => $query->active()]);
        $responses = $form->responses()->with('answers')->latest('submitted_at')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['送信日時', 'メールアドレス', ...$form->questions->pluck('title')];
        $sheet->fromArray($headers, null, 'A1');

        $rows = $responses->map(function ($response) use ($form) {
            $answers = $form->questions->map(function ($question) use ($response) {
                $value = $response->answers->firstWhere('question_id', $question->id)?->value;

                if ($value === null) {
                    return '';
                }

                return is_array($value) ? implode(', ', $value) : (string) $value;
            });

            return [
                $response->submitted_at?->format('Y-m-d H:i:s'),
                $response->respondent_email,
                ...$answers,
            ];
        });

        $sheet->fromArray($rows->all(), null, 'A2');

        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, "form-{$form->id}-responses.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function destroy(Form $form, Response $response)
    {
        $this->authorize('update', $form);
        $this->ensureBelongsToForm($form, $response);

        $response->delete();

        return response()->noContent();
    }
}
