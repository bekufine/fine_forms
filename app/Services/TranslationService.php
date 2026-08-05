<?php

namespace App\Services;

use Anthropic\Client;
use RuntimeException;

class TranslationService
{
    private const MODEL = 'claude-haiku-4-5';

    private const LANGUAGE_NAMES = [
        'en' => 'English',
        'zh' => 'Simplified Chinese',
        'my' => 'Burmese',
        'vi' => 'Vietnamese',
        'si' => 'Sinhala',
        'bn' => 'Bengali',
        'id' => 'Indonesian',
        'ne' => 'Nepali',
    ];

    private Client $client;

    public function __construct()
    {
        $this->client = new Client(apiKey: config('services.anthropic.api_key'));
    }

    /**
     * @param  string[]  $texts  Unique source strings, in Japanese.
     * @return string[] Translations in the same order, one per input string.
     */
    public function translateStrings(array $texts, string $locale): array
    {
        $languageName = self::LANGUAGE_NAMES[$locale] ?? null;

        if ($languageName === null) {
            throw new RuntimeException("Unsupported locale for translation: {$locale}");
        }

        if ($texts === []) {
            return [];
        }

        $message = $this->client->messages->create(
            model: self::MODEL,
            maxTokens: 4096,
            messages: [[
                'role' => 'user',
                'content' => "Translate each Japanese string in this list into {$languageName}. ".
                    'Keep the tone natural for a short customer-facing survey (questions, answer choices, titles). '.
                    "Preserve any leading numbering (e.g. \"1．\") in translated form appropriate to {$languageName}. ".
                    "Return exactly one translation per input string, in the same order.\n\n".
                    json_encode($texts, JSON_UNESCAPED_UNICODE),
            ]],
            outputConfig: [
                'format' => [
                    'type' => 'json_schema',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'translations' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                        ],
                        'required' => ['translations'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        );

        $text = null;
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text = $block->text;
                break;
            }
        }

        if ($text === null) {
            throw new RuntimeException('Translation request returned no text content.');
        }

        $data = json_decode($text, true);
        $translations = $data['translations'] ?? null;

        if (! is_array($translations) || count($translations) !== count($texts)) {
            throw new RuntimeException('Translation response did not match the expected shape.');
        }

        return $translations;
    }
}
