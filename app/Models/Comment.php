<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['article_id', 'name', 'body', 'approved', 'ip'];

    protected $casts = ['approved' => 'boolean'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function scopeApproved($q)
    {
        return $q->where('approved', true);
    }
}