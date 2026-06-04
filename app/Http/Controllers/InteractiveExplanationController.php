<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\InteractiveExplanation;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;

class InteractiveExplanationController extends Controller
{
    public function generate(Article $article, AIService $aiService): JsonResponse
    {
        @set_time_limit(0);

        $existing = InteractiveExplanation::where('article_id', $article->id)->first();

        if ($existing) {
            return response()->json([
                'explanation' => [
                    'summary' => $existing->summary,
                    'steps' => $existing->steps_json,
                    'created_at' => $existing->created_at,
                ],
            ]);
        }

        try {
            $result = $aiService->generateInteractiveExplanation($article);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Не удалось сгенерировать объяснение через AI: ' . $e->getMessage()
                    . ' Проверьте настройки OpenRouter (ключ и модель).',
            ], 502);
        }

        $explanation = InteractiveExplanation::create([
            'article_id' => $article->id,
            'steps_json' => $result['steps'],
            'summary' => $result['summary'],
            'created_at' => now(),
        ]);

        return response()->json([
            'explanation' => [
                'summary' => $explanation->summary,
                'steps' => $explanation->steps_json,
                'created_at' => $explanation->created_at,
            ],
        ]);
    }

    public function regenerate(Article $article, AIService $aiService): JsonResponse
    {
        @set_time_limit(0);

        try {
            $result = $aiService->generateInteractiveExplanation($article);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Не удалось сгенерировать объяснение через AI: ' . $e->getMessage()
                    . ' Проверьте настройки OpenRouter (ключ и модель).',
            ], 502);
        }

        InteractiveExplanation::where('article_id', $article->id)->delete();

        $explanation = InteractiveExplanation::create([
            'article_id' => $article->id,
            'steps_json' => $result['steps'],
            'summary' => $result['summary'],
            'created_at' => now(),
        ]);

        return response()->json([
            'explanation' => [
                'summary' => $explanation->summary,
                'steps' => $explanation->steps_json,
                'created_at' => $explanation->created_at,
            ],
        ]);
    }
}
