<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportWikipediaArticle;
use App\Services\WikipediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WikipediaImportController extends Controller
{
    /**
     * Старт импорта. Ничего долгого здесь НЕ делаем — только валидируем ссылку
     * и ставим задачу в очередь, чтобы не упереться в таймаут прокси/браузера.
     */
    public function import(Request $request, WikipediaService $wikipedia): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $parsed = $wikipedia->parseUrl($request->input('url'));

        if (!$parsed) {
            return response()->json([
                'error' => 'Неверная ссылка. Поддерживаются ссылки вида https://ru.wikipedia.org/wiki/...',
            ], 422);
        }

        $importId = (string) Str::uuid();

        Cache::put(
            ImportWikipediaArticle::cacheKey($importId),
            ['status' => 'processing'],
            ImportWikipediaArticle::TTL,
        );

        ImportWikipediaArticle::dispatch($importId, $parsed['lang'], $parsed['title']);

        return response()->json([
            'import_id' => $importId,
            'status' => 'processing',
            'needs_translation' => $parsed['lang'] !== 'ru',
        ], 202);
    }

    /**
     * Опрос статуса импорта. Фронтенд дёргает этот эндпоинт, пока не получит done/failed.
     */
    public function status(string $importId): JsonResponse
    {
        $data = Cache::get(ImportWikipediaArticle::cacheKey($importId));

        if (!$data) {
            return response()->json([
                'status' => 'failed',
                'error' => 'Задача импорта не найдена или истекла. Попробуйте снова.',
            ], 404);
        }

        return response()->json($data);
    }
}
