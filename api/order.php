<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../core/supabase.php';

function fail(int $code, string $msg): never {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405, 'Method tidak diizinkan');

$in    = json_decode(file_get_contents('php://input'), true) ?: [];
$name  = trim((string)($in['name'] ?? ''));
$phone = preg_replace('/[^0-9+]/', '', (string)($in['phone'] ?? ''));
$items = $in['items'] ?? [];

if ($name === '' || strlen($name) > 80 || strlen($phone) < 8 || strlen($phone) > 16
    || !is_array($items) || !$items || count($items) > 50) {
    fail(422, 'Nama, nomor HP, dan pesanan wajib diisi dengan benar.');
}

// Harga selalu diambil dari database, bukan dari browser (supaya tidak bisa dimanipulasi).
$r = sb('GET', 'menu_items?select=id,name,price,category&is_available=eq.true');
if ($r['code'] !== 200) fail(502, 'Gagal membaca menu');
$menu = [];
$addons = [];
foreach ($r['data'] as $m) {
    $menu[$m['id']] = $m;
    if ($m['category'] === 'Pelengkap') $addons[$m['name']] = $m['price'];
}

$rows  = [];
$total = 0;
foreach ($items as $it) {
    $id  = (int)($it['id'] ?? 0);
    $qty = max(1, min(50, (int)($it['qty'] ?? 1)));
    if (!isset($menu[$id])) fail(422, 'Ada menu yang tidak tersedia.');

    $unit = $menu[$id]['price'];
    $adds = [];
    foreach ((array)($it['adds'] ?? []) as $a) {
        if (isset($addons[$a])) { $adds[] = $a; $unit += $addons[$a]; }
    }
    $spice = in_array($it['sp'] ?? '', ['Tidak Pedas', 'Sedang', 'Pedas Mantap'], true) ? $it['sp'] : null;

    $total += $unit * $qty;
    $rows[] = [
        'menu_item_id' => $id,
        'name'         => $menu[$id]['name'],
        'unit_price'   => $unit,
        'qty'          => $qty,
        'spice'        => $spice,
        'addons'       => $adds ? implode(', ', $adds) : null,
        'note'         => mb_substr(trim((string)($it['note'] ?? '')), 0, 120) ?: null,
    ];
}

$o = sb('POST', 'orders', [['customer_name' => $name, 'phone' => $phone, 'total' => $total]],
        ['Prefer: return=representation']);
if ($o['code'] >= 300 || empty($o['data'][0]['id'])) fail(502, 'Gagal menyimpan pesanan');
$orderId = $o['data'][0]['id'];

foreach ($rows as &$row) $row['order_id'] = $orderId;
unset($row);

$oi = sb('POST', 'order_items', $rows);
if ($oi['code'] >= 300) {
    sb('DELETE', 'orders?id=eq.' . $orderId);   // batalkan pesanan yang setengah jadi
    fail(502, 'Gagal menyimpan isi pesanan');
}

echo json_encode(['ok' => true, 'order_id' => $orderId, 'total' => $total]);
