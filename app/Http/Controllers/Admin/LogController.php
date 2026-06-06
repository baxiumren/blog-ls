<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class LogController extends Controller
{
    protected function path(): string
    {
        return storage_path('logs/laravel.log');
    }

    public function index()
    {
        $path = $this->path();
        $entries = [];
        $size = 0;

        if (file_exists($path)) {
            $size = filesize($path);
            // read last 512KB only (performance on big logs)
            $fp = fopen($path, 'r');
            $read = min($size, 512 * 1024);
            if ($read > 0) {
                fseek($fp, -$read, SEEK_END);
                $content = fread($fp, $read);
            } else {
                $content = '';
            }
            fclose($fp);

            preg_match_all('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.*?)(?=\n\[\d{4}-\d{2}-\d{2}|\z)/s', $content, $m, PREG_SET_ORDER);
            foreach (array_slice(array_reverse($m), 0, 100) as $e) {
                $entries[] = [
                    'time'    => $e[1],
                    'level'   => strtoupper($e[2]),
                    'message' => mb_substr(trim($e[3]), 0, 3000),
                ];
            }
        }

        return view('admin.logs', compact('entries', 'size'));
    }

    public function clear()
    {
        if (file_exists($this->path())) {
            file_put_contents($this->path(), '');
        }
        return back()->with('ok', 'Logs cleared.');
    }
}