<?php
// Membuat pesanan baru. Dipanggil dari checkout.php (assets/js/checkout.js)
require __DIR__ . '/../includes/functions.php';

function fail(int $code, string $msg): never { json_out(['error' => $msg], $code); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405, 'Method tidak diizinkan');

$in      = json_decode(file_get_contents('php://input'), true) ?: [];
$name    = trim((string)($in['name'] ?? ''));
$phone   = preg_replace('/[^0-9+]/', '', (string)($in['phone'] ?? ''));
$items   = $in['items'] ?? [];
$type    = (string)($in['order_type'] ?? '');
$tableNo = mb_substr(trim((string)($in['table_no'] ?? '')), 0, 10);
$address = mb_substr(trim((string)($in['address'] ?? '')), 0, 250);
$note    = mb_substr(trim((string)($in['note'] ?? '')), 0, 200);
$method  = (string)($in['payment_method'] ?? '');

if ($name === '' || mb_strlen($name) > 80 || strlen($phone) < 8 || strlen($phone) > 16)
    fail(422, 'Isi nama dan nomor HP/WhatsApp dengan benar.');
if (!is_array($items) || !$items || count($items) > 50)
    fail(422, 'Keranjang kosong. Pilih menu dulu.');
if (!isset(ORDER_TYPES[$type]))
    fail(422, 'Pilih cara pesan: makan di tempat, ambil sendiri, atau diantar.');
if ($type === 'dine_in' && $tableNo === '')
    fail(422, 'Isi nomor meja kamu.');
if ($type === 'delivery' && mb_strlen($address) < 10)
    fail(422, 'Isi alamat pengantaran lengkap (minimal 10 huruf).');
if (!isset(payment_methods()[$method]))
    fail(422, 'Pilih metode pembayaran.');

// Harga selalu diambil dari database, bukan dari browser (supaya tidak bisa dimanipulasi).
$r = sb('GET', 'menu_items?select=id,name,price,category&is_available=eq.true');
if ($r['code'] !== 200) fail(502, 'Gagal membaca menu');
$menu = [];
$addons = [];
foreach ($r['data'] as $m) {
    $menu[$m['id']] = $m;
    if ($m['category'] === 'Pelengkap') $addons[$m['name']] = $m['price'];
}

$rows     = [];
$subtotal = 0;
foreach ($items as $it) {
    $id  = (int)($it['id'] ?? 0);
    $qty = max(1, min(50, (int)($it['qty'] ?? 1)));
    if (!isset($menu[$id])) fail(422, 'Ada menu di keranjang yang sedang habis. Hapus menu itu lalu coba lagi.');

    $unit = $menu[$id]['price'];
    $adds = [];
    foreach ((array)($it['adds'] ?? []) as $a) {
        if (isset($addons[$a])) { $adds[] = $a; $unit += $addons[$a]; }
    }
    $spice = in_array($it['sp'] ?? '', ['Tidak Pedas', 'Sedang', 'Pedas Mantap'], true) ? $it['sp'] : null;

    $subtotal += $unit * $qty;
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

$fee   = $type === 'delivery' ? delivery_fee($subtotal) : 0;
$total = $subtotal + $fee;

$order = [
    'customer_name' => $name, 'phone' => $phone, 'order_type' => $type,
    'table_no' => $type === 'dine_in' ? $tableNo : null,
    'address'  => $type === 'delivery' ? $address : null,
    'note' => $note ?: null, 'subtotal' => $subtotal, 'delivery_fee' => $fee, 'total' => $total,
    'payment_method' => $method, 'payment_status' => 'belum_bayar', 'status' => 'baru',
];

// Simpan pesanan (ulang kalau kode kebetulan sudah dipakai)
for ($try = 0; $try < 4; $try++) {
    $order['code'] = new_code();
    $o = sb('POST', 'orders', [$order], ['Prefer: return=representation']);
    if ($o['code'] < 300 && !empty($o['data'][0]['id'])) break;
    if ($o['code'] !== 409) fail(502, 'Gagal menyimpan pesanan. Jalankan database/upgrade-pembayaran.sql di Supabase bila belum.');
}
if (empty($o['data'][0]['id'])) fail(502, 'Gagal menyimpan pesanan, coba lagi.');
$orderId = $o['data'][0]['id'];

foreach ($rows as &$row) $row['order_id'] = $orderId;
unset($row);

$oi = sb('POST', 'order_items', $rows);
if ($oi['code'] >= 300) {
    sb('DELETE', 'orders?id=eq.' . $orderId);   // batalkan pesanan yang setengah jadi
    fail(502, 'Gagal menyimpan isi pesanan');
}

json_out(['ok' => true, 'code' => $order['code'], 'total' => $total]);
