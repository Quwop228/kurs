<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Rating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'value' => 'required|integer|min:1|max:5',
        ]);

        Rating::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'article_id' => $article->id,
            ],
            [
                'value' => $validated['value'],
            ]
        );

        return back();
    }
}
