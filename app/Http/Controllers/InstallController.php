<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class InstallController extends Controller
{
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }
        return view('install.index', $this->pageData());
    }

    public function run(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $validator = Validator::make($request->all(), [
            'site_name'        => ['required', 'string', 'max:255'],
            'site_url'         => ['required', 'url'],
            'timezone'         => ['required', 'string'],
            'admin_name'       => ['required', 'string', 'max:255'],
            'admin_email'      => ['required', 'email'],
            'admin_password'   => ['required', 'string', 'min:6', 'confirmed'],
            'api_football_key' => ['required', 'string'],
            'cf_email'         => ['nullable', 'email'],
            'cf_api_key'       => ['nullable', 'string'],
            'vps_ip'           => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return view('install.index', $this->pageData($validator->errors(), $request->all()));
        }
        $data = $validator->validated();

        try {
            // Ensure APP_KEY exists (fresh server may have none)
            if (! config('app.key')) {
                $key = 'base64:' . base64_encode(random_bytes(32));
                $this->setEnv(['APP_KEY' => $key]);
                config(['app.key' => $key]);
                app()->forgetInstance('encrypter');
            }

            // 1. .env basics
            $this->setEnv([
                'APP_NAME'         => '"' . str_replace('"', '', $data['site_name']) . '"',
                'APP_ENV'          => 'production',
                'APP_DEBUG'        => 'false',
                'APP_URL'          => rtrim($data['site_url'], '/'),
                'API_FOOTBALL_KEY' => $data['api_football_key'],
            ]);

            // optional SMTP
            if (filled($request->mail_host)) {
                $this->setEnv([
                    'MAIL_MAILER'       => 'smtp',
                    'MAIL_HOST'         => $request->mail_host,
                    'MAIL_PORT'         => $request->mail_port ?: '587',
                    'MAIL_USERNAME'     => $request->mail_username ?? '',
                    'MAIL_PASSWORD'     => '"' . ($request->mail_password ?? '') . '"',
                    'MAIL_FROM_ADDRESS' => '"' . ($request->mail_from ?? '') . '"',
                ]);
            }

            // 2. database — pakai clean starter DB kalau ada, else kosong
            $sqlite = base_path('database/database.sqlite');
            if (! file_exists($sqlite)) {
                $starter = base_path('database/starter.sqlite');
                file_exists($starter) ? copy($starter, $sqlite) : touch($sqlite);
            }
            Artisan::call('migrate', ['--force' => true]);
            // seed cuma kalau DB kosong (starter DB udah ada reference data)
            if (\App\Models\League::count() === 0) {
                Artisan::call('db:seed', ['--force' => true]);
                Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PageSeeder', '--force' => true]);
            }

            // 3. DB settings
            Setting::put('site_name', $data['site_name']);
            Setting::put('timezone', $data['timezone']);
            if (filled($data['cf_email'] ?? null))   Setting::put('cf_email', $data['cf_email']);
            if (filled($data['cf_api_key'] ?? null)) Setting::put('cf_api_key', Crypt::encryptString($data['cf_api_key']));
            if (filled($data['vps_ip'] ?? null))     Setting::put('vps_ip', $data['vps_ip']);

            // 4. admin master
            User::create([
                'name'     => $data['admin_name'],
                'email'    => $data['admin_email'],
                'password' => $data['admin_password'],
                'role'     => 'admin',
                'is_admin' => true,
            ]);

            // 5. storage symlink
            Artisan::call('storage:link');

            // 6. lock
            file_put_contents($this->lockPath(), 'Installed at ' . date('Y-m-d H:i:s'));

            return view('install.success', ['url' => rtrim($data['site_url'], '/')]);
        } catch (\Throwable $e) {
            return view('install.index', $this->pageData(
                new MessageBag(['install' => 'Install failed: ' . $e->getMessage()]),
                $request->all()
            ));
        }
    }

    protected function pageData($errors = null, array $old = []): array
    {
        $req = [
            'PHP >= 8.2'          => version_compare(PHP_VERSION, '8.2', '>='),
            'PDO SQLite'          => extension_loaded('pdo_sqlite'),
            'OpenSSL'             => extension_loaded('openssl'),
            'Mbstring'            => extension_loaded('mbstring'),
            'cURL'                => extension_loaded('curl'),
            'storage/ writable'   => is_writable(storage_path()),
            'database/ writable'  => is_writable(base_path('database')),
        ];
        return [
            'requirements' => $req,
            'ready'        => ! in_array(false, $req, true),
            'err'          => $errors ?: new MessageBag(),
            'old'          => $old,
        ];
    }

    protected function isInstalled(): bool
    {
        return file_exists($this->lockPath());
    }

    protected function lockPath(): string
    {
        return storage_path('installed');
    }

    protected function setEnv(array $values): void
    {
        $path = base_path('.env');
        $env = file_exists($path) ? file_get_contents($path) : '';
        foreach ($values as $key => $value) {
            $line = $key . '=' . $value;
            if (preg_match("/^{$key}=.*$/m", $env)) {
                $env = preg_replace("/^{$key}=.*$/m", $line, $env);
            } else {
                $env = rtrim($env) . "\n" . $line . "\n";
            }
        }
        file_put_contents($path, $env);
    }
}