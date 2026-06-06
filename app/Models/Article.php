<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'image', 'category', 'is_featured', 'user_id', 'team_id', 'league_id', 'published_at', 'meta_title', 'meta_description', 'noindex', 'tags'];

    protected $casts = ['published_at' => 'datetime', 'is_featured' => 'boolean', 'noindex' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
    public function team() { return $this->belongsTo(Team::class); }
    public function league() { return $this->belongsTo(League::class); }

    public function scopePublished($q)
    {
        return $q->whereNotNull('published_at')->where('published_at', '<=', now())->orderByDesc('published_at');
    }

    public function scopeFeatured($q)
    {
        return $q->where('is_featured', true);
    }

    public function tagList(): array
    {
        return collect(explode(',', (string) $this->tags))->map(fn ($t) => trim($t))->filter()->values()->all();
    }

    public function readingTime(): int
    {
        $words = str_word_count(strip_tags((string) $this->body));
        return max(1, (int) ceil($words / 200));
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function displayExcerpt(int $length = 160): string
    {
        if (filled($this->excerpt)) {
            return $this->excerpt;
        }
        return \Illuminate\Support\Str::limit(trim(strip_tags(\Illuminate\Support\Str::markdown((string) $this->body))), $length);
    }
}