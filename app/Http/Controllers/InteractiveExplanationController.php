<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInteractiveExplanation;
use App\Models\Article;
use App\Models\InteractiveExplanation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class InteractiveExplanationController extends Controller
{
    /**
     * Старт генерации. Если объяснение уже есть — отдаём сразу.
     * Иначе ставим задачу в очередь и возвращаем статус processing (без долгого ожидания).
     */
    public function generate(Article $article): JsonResponse
    {
        $existing = InteractiveExplanation::where('article_id', $article->id)->first();

        if ($existing) {
            return response()->json(['explanation' => $this->present($existing)]);
        }

        $this->dispatchGeneration($article->id, force: false);

        return response()->json(['status' => 'processing'], 202);
    }

    /**
     * Пересоздание — только для админов (см. middleware в routes/web.php).
     */
    public function regenerate(Article $article): JsonResponse
    {
        $this->dispatchGeneration($article->id, force: true);

        return response()->json(['status' => 'processing'], 202);
    }

    /**
     * Опрос статуса: done + данные / processing / failed + ошибка.
     */
    public function status(Article $article): JsonResponse
    {
        $state = Cache::get(GenerateInteractiveExplanation::statusKey($article->id));
        $stateStatus = is_array($state) ? ($state['status'] ?? null) : null;

        if ($stateStatus === 'failed') {
            return response()->json([
                'status' => 'failed',
                'error' => $state['error'] ?? 'Не удалось сгенерировать объяснение.',
            ], 502);
        }

        // Пока активен маркер processing — не отдаём старую запись (важно при regenerate,
        // где прежнее объяснение ещё не удалено фоновой задачей).
        if ($stateStatus === 'processing') {
            return response()->json(['status' => 'processing']);
        }

        $existing = InteractiveExplanation::where('article_id', $article->id)->first();

        if ($existing) {
            return response()->json([
                'status' => 'done',
                'explanation' => $this->present($existing),
            ]);
        }

        return response()->json(['status' => 'processing']);
    }

    private function dispatchGeneration(int $articleId, bool $force): void
    {
        Cache::put(
            GenerateInteractiveExplanation::statusKey($articleId),
            ['status' => 'processing'],
            600,
        );

        GenerateInteractiveExplanation::dispatch($articleId, $force);
    }

    private function present(InteractiveExplanation $explanation): array
    {
        return [
            'summary' => $explanation->summary,
            'steps' => $explanation->steps_json,
            'created_at' => $explanation->created_at,
        ];
    }
}
