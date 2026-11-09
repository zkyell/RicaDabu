<?php
// Panel kasir/admin: daftar pesanan, verifikasi pembayaran, ubah status
require __DIR__ . '/../includes/auth.php';
$root = '../'; $wide = true; $css = ['admin']; $js = ['admin'];

$err = '';
if (($_POST['do'] ?? '') === 'login') {
    if (admin_login((string)($_POST['password'] ?? ''))) { header('Location: index.php'); exit; }
    $err = ADMIN_PASSWORD === '' ? 'ADMIN_PASSWORD belum diisi di config/.env' : 'Password salah.';
}

if (!admin_ok()):
    $title = 'Masuk Admin – Rica Dabu';
    include __DIR__ . '/../includes/header.php'; ?>
<main class="login">
  <form method="post" class="card">
    <img src="../assets/images/logoricadabu.png" alt="" width="64" height="64">
    <h1>Panel Kasir Rica Dabu</h1>
    <?php if (ADMIN_PASSWORD === ''): ?><p class="warn">Isi <b>ADMIN_PASSWORD</b> di config/.env (atau Environment Variables hosting) untuk mengaktifkan panel ini.</p><?php endif; ?>
    <input type="hidden" name="do" value="login">
    <label for="pw">Password</label>
    <input id="pw" type="password" name="password" autocomplete="current-password" required autofocus>
    <?php if ($err): ?><p class="err"><?= e($err) ?></p><?php endif; ?>
    <button class="red full">Masuk</button>
    <a href="../menu.php">← Kembali ke web pemesanan</a>
  </form>
</main>
<?php include __DIR__ . '/../includes/footer.php'; exit; endif;

// ---------- Data ----------
$tab = $_GET['f'] ?? 'aktif';
$filters = [
    'aktif'    => ['Aktif', 'status=in.(baru,diproses,siap,diantar)'],
    'verif'    => ['Perlu verifikasi', 'payment_status=eq.menunggu_verifikasi&status=neq.batal'],
    'selesai'  => ['Selesai', 'status=eq.selesai'],
    'batal'    => ['Batal', 'status=eq.batal'],
    'semua'    => ['Semua', ''],
];
if (!isset($filters[$tab])) $tab = 'aktif';

$r = sb('GET', 'orders?select=' . ORDER_FIELDS . ',order_items(name,unit_price,qty,spice,addons,note)'
    . ($filters[$tab][1] ? '&' . $filters[$tab][1] : '') . '&order=created_at.desc&limit=100');
$orders = $r['code'] === 200 ? $r['data'] : null;

// Ringkasan hari ini (WITA)
$today = (new DateTime('today', new DateTimeZone('Asia/Makassar')))->format('c');
$t = sb('GET', 'orders?select=total,status,payment_status&created_at=gte.' . rawurlencode($today));
$stat = ['n' => 0, 'omzet' => 0, 'verif' => 0, 'aktif' => 0];
foreach (($t['code'] === 200 ? $t['data'] : []) as $x) {
    if ($x['status'] === 'batal') continue;
    $stat['n']++;
    if ($x['payment_status'] === 'lunas') $stat['omzet'] += $x['total'];
    if ($x['payment_status'] === 'menunggu_verifikasi') $stat['verif']++;
    if ($x['status'] !== 'selesai') $stat['aktif']++;
}

$title = 'Pesanan – Panel Kasir Rica Dabu';
include __DIR__ . '/../includes/header.php';
$next = ['baru' => 'diproses', 'diproses' => null, 'siap' => 'selesai', 'diantar' => 'selesai'];
?>
<header class="ah">
  <div class="br"><img src="../assets/images/logoricadabu.png" alt=""><b>Panel Kasir</b></div>
  <nav><a class="on" href="index.php">Pesanan</a><a href="menu.php">Menu</a><a href="logout.php">Keluar</a></nav>
</header>

<section class="stats">
  <div><small>Pesanan hari ini</small><b><?= $stat['n'] ?></b></div>
  <div><small>Belum selesai</small><b><?= $stat['aktif'] ?></b></div>
  <div class="<?= $stat['verif'] ? 'hot' : '' ?>"><small>Perlu verifikasi bayar</small><b><?= $stat['verif'] ?></b></div>
  <div><small>Pemasukan lunas hari ini</small><b><?= rp($stat['omzet']) ?></b></div>
</section>

<?php if (isset($_GET['ok'])): ?><p class="flash" role="status"><?= e($_GET['ok']) ?></p><?php endif; ?>

<div class="ftabs">
<?php foreach ($filters as $k => [$label]): ?>
  <a class="<?= $k === $tab ? 'on' : '' ?>" href="?f=<?= $k ?>"><?= e($label) ?></a>
<?php endforeach; ?>
  <label class="auto"><input type="checkbox" id="auto" checked> Muat ulang otomatis</label>
