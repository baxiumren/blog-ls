<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class CloudflareService
{
    protected string $base = 'https://api.cloudflare.com/client/v4';

    protected function http()
    {
        $key = Setting::get('cf_api_key');
        $key = $key ? Crypt::decryptString($key) : null;

        return Http::baseUrl($this->base)
            ->timeout(20)
            ->withHeaders([
                'X-Auth-Email' => Setting::get('cf_email'),
                'X-Auth-Key'   => $key,
                'Content-Type' => 'application/json',
            ])
            ->acceptJson();
    }

    public function configured(): bool
    {
        return filled(Setting::get('cf_email')) && filled(Setting::get('cf_api_key'));
    }

    public function accountId(): ?string
    {
        return $this->http()->get('/accounts', ['per_page' => 1])->json('result.0.id');
    }

    /** Create a zone → ['zone_id', 'name_servers', 'status']. Throws on failure. */
    public function createZone(string $domain): array
    {
        $accountId = $this->accountId();
        if (! $accountId) {
            throw new \RuntimeException('Could not authenticate with Cloudflare — check the account email and Global API key.');
        }

        $res = $this->http()->post('/zones', [
            'name'    => $domain,
            'account' => ['id' => $accountId],
            'type'    => 'full',
        ]);

        if (! $res->json('success')) {
            throw new \RuntimeException($this->errorMessage($res));
        }

        return [
            'zone_id'      => $res->json('result.id'),
            'name_servers' => $res->json('result.name_servers', []),
            'status'       => $res->json('result.status'),
        ];
    }

    public function addDnsRecord(string $zoneId, string $type, string $name, string $content, bool $proxied = true): bool
    {
        return (bool) $this->http()->post("/zones/{$zoneId}/dns_records", [
            'type'    => $type,
            'name'    => $name,
            'content' => $content,
            'proxied' => $proxied,
            'ttl'     => 1,
        ])->json('success');
    }

    public function setSslFull(string $zoneId): void
    {
        // 'flexible' = CF↔origin over HTTP (port 80) — matches our HTTP-only Nginx so ANY
        // pointed domain works instantly without an origin cert (ideal for domain rotation).
        $this->http()->patch("/zones/{$zoneId}/settings/ssl", ['value' => 'flexible']);
        $this->http()->patch("/zones/{$zoneId}/settings/always_use_https", ['value' => 'on']);
    }

    /** SSL certificate status from Cloudflare Universal SSL: 'active' or 'pending'. */
    public function getSslStatus(string $zoneId): string
    {
        $status = $this->http()->get("/zones/{$zoneId}/ssl/verification")->json('result.0.certificate_status');

        return $status === 'active' ? 'active' : 'pending';
    }

    public function getZoneStatus(string $zoneId): ?string
    {
        return $this->http()->get("/zones/{$zoneId}")->json('result.status');
    }

    protected function errorMessage($res): string
    {
        return $res->json('errors.0.message', 'Cloudflare API error');
    }
}