<?php
// Pelanggan mengunggah foto bukti pembayaran (QRIS / transfer / e-wallet)
require __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Method tidak diizinkan'], 405);

$o = get_order(strtoupper((string)($_POST['kode'] ?? '')), false);
if (!$o) json_out(['error' => 'Pesanan tidak ditemukan'], 404);
if (!needs_proof($o['payment_method'])) json_out(['error' => 'Metode pembayaran ini tidak memerlukan bukti.'], 422);
if (!in_array($o['payment_status'], ['belum_bayar', 'ditolak', 'menunggu_verifikasi'], true) || $o['status'] === 'batal')
    json_out(['error' => 'Pembayaran pesanan ini sudah diproses.'], 422);

$f = $_FILES['bukti'] ?? null;
if (!$f || $f['error'] !== UPLOAD_ERR_OK) json_out(['error' => 'Pilih foto bukti pembayaran dulu.'], 422);
if ($f['size'] > 3 * 1024 * 1024) json_out(['error' => 'Ukuran foto maksimal 3 MB.'], 422);

$info = @getimagesize($f['tmp_name']);
$ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
if (!$ext) json_out(['error' => 'File harus berupa foto JPG, PNG, atau WEBP.'], 422);

$path = $o['code'] . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
if (!sb_upload($path, file_get_contents($f['tmp_name']), $info['mime']))
    json_out(['error' => 'Gagal mengunggah. Pastikan bucket "bukti-bayar" sudah dibuat (jalankan SQL upgrade).'], 502);

$u = sb('PATCH', 'orders?code=eq.' . $o['code'], ['payment_proof' => $path, 'payment_status' => 'menunggu_verifikasi']);
if ($u['code'] >= 300) json_out(['error' => 'Gagal menyimpan bukti pembayaran.'], 502);

json_out(['ok' => true, 'payment_status' => 'menunggu_verifikasi']);
