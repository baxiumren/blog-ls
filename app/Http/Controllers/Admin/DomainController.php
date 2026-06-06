<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Setting;
use App\Services\CloudflareService;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index(CloudflareService $cf)
    {
        return view('admin.domains.index', [
            'domains'    => Domain::orderByDesc('is_primary')->orderBy('domain')->get(),
            'configured' => $cf->configured(),
            'vpsIp'      => Setting::get('vps_ip'),
        ]);
    }

    public function store(Request $request, CloudflareService $cf)
    {
        $data = $request->validate([
            'domain' => ['required', 'string', 'regex:/^[a-z0-9.-]+\.[a-z]{2,}$/i', 'unique:domains,domain'],
        ]);
        $domain = strtolower(trim($data['domain']));

        if (! $cf->configured()) {
            return back()->with('error', 'Set your Cloudflare email & API key in Settings → Cloudflare first.');
        }
        if (! ($vpsIp = Setting::get('vps_ip'))) {
            return back()->with('error', 'Set your Server IP in Settings → Cloudflare first.');
        }

        try {
            $zone = $cf->createZone($domain);
            $proxied = (Setting::get('cf_proxied') ?? '1') === '1';
            $dns1 = $cf->addDnsRecord($zone['zone_id'], 'A', $domain, $vpsIp, $proxied);
            $dns2 = $cf->addDnsRecord($zone['zone_id'], 'A', 'www', $vpsIp, $proxied);
            $cf->setSslFull($zone['zone_id']);
            $ssl = $cf->getSslStatus($zone['zone_id']);

            $message = (! $dns1 || ! $dns2)
                ? "Zone created, but an A record failed — add it manually in Cloudflare (A → {$vpsIp})."
                : 'Created. Point your registrar nameservers to the ones below.';

            Domain::create([
                'domain'       => $domain,
                'cf_zone_id'   => $zone['zone_id'],
                'name_servers' => $zone['name_servers'],
                'status'       => $zone['status'],
                'ssl_status'   => $ssl,
                'is_primary'   => Domain::count() === 0,
                'message'      => $message,
            ]);

            return back()->with('ok', "Domain {$domain} added. Set its nameservers at your registrar.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Cloudflare: ' . $e->getMessage());
        }
    }

    public function refresh(Domain $domain, CloudflareService $cf)
    {
        if ($domain->cf_zone_id) {
            $domain->update([
                'status'     => $cf->getZoneStatus($domain->cf_zone_id) ?? $domain->status,
                'ssl_status' => $cf->getSslStatus($domain->cf_zone_id),
            ]);
        }
        return back()->with('ok', 'Status refreshed.');
    }

    public function primary(Domain $domain)
    {
        Domain::where('is_primary', true)->update(['is_primary' => false]);
        $domain->update(['is_primary' => true]);
        return back()->with('ok', "{$domain->domain} is now the primary domain.");
    }

    public function redirect(Request $request, Domain $domain)
    {
        if ($request->boolean('clear')) {
            $domain->update(['redirect_url' => null, 'redirect_type' => null, 'redirect_absolute' => false]);
            return back()->with('ok', "Redirect removed from {$domain->domain}.");
        }

        $data = $request->validate([
            'redirect_url'  => ['required', 'url'],
            'redirect_type' => ['required', 'in:301,302,307'],
        ]);

        $domain->update([
            'redirect_url'      => $data['redirect_url'],
            'redirect_type'     => $data['redirect_type'],
            'redirect_absolute' => $request->boolean('redirect_absolute'),
        ]);

        return back()->with('ok', "{$domain->domain} now redirects to {$data['redirect_url']}.");
    }

    public function destroy(Domain $domain)
    {
        $domain->delete();
        return back()->with('ok', 'Domain removed from the list (Cloudflare zone was NOT deleted).');
    }
}