<?php
// Login admin tanpa session (cookie bertanda tangan HMAC) supaya juga jalan di Vercel/serverless.
require_once __DIR__ . '/functions.php';

const ADMIN_COOKIE = 'rd_admin';

function admin_sign(string $data): string { return hash_hmac('sha256', $data, APP_SECRET); }

function admin_ok(): bool {
    if (ADMIN_PASSWORD === '') return false;
    [$exp, $sig] = array_pad(explode('.', (string)($_COOKIE[ADMIN_COOKIE] ?? ''), 2), 2, '');
    return ctype_digit($exp) && (int)$exp > time() && hash_equals(admin_sign('login|' . $exp), $sig);
}

function admin_login(string $pw): bool {
    if (ADMIN_PASSWORD === '' || !hash_equals(ADMIN_PASSWORD, $pw)) { usleep(600000); return false; }
    $exp = time() + 12 * 3600;  // login berlaku 12 jam
    setcookie(ADMIN_COOKIE, $exp . '.' . admin_sign('login|' . $exp), [
        'expires' => $exp, 'path' => '/', 'httponly' => true, 'samesite' => 'Strict',
        'secure' => ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https',
    ]);
    return true;
}

function admin_logout(): void { setcookie(ADMIN_COOKIE, '', ['expires' => 1, 'path' => '/']); }

function csrf(): string { return substr(admin_sign('csrf|' . ($_COOKIE[ADMIN_COOKIE] ?? '')), 0, 32); }
function csrf_ok(): bool { return hash_equals(csrf(), (string)($_POST['csrf'] ?? '')); }

function require_admin(): void {
    if (!admin_ok()) { header('Location: index.php'); exit; }
}
