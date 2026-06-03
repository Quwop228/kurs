<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::withCount('articles')->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function show(Category $category): Response
    {
        $articles = $category->articles()
            ->published()
            ->with(['category', 'tags', 'user', 'ratings'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Categories/Show', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }
}
