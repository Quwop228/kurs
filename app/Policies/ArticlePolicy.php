<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Article $article): bool
    {
        if ($article->is_published) {
            return true;
        }

        return $user && in_array($user->role, ['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'editor']);
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'editor' && $user->id === $article->user_id;
    }

    public function delete(User $user, Article $article): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'editor' && $user->id === $article->user_id;
    }
}
