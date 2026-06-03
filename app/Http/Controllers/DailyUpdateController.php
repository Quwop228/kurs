<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\DailyUpdate;
use App\Services\AIService;
use Illuminate\Http\RedirectResponse;

class DailyUpdateController extends Controller
{
    public function generate(Article $article, AIService $aiService): RedirectResponse
    {
        @set_time_limit(0);

        $existing = DailyUpdate::where('article_id', $article->id)
            ->whereDate('created_at', today())
            ->first();

        if ($existing) {
            return back()->with('success', 'Обновление за сегодня уже существует.');
        }

        $result = $aiService->generateDailyUpdate($article);

        DailyUpdate::create([
            'article_id' => $article->id,
            'content' => $result['content'],
            'sources_json' => $result['sources'],
        ]);

        return back()->with('success', 'Ежедневное обновление сгенерировано.');
    }
}
