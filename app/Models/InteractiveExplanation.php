<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InteractiveExplanation extends Model
{
    public $timestamps = false;

    protected $fillable = ['article_id', 'steps_json', 'summary', 'created_at'];

    protected function casts(): array
    {
        return [
            'steps_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
