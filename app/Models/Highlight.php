<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Highlight extends Model
{
    protected $fillable = ['fixture_id', 'youtube_url', 'title', 'user_id'];

    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    // Ambil ID video dari berbagai bentuk URL YouTube
    public function youtubeId(): ?string
    {
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{11})~', (string) $this->youtube_url, $m)) {
            return $m[1];
        }
        return null;
    }
}