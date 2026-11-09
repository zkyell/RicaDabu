<?php
// Aksi admin: ubah status pesanan & pembayaran
require __DIR__ . '/../includes/auth.php';
require_admin();
$back = 'index.php?f=' . rawurlencode((string)($_POST['back'] ?? 'aktif'));
function go(string $m): never { global $back; header('Location: ' . $back . '&ok=' . rawurlencode($m)); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok()) go('Sesi kedaluwarsa, coba lagi.');
$o = get_order(strtoupper((string)($_POST['kode'] ?? '')), false);
if (!$o) go('Pesanan tidak ditemukan.');

$do = (string)($_POST['do'] ?? '');
switch ($do) {
    case 'lunas':  $patch = ['payment_status' => 'lunas', 'paid_at' => gmdate('c')]; $msg = "{$o['code']} ditandai lunas."; break;
    case 'tolak':  $patch = ['payment_status' => 'ditolak']; $msg = "Bukti {$o['code']} ditolak."; break;
    case 'batal':  $patch = ['status' => 'batal']; $msg = "{$o['code']} dibatalkan."; break;
    case 'buka':   $patch = ['status' => 'baru']; $msg = "{$o['code']} diaktifkan lagi."; break;
    case 'status':
        $s = (string)($_POST['status'] ?? '');
        if (!isset(ORDER_STATUS[$s]) || $s === 'batal') go('Status tidak valid.');
        $patch = ['status' => $s]; $msg = "{$o['code']}: " . ORDER_STATUS[$s][0] . '.'; break;
    default: go('Aksi tidak dikenal.');
}
$r = sb('PATCH', 'orders?code=eq.' . $o['code'], $patch);
go($r['code'] < 300 ? $msg : 'Gagal menyimpan perubahan.');
