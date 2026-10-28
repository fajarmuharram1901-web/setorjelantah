<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
    | Origins yang boleh memanggil API dari peramban.
    |
    | Aplikasi seluler (Android dan iOS) tidak melewati CORS sama sekali,
    | jadi daftar ini hanya relevan untuk versi web Flutter. Nilai bawaan `*`
    | enak dipakai saat pengembangan, tetapi untuk server produksi isi daftar
    | domain sendiri di .env, misalnya:
    |
    |   CORS_ALLOWED_ORIGINS=https://jelantah.example.com
    */
    'allowed_origins' => explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
