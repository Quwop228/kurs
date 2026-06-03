<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class Article extends Model implements Feedable
{
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt',
        'category_id', 'user_id', 'views_count', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (Article $article) {
            if (empty($article->slug)) {
                $base = Str::slug($article->title);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $article->slug = $slug;
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function dailyUpdates()
    {
        return $this->hasMany(DailyUpdate::class)->latest();
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function averageRating(): float
    {
        return round($this->ratings()->avg('value') ?? 0, 1);
    }

    public function toFeedItem(): FeedItem
    {
        return FeedItem::create()
            ->id($this->id)
            ->title($this->title)
            ->summary($this->excerpt ?? Str::limit(strip_tags($this->content), 200))
            ->updated($this->updated_at)
            ->link("/articles/{$this->slug}")
            ->authorName($this->user?->name ?? 'Редакция');
    }

    public static function getFeedItems()
    {
        return static::published()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }
}
