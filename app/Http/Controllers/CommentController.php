<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|max:2000',
        ]);

        $article->comments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Комментарий добавлен.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Комментарий удален.');
    }
}
