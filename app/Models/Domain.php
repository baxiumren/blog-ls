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

    /** Canonical base URL for SEO. Priority: manual SEO setting → primary domain → APP_URL. No trailing slash. */
    public static function activeBaseUrl(): string
    {
        // Manual SEO override (Settings → SEO → Canonical domain), e.g. https://www.maha-bola.com
        $manual = Setting::get('canonical_domain');
        if (filled($manual)) {
            $manual = rtrim(trim($manual), '/');

            return preg_match('#^https?://#', $manual) ? $manual : 'https://' . ltrim($manual, '/');
        }

        $primary = Cache::remember('domain.primary', 3600, fn () => static::where('is_primary', true)->value('domain'));

        return $primary ? 'https://' . $primary : rtrim(config('app.url'), '/');
    }

    /** Full URL on the primary domain for a given path. */
    public static function to(string $path = '/'): string
    {
        return static::activeBaseUrl() . '/' . ltrim($path, '/');
    }
}