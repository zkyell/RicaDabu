<?php
// Konfigurasi dibaca dari ENVIRONMENT VARIABLE (SUPABASE_URL dan SUPABASE_KEY).
// File ini AMAN diunggah ke GitHub karena tidak berisi rahasia.
//
// - Di hosting (Vercel, dll): isi di dashboard hosting > Environment Variables.
// - Di komputer sendiri: salin config/.env.example menjadi config/.env lalu isi.
//   (config/.env sudah masuk .gitignore, jadi tidak ikut ter-upload.)

(function () {
    $file = __DIR__ . '/.env';
    if (!is_file($file)) return;
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $v = trim($v, "\"'");
        // Environment variable asli (dari hosting) selalu menang atas file .env.
        if ($k !== '' && getenv($k) === false) {
            putenv("$k=$v");
        }
    }
})();

define('SUPABASE_URL', rtrim((string)(getenv('SUPABASE_URL') ?: ''), '/'));
define('SUPABASE_KEY', (string)(getenv('SUPABASE_KEY') ?: ''));

define('ADMIN_PASSWORD', (string)(getenv('ADMIN_PASSWORD') ?: ''));
define('APP_SECRET', (string)(getenv('APP_SECRET') ?: hash('sha256', 'rd|' . ADMIN_PASSWORD . '|' . SUPABASE_KEY)));

define('MIDTRANS_SERVER_KEY', (string)(getenv('MIDTRANS_SERVER_KEY') ?: ''));
define('MIDTRANS_CLIENT_KEY', (string)(getenv('MIDTRANS_CLIENT_KEY') ?: ''));
define('MIDTRANS_PRODUCTION', filter_var(getenv('MIDTRANS_PRODUCTION') ?: 'false', FILTER_VALIDATE_BOOLEAN));
