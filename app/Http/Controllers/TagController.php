<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function show(Tag $tag): Response
    {
        $articles = $tag->articles()
            ->published()
            ->with(['category', 'tags', 'user', 'ratings'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Tags/Show', [
            'tag' => $tag,
            'articles' => $articles,
        ]);
    }
}
