<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Единая точка обращения к OpenRouter.
     * Бросает исключение при ошибке API или пустом ответе — чтобы вызывающий код
     * не сохранял "пустышку" (пустое объяснение / непереведённый текст) как валидный результат.
     *
     * @param array<int, array{role: string, content: string}> $messages
     */
    /**
     * Условие повтора запроса к OpenRouter: повторяем только временные сбои
     * (обрыв связи, 429 rate-limit, 5xx — частая ситуация у :free-моделей при "холодном старте").
     * На 400/401/403 НЕ повторяем — это не временные ошибки.
     */
    private function retryWhen(): \Closure
    {
        return function ($exception) {
            if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
                return true;
            }

            $status = $exception instanceof \Illuminate\Http\Client\RequestException
                ? $exception->response->status()
                : null;

            return $status === 429 || ($status !== null && $status >= 500);
        };
    }

    private function chat(array $messages, int $timeout = 120): string
    {
        $apiKey = config('openrouter.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('OPENROUTER_API_KEY не задан. Укажите ключ в .env / переменных окружения.');
        }

        $lastError = 'OpenRouter вернул пустой ответ.';

        // До 3 попыток — гасит "холодный старт" :free-моделей (первый раз пусто/ошибка, потом ок).
        // Http::retry ловит 429/5xx/обрыв; внешний цикл дополнительно повторяет на пустом 200-ответе.
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $response = Http::retry(2, 1500, $this->retryWhen(), throw: false)
                ->timeout($timeout)->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post(config('openrouter.base_url') . '/chat/completions', [
                    'model' => config('openrouter.model'),
                    'messages' => $messages,
                ]);

            if ($response->failed()) {
                $body = $response->json();
                $apiMessage = $body['error']['message'] ?? $response->body();

                Log::error('OpenRouter API error', [
                    'status' => $response->status(),
                    'model' => config('openrouter.model'),
                    'error' => $apiMessage,
                ]);

                // На 401/400/403 повторять бессмысленно — выходим сразу.
                if (in_array($response->status(), [400, 401, 403], true)) {
                    throw new \RuntimeException("Ошибка OpenRouter ({$response->status()}): {$apiMessage}");
                }

                $lastError = "Ошибка OpenRouter ({$response->status()}): {$apiMessage}";
                continue;
            }

            $content = $response->json('choices.0.message.content');

            if (is_string($content) && trim($content) !== '') {
                return $content;
            }

            Log::warning('OpenRouter вернул пустой ответ, попытка ' . $attempt, [
                'model' => config('openrouter.model'),
            ]);
            $lastError = 'OpenRouter вернул пустой ответ.';
        }

        throw new \RuntimeException($lastError);
    }

    public function generateDailyUpdate(Article $article): array
    {
        try {
            $news = app(GoogleNewsService::class)->search($article->title, 5);

            $prompt = $this->buildDailyUpdatePrompt($article, $news);

            $response = Http::timeout(120)->withHeaders([
                'Authorization' => 'Bearer ' . config('openrouter.api_key'),
                'Content-Type' => 'application/json',
            ])->post(config('openrouter.base_url') . '/chat/completions', [
                'model' => config('openrouter.model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Ты — редактор научно-популярной энциклопедии. Пишешь краткие сводки свежих новостей по теме. '
                            . 'Отвечай СТРОГО в формате JSON без markdown-обёртки.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            $data = $response->json();
            $messageContent = $data['choices'][0]['message']['content'] ?? '';
            $messageContent = preg_replace('/^```(?:json)?\s*/m', '', $messageContent);
            $messageContent = preg_replace('/\s*```$/m', '', $messageContent);
            $messageContent = trim($messageContent);

            $decoded = json_decode($messageContent, true);

            $sources = $this->mapNewsToSources($news);

            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['content'])) {
                return [
                    'content' => $decoded['content'],
                    'sources' => !empty($sources) ? $sources : ($decoded['sources'] ?? []),
                ];
            }

            return [
                'content' => $messageContent !== '' ? $messageContent : 'Не удалось сгенерировать сводку.',
                'sources' => $sources,
            ];
        } catch (\Throwable $e) {
            Log::error('AIService generateDailyUpdate error: ' . $e->getMessage(), [
                'article_id' => $article->id,
            ]);

            return [
                'content' => 'Не удалось сгенерировать обновление.',
                'sources' => [],
            ];
        }
    }

    private function buildDailyUpdatePrompt(Article $article, array $news): string
    {
        $prompt = "Статья энциклопедии: \"{$article->title}\"\n";
        if (!empty($article->excerpt)) {
            $prompt .= "Краткое описание: \"{$article->excerpt}\"\n";
        }

        if (!empty($news)) {
            $prompt .= "\nСвежие новости по теме (из Google News):\n";
            foreach ($news as $i => $item) {
                $n = $i + 1;
                $prompt .= "[{$n}] {$item['title']}";
                if (!empty($item['source'])) {
                    $prompt .= " — {$item['source']}";
                }
                if (!empty($item['published_at'])) {
                    $prompt .= " ({$item['published_at']})";
                }
                if (!empty($item['description'])) {
                    $prompt .= "\n    {$item['description']}";
                }
                $prompt .= "\n";
            }
            $prompt .= "\nНа основе этих новостей напиши короткую (3-5 предложений) сводку на русском языке: что интересного произошло по теме статьи. "
                . "Не выдумывай фактов, опирайся только на заголовки выше. Если новости напрямую не касаются темы — укажи это.\n";
        } else {
            $prompt .= "\nСвежих новостей по этой теме не нашлось. Напиши короткую (3-5 предложений) сводку того, что в целом актуально по этой теме, и укажи, что это общий обзор, а не свежие новости.\n";
        }

        $prompt .= "\nФормат ответа — JSON: {\"content\": \"текст сводки\"}";

        return $prompt;
    }

    private function mapNewsToSources(array $news): array
    {
        return array_map(fn($item) => [
            'title' => $item['title'],
            'url' => $item['link'],
            'source' => $item['source'] ?? '',
            'published_at' => $item['published_at'] ?? null,
        ], $news);
    }

    public function generateInteractiveExplanation(Article $article): array
    {
        try {
            $plainText = strip_tags($article->content);
            $truncated = mb_substr($plainText, 0, 6000);

            $messageContent = $this->chat([
                [
                    'role' => 'system',
                    'content' => 'Ты — дружелюбный преподаватель, который объясняет сложные темы простым языком. '
                        . 'Твоя задача — взять энциклопедическую статью и превратить её в серию коротких, понятных шагов-объяснений. '
                        . 'Каждый шаг раскрывает одну ключевую идею. Используй аналогии, примеры из жизни, простые сравнения. '
                        . 'Пиши так, будто объясняешь другу, который впервые слышит об этой теме. '
                        . 'Отвечай СТРОГО в формате JSON без markdown-обёртки.',
                ],
                [
                    'role' => 'user',
                    'content' => "Разбери следующую статью на 4-8 простых шагов-объяснений.\n\n"
                        . "Название: \"{$article->title}\"\n\n"
                        . "Текст статьи:\n{$truncated}\n\n"
                        . "Ответ в формате JSON:\n"
                        . "{\n"
                        . "  \"summary\": \"Одно предложение: о чём эта статья\",\n"
                        . "  \"steps\": [\n"
                        . "    {\n"
                        . "      \"title\": \"Название шага\",\n"
                        . "      \"explanation\": \"Простое объяснение на 2-4 предложения, как будто рассказываешь другу\",\n"
                        . "      \"analogy\": \"Аналогия или пример из повседневной жизни (1 предложение)\",\n"
                        . "      \"key_points\": [\"ключевой факт 1\", \"ключевой факт 2\"]\n"
                        . "    }\n"
                        . "  ]\n"
                        . "}",
                ],
            ]);

            $messageContent = preg_replace('/^```(?:json)?\s*/m', '', $messageContent);
            $messageContent = preg_replace('/\s*```$/m', '', $messageContent);
            $messageContent = trim($messageContent);

            $decoded = json_decode($messageContent, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['steps'])) {
                return [
                    'summary' => $decoded['summary'] ?? '',
                    'steps' => $decoded['steps'],
                ];
            }

            // Модель вернула текст, но не в ожидаемом JSON — отдаём как один шаг.
            return [
                'summary' => '',
                'steps' => [
                    [
                        'title' => 'Обзор',
                        'explanation' => $messageContent,
                        'analogy' => '',
                        'key_points' => [],
                    ],
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('AIService generateInteractiveExplanation error: ' . $e->getMessage(), [
                'article_id' => $article->id,
            ]);

            throw $e;
        }
    }

    public function translateToRussian(string $title, string $html): array
    {
        $plainText = strip_tags($html);
        $chunks = $this->splitForTranslation($plainText, 8000);
        $translatedChunks = [];

        $systemPrompt = 'Ты — профессиональный переводчик энциклопедических текстов. '
            . 'Переведи текст на русский язык. Сохрани научный стиль, терминологию, структуру абзацев. '
            . 'Оборачивай абзацы в HTML-теги <p>. Заголовки оборачивай в <h2> или <h3>. '
            . 'Списки оборачивай в <ul><li>. Не добавляй ничего от себя — только перевод.';

        $apiKey = config('openrouter.api_key');
        $baseUrl = config('openrouter.base_url');
        $model = config('openrouter.model');

        $retryWhen = $this->retryWhen();

        $responses = Http::pool(function ($pool) use ($chunks, $apiKey, $baseUrl, $model, $systemPrompt, $retryWhen) {
            $requests = [];
            foreach ($chunks as $i => $chunk) {
                $requests[] = $pool->as("chunk_{$i}")
                    ->retry(3, 1500, $retryWhen, throw: false)
                    ->timeout(180)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($baseUrl . '/chat/completions', [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => "Переведи на русский:\n\n" . $chunk],
                        ],
                    ]);
            }
            return $requests;
        });

        foreach ($chunks as $i => $chunk) {
            $response = $responses["chunk_{$i}"] ?? null;

            // Если запрос упал (исключение пула) или вернул ошибку API — не подсовываем
            // молча оригинальный английский текст, а сообщаем о провале перевода.
            if (!$response instanceof \Illuminate\Http\Client\Response || $response->failed()) {
                $detail = $response instanceof \Illuminate\Http\Client\Response
                    ? ($response->json('error.message') ?? "HTTP {$response->status()}")
                    : 'нет ответа от API';

                Log::error('OpenRouter translation chunk failed', [
                    'chunk' => $i,
                    'model' => config('openrouter.model'),
                    'detail' => $detail,
                ]);

                throw new \RuntimeException("Не удалось перевести текст через OpenRouter: {$detail}");
            }

            $translated = $response->json('choices.0.message.content');

            if (!is_string($translated) || trim($translated) === '') {
                throw new \RuntimeException('OpenRouter вернул пустой перевод фрагмента текста.');
            }

            $translated = preg_replace('/^```(?:html)?\s*/m', '', $translated);
            $translated = preg_replace('/\s*```$/m', '', $translated);
            $translatedChunks[] = trim($translated);
        }

        // Перевод заголовка — через общий метод chat() (с обработкой ошибок).
        $translatedTitle = trim(
            $this->chat([
                [
                    'role' => 'user',
                    'content' => "Переведи на русский одно название (без пояснений, только перевод): \"{$title}\"",
                ],
            ], 30),
            " \t\n\r\0\x0B\"«»"
        );

        return [
            'title' => $translatedTitle !== '' ? $translatedTitle : $title,
            'content' => implode("\n", $translatedChunks),
        ];
    }

    private function splitForTranslation(string $text, int $target): array
    {
        if (mb_strlen($text) <= $target) {
            return [$text];
        }

        $paragraphs = preg_split("/(\n\s*\n)/u", $text) ?: [$text];
        $chunks = [];
        $buffer = '';

        foreach ($paragraphs as $para) {
            if (mb_strlen($buffer) + mb_strlen($para) + 2 > $target && $buffer !== '') {
                $chunks[] = $buffer;
                $buffer = $para;
            } else {
                $buffer .= ($buffer === '' ? '' : "\n\n") . $para;
            }

            while (mb_strlen($buffer) > $target * 1.5) {
                $chunks[] = mb_substr($buffer, 0, $target);
                $buffer = mb_substr($buffer, $target);
            }
        }

        if ($buffer !== '') {
            $chunks[] = $buffer;
        }

        return $chunks;
    }
}
