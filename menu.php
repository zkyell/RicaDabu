<?php
// Tampilan menu: daftar menu diambil dari Supabase oleh PHP
require __DIR__ . '/includes/functions.php';
$title = 'Menu – Rica Dabu';
$css = ['menu', 'cart'];
$navOn = 'home';
$js  = ['common', 'cart', 'menu'];
$items = get_menu();
include __DIR__ . '/includes/header.php';
?>
<main id="home">
  <header class="top">
    <div class="br"><img src="assets/images/logoricadabu.png" alt=""><b>Rica Dabu</b></div>
    <div class="rt"><span>Beranda</span><a class="av" href="profil.php" aria-label="Profil"><svg viewBox="0 0 24 24" width="18" fill="#fff"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg></a></div>
  </header>
  <div class="hi">
    <img src="assets/images/logoricadabu.png" alt="">
    <div><small>Selamat Datang 👋</small><h2>Mau makan apa hari ini?</h2></div>
    <a class="ib" href="riwayat.php" aria-label="Pesanan saya" style="text-decoration:none">🧾</a>
  </div>
  <a class="loc" href="profil.php" style="text-decoration:none">📍 <?= e(store()['alamat']) ?></a>
  <div class="sr">
    <label class="srch">🔍<input id="search" type="search" placeholder="Cari menu khas Manado..." aria-label="Cari menu"></label>
    <button class="ib sq" id="sort" aria-label="Urutkan menu" title="Urutkan">⇅</button>
  </div>
  <div class="promo">
    <div class="pt">
      <span class="live">● BUKA TIAP HARI</span>
      <h3>Spesial Ikan Bakar &amp; Rahang Tuna Dabu-Dabu!</h3>
      <p>Pedas segar khas Manado, bumbu rempah melimpah.</p>
      <a href="#menuTop">Pesan Sekarang →</a>
    </div>
    <div class="pi"><div class="ph"><img src="assets/images/hero-ikan-bakar.jpg" alt="Ikan bakar dabu-dabu" onerror="this.remove()"><span>🐟</span></div></div>
  </div>
  <div class="tabs" id="tabs">
    <button class="tab on" data-c="Semua">Semua</button>
    <button class="tab" data-c="Makanan">Makanan</button>
    <button class="tab" data-c="Minuman">Minuman</button>
    <button class="tab" data-c="Pelengkap">Pelengkap</button>
  </div>
  <div class="sh" id="menuTop">
    <div><h3>Menu Pilihan Manado</h3><p>Racikan bumbu dabu-dabu segar setiap hari</p></div>
    <b id="count"></b>
  </div>
  <div id="list">
<?php if ($items === null): ?>
    <p class="empty">Menu belum bisa dimuat. Periksa config/config.php dan pastikan schema.sql sudah dijalankan di Supabase.</p>
<?php else: foreach ($items as $m): $x = extra($m['name']); ?>
    <a class="cd" href="detail.php?id=<?= (int)$m['id'] ?>" data-p="<?= (int)$m['price'] ?>" data-c="<?= e($m['category']) ?>" data-n="<?= e(mb_strtolower($m['name'])) ?>">
      <div class="th"><?= photo($m) ?><?php if ($m['is_hot']): ?><em class="pd">🔥 Pedas</em><?php endif; ?><?php if (!empty($x['tag'])): ?><em class="fv"><?= e($x['tag']) ?></em><?php endif; ?></div>
      <div class="in"><h4><?= e($m['name']) ?></h4><p><?= e($m['description']) ?></p>
        <div class="rw"><b><?= rp($m['price']) ?></b><span class="ad">+ Tambah</span></div></div>
    </a>
<?php endforeach; endif; ?>
    <p class="empty" id="none" hidden>Menu tidak ditemukan. Coba kata kunci lain.</p>
  </div>
</main>
<?php include __DIR__ . '/includes/cart.php'; include __DIR__ . '/includes/nav.php'; include __DIR__ . '/includes/footer.php'; ?>
