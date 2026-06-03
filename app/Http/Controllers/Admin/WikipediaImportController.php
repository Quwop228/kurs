<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AIService;
use App\Services\WikipediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WikipediaImportController extends Controller
{
    public function import(Request $request, WikipediaService $wikipedia, AIService $ai): JsonResponse
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $request->validate([
            'url' => 'required|url',
        ]);

        $parsed = $wikipedia->parseUrl($request->input('url'));

        if (!$parsed) {
            return response()->json(['error' => 'Неверная ссылка. Поддерживаются ссылки вида https://ru.wikipedia.org/wiki/...'], 422);
        }

        $article = $wikipedia->fetch($parsed['lang'], $parsed['title']);

        if (!$article) {
            return response()->json(['error' => 'Не удалось загрузить статью из Википедии. Проверьте ссылку.'], 422);
        }

        $needsTranslation = $parsed['lang'] !== 'ru';

        if ($needsTranslation) {
            $translated = $ai->translateToRussian($article['title'], $article['content']);
            $article['title'] = $translated['title'];
            $article['content'] = $translated['content'];
            $article['excerpt'] = mb_substr(strip_tags($translated['content']), 0, 300);
        }

        return response()->json([
            'title' => $article['title'],
            'content' => $article['content'],
            'excerpt' => $article['excerpt'],
            'translated' => $needsTranslation,
            'source_lang' => $parsed['lang'],
        ]);
    }
}
