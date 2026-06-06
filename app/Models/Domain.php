<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Domain extends Model
{
    protected $fillable = ['domain', 'cf_zone_id', 'name_servers', 'status', 'ssl_status', 'is_primary', 'redirect_url', 'redirect_type', 'redirect_absolute', 'message'];

    protected $casts = [
        'name_servers'      => 'array',
        'is_primary'        => 'boolean',
        'redirect_absolute' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('domain.primary');
            Cache::forget('domain.redirects');
        });
        static::deleted(function () {
            Cache::forget('domain.primary');
            Cache::forget('domain.redirects');
        });
    }

    /** Base URL of the primary domain, e.g. https://example.com (no trailing slash). Falls back to APP_URL. */
    public static function activeBaseUrl(): string
    {
        $primary = Cache::remember('domain.primary', 3600, fn () => static::where('is_primary', true)->value('domain'));

        return $primary ? 'https://' . $primary : rtrim(config('app.url'), '/');
    }

    /** Full URL on the primary domain for a given path. */
    public static function to(string $path = '/'): string
    {
        return static::activeBaseUrl() . '/' . ltrim($path, '/');
    }
}