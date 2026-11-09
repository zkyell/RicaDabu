<?php
// Status pesanan untuk pelanggan. GET ?kode=RD-XXXXXX  atau  ?kode=RD-AAA,RD-BBB (maks 30, untuk riwayat)
require __DIR__ . '/../includes/functions.php';
header('Cache-Control: no-store');

$codes = array_slice(array_filter(array_map('trim', explode(',', strtoupper((string)($_GET['kode'] ?? '')))), 'valid_code'), 0, 30);
if (!$codes) json_out(['error' => 'Kode pesanan tidak valid'], 422);

$r = sb('GET', 'orders?select=' . ORDER_FIELDS . ',order_items(name)&code=in.(' . implode(',', $codes) . ')&order=created_at.desc');
if ($r['code'] !== 200) json_out(['error' => 'Gagal membaca pesanan'], 502);
json_out(array_map('public_order', $r['data']));
