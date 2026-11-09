<?php
// ============================================================
//  ROUTER KHUSUS VERCEL
//  Vercel hanya menjalankan PHP dari folder api/, jadi semua halaman
//  (menu.php, checkout.php, admin/..., api/order.php, dll.) diarahkan
//  ke file ini lewat vercel.json, lalu file aslinya di-include di sini.
//  Di XAMPP file ini tidak dipakai.
//  Catatan: nama variabel diawali __rd_ supaya tidak bentrok dengan
//  variabel halaman ($root, $css, $js, $title, ...).
// ============================================================
$__rd_base = dirname(__DIR__);
$__rd_uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$__rd_path = '/' . trim(rawurldecode($__rd_uri), '/');

// Saat uji lokal (php -S localhost:8000 api/index.php): file statis dilayani langsung
if (PHP_SAPI === 'cli-server' && $__rd_path !== '/' && is_file($__rd_base . $__rd_path)
    && !preg_match('#\.php$|^/(config|core|includes|database)/#', $__rd_path)) {
    return false;
}

if ($__rd_path === '/') $__rd_path = '/index.php';
if ($__rd_path === '/admin') {
    // pastikan ada garis miring di akhir supaya link relatif di panel admin benar
    if (!str_ends_with($__rd_uri, '/')) { header('Location: /admin/', true, 308); return; }
    $__rd_path = '/admin/index.php';
}

// Hanya halaman publik yang boleh dibuka (folder config/core/includes tidak bisa diakses)
$__rd_ok = [];
foreach (['/*.php', '/admin/*.php', '/api/*.php'] as $__rd_p) {
    foreach (glob($__rd_base . $__rd_p) as $__rd_f) $__rd_ok[substr($__rd_f, strlen($__rd_base))] = $__rd_f;
}
unset($__rd_ok['/api/index.php']);

if (!isset($__rd_ok[$__rd_path])) {
    http_response_code(404);
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width">'
       . '<body style="font-family:sans-serif;text-align:center;padding:60px 20px">'
       . '<h1>404</h1><p>Halaman tidak ditemukan.</p><p><a href="/menu.php">Kembali ke menu</a></p></body>';
    return;
}

$__rd_file = $__rd_ok[$__rd_path];
$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $__rd_path;
$_SERVER['SCRIPT_FILENAME'] = $__rd_file;
unset($__rd_ok, $__rd_p, $__rd_f, $__rd_uri, $__rd_base);
chdir(dirname($__rd_file));
require $__rd_file;