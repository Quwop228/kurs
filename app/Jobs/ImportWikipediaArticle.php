<?php

namespace App\Jobs;

use App\Services\AIService;
use App\Services\WikipediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImportWikipediaArticle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Сколько хранить результат в кэше (сек). */
    public const TTL = 1800;

    /** Запас по времени на медленный перевод через AI. */
    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(
        public string $importId,
        public string $lang,
        public string $title,
    ) {
    }

    public static function cacheKey(string $importId): string
    {
        return "wiki_import:{$importId}";
    }

    public function handle(WikipediaService $wikipedia, AIService $ai): void
    {
        $key = self::cacheKey($this->importId);

        try {
            $article = $wikipedia->fetch($this->lang, $this->title);

            if (!$article) {
                Cache::put($key, [
                    'status' => 'failed',
                    'error' => 'Не удалось загрузить статью из Википедии. Проверьте ссылку.',
                ], self::TTL);
                return;
            }

            $needsTranslation = $this->lang !== 'ru';

            if ($needsTranslation) {
                $translated = $ai->translateToRussian($article['title'], $article['content']);
                $article['title'] = $translated['title'];
                $article['content'] = $translated['content'];
                $article['excerpt'] = mb_substr(strip_tags($translated['content']), 0, 300);
            }

            Cache::put($key, [
                'status' => 'done',
                'result' => [
                    'title' => $article['title'],
                    'content' => $article['content'],
                    'excerpt' => $article['excerpt'],
                    'translated' => $needsTranslation,
                    'source_lang' => $this->lang,
                ],
            ], self::TTL);
        } catch (\Throwable $e) {
            Log::error('ImportWikipediaArticle failed: ' . $e->getMessage(), [
                'import_id' => $this->importId,
                'lang' => $this->lang,
                'title' => $this->title,
            ]);

            Cache::put($key, [
                'status' => 'failed',
                'error' => 'Ошибка при импорте/переводе: ' . $e->getMessage()
                    . ' Проверьте настройки OpenRouter (ключ и модель).',
            ], self::TTL);
        }
    }

    /** Если джоб упал на уровне очереди (таймаут и т.п.). */
    public function failed(\Throwable $e): void
    {
        Cache::put(self::cacheKey($this->importId), [
            'status' => 'failed',
            'error' => 'Импорт не завершился: ' . $e->getMessage(),
        ], self::TTL);
    }
}
