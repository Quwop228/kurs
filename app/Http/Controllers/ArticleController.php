<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\InteractiveExplanation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Article::published()
            ->with(['category', 'tags', 'user', 'ratings']);

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->input('tag'));
            });
        }

        $sort = $request->input('sort', 'latest');
        $query = match ($sort) {
            'popular' => $query->orderByDesc('views_count'),
            'rated' => $query->withAvg('ratings', 'value')->orderByDesc('ratings_avg_value'),
            default => $query->latest(),
        };

        $articles = $query->paginate(12)->withQueryString();

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
            'filters' => $request->only(['category', 'tag', 'sort']),
        ]);
    }

    public function show(Article $article): Response
    {
        $article->increment('views_count');

        $article->load([
            'comments.user',
            'tags',
            'category',
            'user',
            'ratings',
            'dailyUpdates' => fn($q) => $q->latest()->take(1),
        ]);

        $isFavorited = auth()->user()?->favorites()->where('article_id', $article->id)->exists() ?? false;

        $explanation = InteractiveExplanation::where('article_id', $article->id)->first();

        return Inertia::render('Articles/Show', [
            'article' => $article,
            'isFavorited' => $isFavorited,
            'interactiveExplanation' => $explanation ? [
                'summary' => $explanation->summary,
                'steps' => $explanation->steps_json,
                'created_at' => $explanation->created_at,
            ] : null,
        ]);
    }
}