</div>

<?php if ($orders === null): ?>
<p class="err box">Gagal membaca pesanan. Pastikan database/upgrade-pembayaran.sql sudah dijalankan di Supabase.</p>
<?php elseif (!$orders): ?>
<p class="box">Belum ada pesanan di daftar ini.</p>
<?php endif; ?>

<div class="grid">
<?php foreach ($orders ?? [] as $o):
    $pm = $o['payment_method']; $ps = $o['payment_status']; $st = $o['status'];
    $statuses = $o['order_type'] === 'delivery' ? ['baru', 'diproses', 'diantar', 'selesai'] : ['baru', 'diproses', 'siap', 'selesai'];
?>
  <article class="oc st-<?= e($st) ?>">
    <div class="oh">
      <div><b class="code"><?= e($o['code']) ?></b><small><?= e(tgl($o['created_at'])) ?></small></div>
      <span class="badge s-<?= e($st) ?>"><?= e(ORDER_STATUS[$st][0] ?? $st) ?></span>
    </div>
    <div class="who">
      <b><?= e($o['customer_name']) ?></b>
      <a href="https://wa.me/<?= e(preg_replace(['/\D/', '/^0/'], ['', '62'], $o['phone'])) ?>" target="_blank" rel="noopener"><?= e($o['phone']) ?></a>
      <span class="type"><?= e((ORDER_TYPES[$o['order_type']][1] ?? '') . ' ' . (ORDER_TYPES[$o['order_type']][0] ?? '')) ?>
        <?= $o['table_no'] ? ' · Meja ' . e($o['table_no']) : '' ?></span>
      <?php if ($o['address']): ?><span class="addr">📍 <?= e($o['address']) ?></span><?php endif; ?>
      <?php if ($o['note']): ?><span class="addr">📝 <?= e($o['note']) ?></span><?php endif; ?>
    </div>
    <ul class="its">
<?php foreach ($o['order_items'] as $it): ?>
      <li><b><?= (int)$it['qty'] ?>×</b> <?= e($it['name']) ?>
        <?php $d = implode(' • ', array_filter([$it['spice'], $it['addons'] ? '+ ' . $it['addons'] : null, $it['note']])); if ($d): ?><small><?= e($d) ?></small><?php endif; ?></li>
<?php endforeach; ?>
    </ul>
    <div class="pay">
      <div><small><?= e(['tunai' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer Bank', 'ewallet' => 'E-Wallet', 'midtrans' => 'Online (Midtrans)'][$pm] ?? $pm) ?></small>
        <b><?= rp($o['total']) ?></b><?= $o['delivery_fee'] ? '<small>termasuk ongkir ' . rp($o['delivery_fee']) . '</small>' : '' ?></div>
      <span class="badge b-<?= e($ps) ?>"><?= e(PAY_STATUS[$ps] ?? $ps) ?></span>
    </div>
    <?php if ($o['payment_proof']): ?><a class="proof" href="bukti.php?kode=<?= e($o['code']) ?>" target="_blank" rel="noopener">🧾 Lihat bukti pembayaran</a><?php endif; ?>
    <?php if ($o['payment_ref']): ?><small class="ref">Ref: <?= e($o['payment_ref']) ?></small><?php endif; ?>

    <form method="post" action="aksi.php" class="acts">
      <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
      <input type="hidden" name="kode" value="<?= e($o['code']) ?>">
      <input type="hidden" name="back" value="<?= e($tab) ?>">
<?php if ($st !== 'batal'): ?>
  <?php if ($ps !== 'lunas'): ?>
      <button name="do" value="lunas" class="ok"><?= $ps === 'menunggu_verifikasi' ? '✓ Bukti valid, tandai lunas' : '✓ Tandai lunas' ?></button>
  <?php endif; ?>
  <?php if ($ps === 'menunggu_verifikasi'): ?>
      <button name="do" value="tolak" class="no" data-confirm="Tolak bukti pembayaran ini? Pelanggan akan diminta unggah ulang.">✕ Tolak bukti</button>
  <?php endif; ?>
      <div class="sel">
        <select name="status" aria-label="Ubah status pesanan">
<?php foreach ($statuses as $s2): ?><option value="<?= $s2 ?>"<?= $s2 === $st ? ' selected' : '' ?>><?= e(ORDER_STATUS[$s2][0]) ?></option><?php endforeach; ?>
        </select>
        <button name="do" value="status">Simpan</button>
      </div>
      <button name="do" value="batal" class="ghost" data-confirm="Batalkan pesanan <?= e($o['code']) ?>?">Batalkan</button>
<?php else: ?>
      <button name="do" value="buka" class="ghost">Aktifkan lagi</button>
<?php endif; ?>
    </form>
  </article>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
