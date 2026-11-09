<?php
// Menampilkan foto bukti pembayaran (bucket privat) khusus untuk admin
require __DIR__ . '/../includes/auth.php';
require_admin();
$o = get_order(strtoupper((string)($_GET['kode'] ?? '')), false);
if (!$o || !$o['payment_proof']) { http_response_code(404); exit('Bukti tidak ditemukan'); }
$f = sb_download($o['payment_proof']);
if (!$f) { http_response_code(502); exit('Gagal mengambil file dari Supabase Storage'); }
header('Content-Type: ' . (preg_match('#^image/(jpeg|png|webp)$#', $f['mime']) ? $f['mime'] : 'application/octet-stream'));
header('Cache-Control: private, max-age=300');
header('X-Content-Type-Options: nosniff');
echo $f['bytes'];
