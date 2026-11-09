<?php
require_once __DIR__ . '/../core/supabase.php';
require_once __DIR__ . '/../core/midtrans.php';

const MENU_FIELDS = 'id,name,price,category,description,image,emoji,is_hot';

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n): string { return 'Rp ' . number_format((int)$n, 0, ',', '.'); }

function get_menu(): ?array {
    $r = sb('GET', 'menu_items?select=' . MENU_FIELDS . '&is_available=eq.true&order=sort.asc');
    return ($r['code'] === 200 && is_array($r['data'])) ? $r['data'] : null;
}
function get_item(int $id): ?array {
    $r = sb('GET', 'menu_items?select=' . MENU_FIELDS . '&is_available=eq.true&id=eq.' . $id . '&limit=1');
    return ($r['code'] === 200 && !empty($r['data'][0])) ? $r['data'][0] : null;
}
function photo(array $m): string {
    return '<div class="ph"><img src="assets/images/' . e($m['image']) . '.jpg" alt="' . e($m['name'])
         . '" loading="lazy" onerror="this.remove()"><span>' . e($m['emoji'] ?: '🍽️') . '</span></div>';
}
// Tambahan tampilan dari desain Stitch. Kosongkan array ini kalau tidak mau menampilkan
// harga coret, rating, dan estimasi waktu.
function extra(string $name): array {
    static $x = [
        'Nasi Ikan Bakar Dabu-Dabu' => ['top' => 1, 'fav' => 1, 'old' => 32000, 'rate' => '4.9 (1.2k ulasan)', 'time' => '15–20 Menit',
            'desc' => 'Ikan laut bakar segar dengan bumbu rempah Manado, disajikan dengan seporsi nasi pulen hangat, lalapan timun segar, dan siraman sambal dabu-dabu iris (tomat merah & hijau, cabai rawit, bawang merah, jeruk lemon cui asli).'],
        'Rahang Tuna Dabu-Dabu' => ['tag' => 'FAVORIT'],
    ];
    return $x[$name] ?? [];
}

// ================== Toko, pesanan & pembayaran ==================

function store(): array {
    static $s = null;
    return $s ??= require __DIR__ . '/../config/toko.php';
}

function base_url(): string {
    $https = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    $host  = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $dir   = preg_replace('#/(api|admin)$#', '', $dir);
    return ($https ? 'https' : 'http') . '://' . $host . $dir;
}

// Metode pembayaran yang aktif → [kode => [label, ikon, keterangan]]
function payment_methods(): array {
    $s = store();
    $m = [];
    if ($s['tunai']['aktif'] ?? false)
        $m['tunai'] = ['Tunai', '💵', 'Bayar di kasir, atau ke kurir saat pesanan diantar'];
    if (($s['qris']['aktif'] ?? false) && is_file(__DIR__ . '/../' . $s['qris']['gambar']))
        $m['qris'] = ['QRIS', '▦', 'Scan pakai m-banking, DANA, GoPay, OVO, ShopeePay'];
    if (($s['transfer']['aktif'] ?? false) && !empty($s['transfer']['rekening']))
        $m['transfer'] = ['Transfer Bank', '🏦', implode(', ', array_column($s['transfer']['rekening'], 'bank'))];
    if (($s['ewallet']['aktif'] ?? false) && !empty($s['ewallet']['akun']))
        $m['ewallet'] = ['E-Wallet', '📱', implode(', ', array_column($s['ewallet']['akun'], 'nama'))];
    if (midtrans_on())
        $m['midtrans'] = ['Bayar Online Otomatis', '⚡', 'QRIS, Virtual Account, e-wallet, kartu — langsung terkonfirmasi'];
    return $m;
}

// Metode yang butuh upload bukti bayar manual
function needs_proof(string $method): bool { return in_array($method, ['qris', 'transfer', 'ewallet'], true); }

function delivery_fee(int $subtotal): int {
    $s = store();
    $min = (int)($s['gratis_ongkir_min'] ?? 0);
    return ($min > 0 && $subtotal >= $min) ? 0 : (int)($s['ongkir'] ?? 0);
}

const ORDER_TYPES = ['dine_in' => ['Makan di Tempat', '🍽️'], 'takeaway' => ['Ambil Sendiri', '🛍'], 'delivery' => ['Diantar', '🛵']];

const ORDER_STATUS = [
    'baru'     => ['Pesanan diterima', 'Menunggu dikonfirmasi dapur'],
    'diproses' => ['Sedang dimasak', 'Dapur sedang menyiapkan pesananmu'],
    'siap'     => ['Siap', 'Pesanan siap disajikan / diambil'],
    'diantar'  => ['Sedang diantar', 'Kurir sedang menuju alamatmu'],
    'selesai'  => ['Selesai', 'Terima kasih, selamat menikmati!'],
    'batal'    => ['Dibatalkan', 'Pesanan ini dibatalkan'],
];

const PAY_STATUS = [
    'belum_bayar'         => 'Belum dibayar',
    'menunggu_verifikasi' => 'Menunggu verifikasi',
    'lunas'               => 'Lunas',
    'ditolak'             => 'Bukti ditolak',
    'gagal'               => 'Pembayaran gagal',
];

const ORDER_FIELDS = 'id,code,customer_name,phone,order_type,table_no,address,note,subtotal,delivery_fee,total,'
                   . 'status,payment_method,payment_status,payment_proof,payment_ref,paid_at,created_at,updated_at';

function valid_code(string $c): bool { return (bool)preg_match('/^RD-[A-Z0-9]{6}$/', $c); }

function get_order(string $code, bool $withItems = true): ?array {
    if (!valid_code($code)) return null;
    $sel = ORDER_FIELDS . ($withItems ? ',order_items(name,unit_price,qty,spice,addons,note)' : '');
    $r = sb('GET', 'orders?select=' . $sel . '&code=eq.' . $code . '&limit=1');
    return ($r['code'] === 200 && !empty($r['data'][0])) ? $r['data'][0] : null;
}

function new_code(): string {
    $abc = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';   // tanpa I, L, O, 0, 1 supaya tidak tertukar
    $c = '';
    for ($i = 0; $i < 6; $i++) $c .= $abc[random_int(0, strlen($abc) - 1)];
    return 'RD-' . $c;
}

function wa_link(string $text): string {
    return 'https://wa.me/' . preg_replace('/\D/', '', store()['whatsapp']) . '?text=' . rawurlencode($text);
}

function tgl(?string $iso): string {
    if (!$iso) return '-';
    $d = new DateTime($iso);
    $d->setTimezone(new DateTimeZone('Asia/Makassar'));
    $bln = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return $d->format('j') . ' ' . $bln[(int)$d->format('n') - 1] . ' ' . $d->format('Y, H.i') . ' WITA';
}

// Data pesanan yang aman dikirim ke browser pelanggan
function public_order(array $o): array {
    return [
        'code' => $o['code'], 'status' => $o['status'], 'status_label' => ORDER_STATUS[$o['status']][0] ?? $o['status'],
        'payment_method' => $o['payment_method'], 'payment_status' => $o['payment_status'],
        'payment_label' => PAY_STATUS[$o['payment_status']] ?? $o['payment_status'],
        'order_type' => $o['order_type'], 'total' => (int)$o['total'],
        'created_at' => $o['created_at'], 'updated_at' => $o['updated_at'] ?? null,
        'items' => count($o['order_items'] ?? []),
    ];
}

function json_out($data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
