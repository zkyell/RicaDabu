<?php
// Membuat token Midtrans Snap untuk pesanan dengan metode "Bayar Online Otomatis"
require __DIR__ . '/../includes/functions.php';

if (!midtrans_on()) json_out(['error' => 'Pembayaran online belum diaktifkan.'], 503);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Method tidak diizinkan'], 405);

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$o  = get_order(strtoupper((string)($in['kode'] ?? '')));
if (!$o) json_out(['error' => 'Pesanan tidak ditemukan'], 404);
if ($o['payment_method'] !== 'midtrans') json_out(['error' => 'Metode pembayaran pesanan ini bukan bayar online.'], 422);
if ($o['payment_status'] === 'lunas') json_out(['error' => 'Pesanan ini sudah lunas.'], 422);
if ($o['status'] === 'batal') json_out(['error' => 'Pesanan ini sudah dibatalkan.'], 422);

// order_items di sini belum punya menu_item_id; ambil lengkap
$it = sb('GET', 'order_items?select=menu_item_id,name,unit_price,qty&order_id=eq.' . (int)$o['id']);
if ($it['code'] !== 200) json_out(['error' => 'Gagal membaca isi pesanan'], 502);

$snap = midtrans_create($o, $it['data'], base_url() . '/pesanan.php?kode=' . $o['code']);
if (!$snap) json_out(['error' => 'Gagal menghubungi Midtrans. Periksa server key.'], 502);

json_out(['token' => $snap['token']]);
