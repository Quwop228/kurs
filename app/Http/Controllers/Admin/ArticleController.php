<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(): Response
    {
        $articles = Article::with(['category', 'user'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Articles/Index', [
            'articles' => $articles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Articles/Create', [
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'excerpt' => 'nullable',
            'tags' => 'array',
            'is_published' => 'boolean',
        ]);

        $article = Article::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category_id' => $validated['category_id'],
            'excerpt' => $validated['excerpt'] ?? null,
            'is_published' => $validated['is_published'] ?? false,
            'user_id' => auth()->id(),
        ]);

        if (!empty($validated['tags'])) {
            $article->tags()->sync($validated['tags']);
        }

        return redirect()->route('admin.articles.index')
            ->with('success', 'Статья создана.');
    }

    public function edit(Article $article): Response
    {
        $article->load('tags');

        return Inertia::render('Admin/Articles/Edit', [
            'article' => $article,
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'excerpt' => 'nullable',
            'tags' => 'array',
            'is_published' => 'boolean',
        ]);

        $article->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category_id' => $validated['category_id'],
            'excerpt' => $validated['excerpt'] ?? null,
            'is_published' => $validated['is_published'] ?? false,
        ]);

        $article->tags()->sync($validated['tags'] ?? []);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Статья обновлена.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return back()->with('success', 'Статья удалена.');
    }
}
