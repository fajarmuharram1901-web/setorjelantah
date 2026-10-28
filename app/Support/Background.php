<?php

namespace App\Support;

class Background
{
    /**
     * Jalankan perintah artisan di latar belakang (Windows: start /B).
     */
    public static function artisan(array $arguments): void
    {
        $php = PHP_BINARY ?: 'php';
        $artisan = base_path('artisan');
        $cmd = $php . ' ' . escapeshellarg($artisan) . ' ' . implode(' ', array_map('escapeshellarg', $arguments));

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $full = 'start /B "" ' . $cmd . ' > NUL 2>&1';
        } else {
            $full = $cmd . ' > /dev/null 2>&1 &';
        }

        $process = proc_open(
            $full,
            [
                0 => ['pipe', 'r'],
                1 => ['file', 'NUL', 'w'],
                2 => ['file', 'NUL', 'w'],
            ],
            $pipes
        );

        if (is_resource($process)) {
            proc_close($process);
        }
    }
}