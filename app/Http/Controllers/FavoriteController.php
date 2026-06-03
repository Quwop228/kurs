<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FavoriteController extends Controller
{
    public function toggle(Article $article): RedirectResponse
    {
        $user = auth()->user();

        if ($user->favorites->contains($article->id)) {
            $user->favorites()->detach($article->id);
        } else {
            $user->favorites()->attach($article->id);
        }

        return back();
    }

    public function index(): Response
    {
        $articles = auth()->user()
            ->favorites()
            ->with(['category', 'tags', 'user', 'ratings'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Favorites/Index', [
            'articles' => $articles,
        ]);
    }
}
