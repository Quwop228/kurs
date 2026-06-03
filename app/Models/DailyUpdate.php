<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyUpdate extends Model
{
    protected $fillable = ['article_id', 'content', 'sources_json'];

    protected function casts(): array
    {
        return [
            'sources_json' => 'array',
        ];
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
