<?php
// Kelola menu: tersedia/habis dan harga
require __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = 'Sesi kedaluwarsa, coba lagi.';
    if (csrf_ok()) {
        $id = (int)($_POST['id'] ?? 0);
        $patch = ['price' => max(0, min(10000000, (int)preg_replace('/\D/', '', (string)($_POST['price'] ?? '0')))),
                  'is_available' => isset($_POST['is_available'])];
        $r = sb('PATCH', 'menu_items?id=eq.' . $id, $patch);
        $msg = $r['code'] < 300 ? 'Menu disimpan.' : 'Gagal menyimpan menu.';
    }
    header('Location: menu.php?ok=' . rawurlencode($msg)); exit;
}

$r = sb('GET', 'menu_items?select=id,name,price,category,is_available,image,emoji&order=sort.asc');
$items = $r['code'] === 200 ? $r['data'] : [];
$root = '../'; $wide = true; $css = ['admin']; $js = ['admin'];
$title = 'Menu – Panel Kasir Rica Dabu';
include __DIR__ . '/../includes/header.php';
?>
<header class="ah">
  <div class="br"><img src="../assets/images/logoricadabu.png" alt=""><b>Panel Kasir</b></div>
  <nav><a href="index.php">Pesanan</a><a class="on" href="menu.php">Menu</a><a href="logout.php">Keluar</a></nav>
</header>
<?php if (isset($_GET['ok'])): ?><p class="flash" role="status"><?= e($_GET['ok']) ?></p><?php endif; ?>
<p class="box">Matikan <b>Tersedia</b> kalau menu sedang habis — menu otomatis hilang dari halaman pelanggan.</p>
<div class="mlist">
<?php foreach ($items as $m): ?>
  <form method="post" class="mi<?= $m['is_available'] ? '' : ' off' ?>">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
    <span class="em"><?= e($m['emoji']) ?></span>
    <div class="nm"><b><?= e($m['name']) ?></b><small><?= e($m['category']) ?></small></div>
    <label class="pr">Rp <input name="price" inputmode="numeric" value="<?= (int)$m['price'] ?>" aria-label="Harga <?= e($m['name']) ?>"></label>
    <label class="sw"><input type="checkbox" name="is_available"<?= $m['is_available'] ? ' checked' : '' ?>><span></span>Tersedia</label>
    <button>Simpan</button>
  </form>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
