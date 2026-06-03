<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function index(): Response
    {
        $tags = Tag::withCount('articles')->get();

        return Inertia::render('Admin/Tags/Index', [
            'tags' => $tags,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
        ]);

        Tag::create($validated);

        return back()->with('success', 'Тег создан.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('success', 'Тег удален.');
    }
}
