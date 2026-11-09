<?php
// Integrasi Midtrans Snap (opsional). Aktif kalau MIDTRANS_SERVER_KEY & MIDTRANS_CLIENT_KEY diisi.
require_once __DIR__ . '/../config/config.php';

function midtrans_on(): bool { return MIDTRANS_SERVER_KEY !== '' && MIDTRANS_CLIENT_KEY !== ''; }

function midtrans_snap_js(): string {
    return MIDTRANS_PRODUCTION ? 'https://app.midtrans.com/snap/snap.js'
                               : 'https://app.sandbox.midtrans.com/snap/snap.js';
}

function midtrans_api(string $method, string $url, ?array $body = null): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json', 'Content-Type: application/json',
                                   'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')],
        CURLOPT_TIMEOUT        => 20,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $res = curl_exec($ch);
    return ['code' => curl_getinfo($ch, CURLINFO_HTTP_CODE), 'data' => json_decode($res ?: 'null', true)];
}

// Membuat token pembayaran Snap. order_id Midtrans = kode pesanan + akhiran waktu,
// supaya pelanggan bisa mencoba bayar lagi kalau transaksi sebelumnya kedaluwarsa.
function midtrans_create(array $order, array $items, string $finishUrl): ?array {
    $base = MIDTRANS_PRODUCTION ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
    $details = [];
    foreach ($items as $it) {
        $details[] = ['id' => (string)$it['menu_item_id'], 'price' => (int)$it['unit_price'],
                      'quantity' => (int)$it['qty'], 'name' => mb_substr($it['name'], 0, 50)];
    }
    if ((int)$order['delivery_fee'] > 0) {
        $details[] = ['id' => 'ONGKIR', 'price' => (int)$order['delivery_fee'], 'quantity' => 1, 'name' => 'Ongkos kirim'];
    }
    $r = midtrans_api('POST', $base . '/snap/v1/transactions', [
        'transaction_details' => ['order_id' => $order['code'] . '-' . base_convert((string)time(), 10, 36),
                                  'gross_amount' => (int)$order['total']],
        'item_details'     => $details,
        'customer_details' => ['first_name' => $order['customer_name'], 'phone' => $order['phone']],
        'callbacks'        => ['finish' => $finishUrl],
        'expiry'           => ['unit' => 'minutes', 'duration' => 60],
    ]);
    return ($r['code'] === 201 && !empty($r['data']['token'])) ? $r['data'] : null;
}

// Mengecek status transaksi langsung ke Midtrans (dipakai webhook untuk verifikasi ulang)
function midtrans_status(string $midtransOrderId): ?array {
    $base = MIDTRANS_PRODUCTION ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';
    $r = midtrans_api('GET', $base . '/v2/' . rawurlencode($midtransOrderId) . '/status');
    return $r['code'] === 200 ? $r['data'] : null;
}
