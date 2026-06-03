<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $latestArticles = Article::published()
            ->with(['category', 'tags', 'user', 'ratings'])
            ->latest()
            ->take(6)
            ->get();

        $popularArticles = Article::published()
            ->with(['category', 'tags', 'user', 'ratings'])
            ->orderByDesc('views_count')
            ->take(6)
            ->get();

        $categories = Category::withCount('articles')->get();

        return Inertia::render('Home', [
            'latestArticles' => $latestArticles,
            'popularArticles' => $popularArticles,
            'categories' => $categories,
        ]);
    }
}
