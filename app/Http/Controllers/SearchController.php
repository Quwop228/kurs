<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $query = $request->input('q', '');
        $articles = collect();

        if ($query) {
            $articles = Article::published()
                ->with(['category', 'tags', 'user', 'ratings'])
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('content', 'LIKE', "%{$query}%");
                })
                ->latest()
                ->paginate(12)
                ->withQueryString();
        }

        return Inertia::render('Search/Index', [
            'articles' => $articles,
            'query' => $query,
        ]);
    }
}
