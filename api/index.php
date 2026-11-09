<?php
// ============================================================
//  ROUTER KHUSUS VERCEL
//  Vercel hanya menjalankan PHP dari folder api/, jadi semua halaman
//  (menu.php, checkout.php, admin/..., api/order.php, dll.) diarahkan
//  ke file ini lewat vercel.json, lalu file aslinya di-include di sini.
//  Di XAMPP file ini tidak dipakai.
// ============================================================
$root = dirname(__DIR__);
$path = '/' . trim(rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'), '/');

// Saat uji lokal (php -S localhost:8000 api/index.php): file statis dilayani langsung
if (PHP_SAPI === 'cli-server' && $path !== '/' && is_file($root . $path)
    && !preg_match('#\.php$|^/(config|core|includes|database)/#', $path)) {
    return false;
}

if ($path === '/')      $path = '/index.php';
if ($path === '/admin') {
    // pastikan ada garis miring di akhir supaya link relatif di panel admin benar
    if (!str_ends_with(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')) { header('Location: /admin/', true, 308); return; }
    $path = '/admin/index.php';
}

// Hanya halaman publik yang boleh dibuka (folder config/core/includes tidak bisa diakses)
$allowed = [];
foreach (['/*.php', '/admin/*.php', '/api/*.php'] as $pattern) {
    foreach (glob($root . $pattern) as $f) $allowed[substr($f, strlen($root))] = $f;
}
unset($allowed['/api/index.php']);

if (!isset($allowed[$path])) {
    http_response_code(404);
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width">'
       . '<body style="font-family:sans-serif;text-align:center;padding:60px 20px">'
       . '<h1>404</h1><p>Halaman tidak ditemukan.</p><p><a href="/menu.php">Kembali ke menu</a></p></body>';
    return;
}

$file = $allowed[$path];
$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $path;
$_SERVER['SCRIPT_FILENAME'] = $file;
chdir(dirname($file));
require $file;
