<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormTranslation;

class FormTranslator
{
    public function __construct(private TranslationService $translationService)
    {
    }

    /**
     * @return array{title: ?string, description: ?string, questions: array<int, array{title: string, options: ?array}>}
     */
    public function translate(Form $form, string $locale): array
    {
        $sourceHash = $this->hash($form);

        $cached = FormTranslation::query()
            ->where('form_id', $form->id)
            ->where('locale', $locale)
            ->first();

        if ($cached && $cached->source_hash === $sourceHash) {
            return $cached->payload;
        }

        $payload = $this->buildTranslation($form, $locale);

        FormTranslation::updateOrCreate(
            ['form_id' => $form->id, 'locale' => $locale],
            ['source_hash' => $sourceHash, 'payload' => $payload]
        );

        return $payload;
    }

    private function hash(Form $form): string
    {
        $parts = [$form->title, $form->description];

        foreach ($form->questions as $question) {
            $parts[] = $question->id.':'.$question->title.':'.json_encode($question->options);
        }

        return hash('sha256', implode('|', $parts));
    }

    private function buildTranslation(Form $form, string $locale): array
    {
        $unique = [];

        $collect = function (?string $text) use (&$unique) {
            if ($text !== null && $text !== '' && ! in_array($text, $unique, true)) {
                $unique[] = $text;
            }
        };

        $collect($form->title);
        $collect($form->description);

        foreach ($form->questions as $question) {
            $collect($question->title);

            foreach ($question->options ?? [] as $option) {
                $collect($option);
            }
        }

        $translated = $this->translationService->translateStrings($unique, $locale);
        $map = array_combine($unique, $translated);

        $lookup = fn (?string $text) => ($text === null || $text === '') ? $text : ($map[$text] ?? $text);

        return [
            'title' => $lookup($form->title),
            'description' => $lookup($form->description),
            'questions' => $form->questions->mapWithKeys(fn ($question) => [
                $question->id => [
                    'title' => $lookup($question->title),
                    'options' => $question->options ? array_map($lookup, $question->options) : null,
                ],
            ])->all(),
        ];
    }
}
