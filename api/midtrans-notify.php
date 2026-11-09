<?php
// Webhook Midtrans. Isi URL ini di dashboard Midtrans:
//   Settings > Configuration > Payment Notification URL
//   https://DOMAIN-KAMU/api/midtrans-notify.php
require __DIR__ . '/../includes/functions.php';

if (!midtrans_on()) json_out(['error' => 'off'], 503);
$n = json_decode(file_get_contents('php://input'), true) ?: [];
$mid = (string)($n['order_id'] ?? '');

// 1) Cek tanda tangan dari Midtrans
$sig = hash('sha512', $mid . ($n['status_code'] ?? '') . ($n['gross_amount'] ?? '') . MIDTRANS_SERVER_KEY);
if (!$mid || !hash_equals($sig, (string)($n['signature_key'] ?? ''))) json_out(['error' => 'Signature tidak valid'], 403);

// 2) Ambil status langsung dari Midtrans (jangan percaya isi notifikasi mentah-mentah)
$st = midtrans_status($mid) ?? $n;
$code = substr($mid, 0, 9);               // "RD-XXXXXX-abc" → "RD-XXXXXX"
$o = get_order($code, false);
if (!$o) json_out(['ok' => true]);         // bukan pesanan kita, abaikan

if ((int)round((float)($st['gross_amount'] ?? 0)) !== (int)$o['total']) json_out(['error' => 'Nominal tidak cocok'], 422);

$ts = $st['transaction_status'] ?? '';
$fraud = $st['fraud_status'] ?? 'accept';
$patch = ['payment_ref' => ($st['payment_type'] ?? '') . ' ' . ($st['transaction_id'] ?? '')];

if (($ts === 'capture' && $fraud === 'accept') || $ts === 'settlement') {
    $patch += ['payment_status' => 'lunas', 'paid_at' => gmdate('c')];
} elseif ($ts === 'pending') {
    if ($o['payment_status'] !== 'lunas') $patch['payment_status'] = 'belum_bayar';
} elseif (in_array($ts, ['deny', 'cancel', 'expire', 'failure'], true)) {
    if ($o['payment_status'] !== 'lunas') $patch['payment_status'] = 'gagal';
}
sb('PATCH', 'orders?code=eq.' . $o['code'], $patch);
json_out(['ok' => true]);
