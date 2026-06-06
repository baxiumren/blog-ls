<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    protected $fillable = ['fixture_id', 'tip', 'predicted_score', 'confidence', 'body', 'user_id', 'published_at'];

    protected $casts = ['published_at' => 'datetime', 'confidence' => 'integer'];

    public function fixture() { return $this->belongsTo(Fixture::class); }
    public function user() { return $this->belongsTo(User::class); }

    public function scopePublished($q)
    {
        return $q->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}