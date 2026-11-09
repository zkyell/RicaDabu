<?php
require_once __DIR__ . '/../core/supabase.php';
require_once __DIR__ . '/../core/midtrans.php';

const MENU_FIELDS = 'id,name,price,category,description,image,emoji,is_hot';

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n): string { return 'Rp ' . number_format((int)$n, 0, ',', '.'); }

// Daftar menu sesuai design brief (Makanan · Pelengkap · Minuman).
// Dipakai sebagai cadangan kalau database Supabase belum tersambung, supaya menu tetap tampil.
// Urutan & id sama dengan database/schema.sql.
const LOCAL_MENU = [
    [1,  'Nasi Ayam Rica',            20000, 'Makanan',   'Ayam empuk dengan sambal rica pedas gurih, disajikan dengan nasi hangat.', 'nasi-ayam-rica', '🍛', true],
    [2,  'Nasi Ayam Bakar Dabu-Dabu', 22000, 'Makanan',   'Ayam bakar berbumbu dengan sambal dabu-dabu segar dan nasi.', 'nasi-ayam-bakar', '🍗', true],
    [3,  'Nasi Cakalang Rica',        23000, 'Makanan',   'Suwiran ikan cakalang berbumbu rica pedas gurih, dengan nasi.', 'nasi-cakalang-rica', '🐟', true],
    [4,  'Nasi Ikan Bakar Dabu-Dabu', 25000, 'Makanan',   'Ikan bakar segar disiram sambal dabu-dabu iris khas Manado, dengan nasi.', 'nasi-ikan-bakar', '🔥', true],
    [5,  'Rahang Tuna Dabu-Dabu',     30000, 'Makanan',   'Rahang tuna bakar dengan siraman dabu-dabu segar berlimpah.', 'rahang-tuna', '🐠', true],
    [6,  'Es Teh',                     5000, 'Minuman',   'Teh manis dingin yang menyegarkan.', 'es-teh', '🧋', false],
    [7,  'Teh Hangat',                 5000, 'Minuman',   'Teh manis hangat.', 'teh-hangat', '🍵', false],
    [8,  'Es Jeruk',                   8000, 'Minuman',   'Jeruk peras segar dengan es.', 'es-jeruk', '🍊', false],
    [9,  'Lemon Tea',                  8000, 'Minuman',   'Teh dingin dengan perasan lemon.', 'lemon-tea', '🍋', false],
    [10, 'Kopi Susu',                  7000, 'Minuman',   'Kopi dengan susu, manis pas.', 'kopi-susu', '☕', false],
    [11, 'Air Mineral',                6000, 'Minuman',   'Air mineral botol.', 'air-mineral', '💧', false],
    [12, 'Perkedel Jagung',            8000, 'Pelengkap', 'Perkedel jagung manis, gurih, dan renyah.', 'perkedel-jagung', '🌽', false],
    [13, 'Tumis Kangkung',             8000, 'Pelengkap', 'Kangkung tumis segar.', 'tumis-kangkung', '🥬', false],
    [14, 'Tahu/Tempe',                 5000, 'Pelengkap', 'Tahu atau tempe goreng.', 'tahu-tempe', '🟨', false],
    [15, 'Dabu-Dabu',                  5000, 'Pelengkap', 'Sambal segar cabai, bawang merah, dan tomat.', 'dabu-dabu', '🍅', true],
    [16, 'Sambal Rica',                5000, 'Pelengkap', 'Sambal rica pedas gurih.', 'sambal-rica', '🌶️', true],
];
function local_menu(): array {
    $k = ['id', 'name', 'price', 'category', 'description', 'image', 'emoji', 'is_hot'];
    return array_map(fn($r) => array_combine($k, $r), LOCAL_MENU);
}

function get_menu(): ?array {
    $r = sb('GET', 'menu_items?select=' . MENU_FIELDS . '&is_available=eq.true&order=sort.asc');
    if ($r['code'] === 200 && is_array($r['data']) && $r['data']) return $r['data'];
    return local_menu();
}
function get_item(int $id): ?array {
    $r = sb('GET', 'menu_items?select=' . MENU_FIELDS . '&is_available=eq.true&id=eq.' . $id . '&limit=1');
    if ($r['code'] === 200 && !empty($r['data'][0])) return $r['data'][0];
    if ($r['code'] === 200) return null;           // database tersambung tapi menu habis / tidak ada
    foreach (local_menu() as $m) if ($m['id'] === $id) return $m;
    return null;
}
// Kelompokkan menu per kategori, urutan sesuai flyer: Makanan, Pelengkap, Minuman
function menu_by_cat(array $items): array {
    $g = ['Makanan' => [], 'Pelengkap' => [], 'Minuman' => []];
    foreach ($items as $m) $g[$m['category']][] = $m;
    return array_filter($g);
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

// Ikon garis (SVG) yang dipakai di navigasi & tombol
function icon(string $n, int $size = 22): string {
    static $p = [
        'home'    => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'bag'     => '<path d="M5 8h14l-1 12H6z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
        'receipt' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6M9 16h3"/>',
        'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/>',
        'menu'    => '<path d="M4 6h16M4 12h16M4 18h10"/>',
        'search'  => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'sort'    => '<path d="M7 4v16M3 16l4 4 4-4M17 20V4M13 8l4-4 4 4"/>',
        'back'    => '<path d="M15 5l-7 7 7 7"/>',
        'chat'    => '<path d="M4 5h16v11H9l-5 4z"/>',
        'pin'     => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'x'       => '<path d="M6 6l12 12M18 6 6 18"/>',
        'arrow'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'download'=> '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
    ];
    return '<svg class="ico" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$n] ?? '') . '</svg>';
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
    if (($s['qris']['aktif'] ?? false) && is_file(__DIR__ . '/../' . $s['qris']['gambar']))
        $m['qris'] = ['QRIS', '▦', 'Scan pakai GoPay, m-banking BCA/BRI/BNI/Mandiri, DANA, OVO, ShopeePay'];
    if ($s['tunai']['aktif'] ?? false)
        $m['tunai'] = ['Tunai', '💵', 'Bayar di kasir, atau ke kurir saat pesanan diantar'];
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
