<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\InteractiveExplanation;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateInteractiveExplanation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Запас по времени на медленный ответ AI. */
    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(
        public int $articleId,
        public bool $force = false,
    ) {
    }

    public static function statusKey(int $articleId): string
    {
        return "interactive_status:{$articleId}";
    }

    public function handle(AIService $ai): void
    {
        $statusKey = self::statusKey($this->articleId);

        try {
            $article = Article::find($this->articleId);

            if (!$article) {
                Cache::put($statusKey, ['status' => 'failed', 'error' => 'Статья не найдена.'], 600);
                return;
            }

            if ($this->force) {
                InteractiveExplanation::where('article_id', $article->id)->delete();
            } elseif (InteractiveExplanation::where('article_id', $article->id)->exists()) {
                // Уже сгенерировано другим запросом — ничего не делаем.
                Cache::forget($statusKey);
                return;
            }

            $result = $ai->generateInteractiveExplanation($article);

            InteractiveExplanation::create([
                'article_id' => $article->id,
                'steps_json' => $result['steps'],
                'summary' => $result['summary'],
                'created_at' => now(),
            ]);

            // Готово — статус определяется наличием записи в БД, маркер можно убрать.
            Cache::forget($statusKey);
        } catch (\Throwable $e) {
            Log::error('GenerateInteractiveExplanation failed: ' . $e->getMessage(), [
                'article_id' => $this->articleId,
            ]);

            Cache::put($statusKey, [
                'status' => 'failed',
                'error' => 'Не удалось сгенерировать объяснение через AI: ' . $e->getMessage()
                    . ' Проверьте настройки OpenRouter (ключ и модель).',
            ], 600);
        }
    }

    public function failed(\Throwable $e): void
    {
        Cache::put(self::statusKey($this->articleId), [
            'status' => 'failed',
            'error' => 'Генерация не завершилась: ' . $e->getMessage(),
        ], 600);
    }
}
