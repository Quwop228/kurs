<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DailyUpdateController;
use App\Http\Controllers\InteractiveExplanationController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WikipediaImportController;
use Illuminate\Support\Facades\Route;

Route::feeds();

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/tags/{tag:slug}', [TagController::class, 'show'])->name('tags.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/dashboard', function () {
    return Inertia\Inertia::render('Dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/articles/{article:slug}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/articles/{article:slug}/rating', [RatingController::class, 'store'])->name('ratings.store');
    Route::post('/articles/{article:slug}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/articles/{article:slug}/daily-update', [DailyUpdateController::class, 'generate'])->name('daily-update.generate');
    Route::post('/articles/{article:slug}/interactive', [InteractiveExplanationController::class, 'generate'])->name('interactive.generate');
    Route::post('/articles/{article:slug}/interactive/regenerate', [InteractiveExplanationController::class, 'regenerate'])->name('interactive.regenerate');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin,editor'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('articles', AdminArticleController::class);
    Route::post('/wikipedia-import', [WikipediaImportController::class, 'import'])->name('wikipedia.import');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/tags', [AdminTagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [AdminTagController::class, 'store'])->name('tags.store');
    Route::delete('/tags/{tag}', [AdminTagController::class, 'destroy'])->name('tags.destroy');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.updateRole');
});

require __DIR__.'/auth.php';
