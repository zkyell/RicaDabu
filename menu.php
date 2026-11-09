<?php
// Beranda / daftar menu
require __DIR__ . '/includes/functions.php';
$title = 'Menu – Rica Dabu, Rumah Makan Manado';
$css = ['menu', 'cart'];
$navOn = 'home';
$js  = ['common', 'cart', 'menu'];
$siteFooter = true;
$items = get_menu();
$groups = menu_by_cat($items);
$s = store();
include __DIR__ . '/includes/header.php';
?>
<main id="home">
  <header class="top m-only">
    <div class="br"><img src="assets/images/logoricadabu.png" alt=""><b class="brush">Rica <span style="color:var(--r)">Dabu</span></b></div>
    <div class="rt">
      <a class="ib" href="riwayat.php" aria-label="Pesanan saya"><?= icon('receipt', 20) ?></a>
      <a class="av" href="profil.php" aria-label="Profil"><?= icon('user', 20) ?></a>
    </div>
  </header>

  <!-- HERO -->
  <section class="wrap">
    <div class="hero">
      <div class="hero-tx">
        <span class="kop">Rumah Makan Manado</span>
        <h1>Pedas, segar,<br>rasa <em>kampung halaman.</em></h1>
        <p class="tagline">Made with Love, Served with Proud</p>
        <p class="lead">Rica-rica &amp; dabu-dabu khas Sulawesi Utara, dimasak seperti di rumah sendiri. Pesan untuk makan di tempat, ambil sendiri, atau diantar.</p>
        <div class="hero-cta">
          <a class="red btn" href="#menuTop">Pesan Sekarang <?= icon('arrow', 18) ?></a>
          <a class="ghost btn" href="#flyer">Lihat Daftar Menu</a>
        </div>
        <ul class="hero-meta">
          <li><?= icon('clock', 16) ?><?= e($s['jam_buka']) ?></li>
          <li><?= icon('pin', 16) ?><?= e($s['alamat']) ?></li>
        </ul>
      </div>
      <div class="hero-img">
        <div class="ring"><div class="ph"><img src="assets/images/hero-ikan-bakar.jpg" alt="Ikan bakar dabu-dabu" onerror="this.remove()"><span>🐟</span></div></div>
        <span class="float f1">🌶️ Pedas khas Manado</span>
        <span class="float f2">⭐ Favorit: Rahang Tuna</span>
      </div>
    </div>
  </section>

  <!-- PENCARIAN & KATEGORI -->
  <section class="wrap" id="menuTop">
    <div class="sh">
      <div><h2>Menu Pilihan Manado</h2><p>Racikan bumbu dabu-dabu segar setiap hari</p></div>
      <b id="count"></b>
    </div>
    <div class="tools">
      <label class="srch"><?= icon('search', 20) ?><input id="search" type="search" placeholder="Cari menu khas Manado…" aria-label="Cari menu"></label>
      <div class="tabs" id="tabs" role="tablist">
        <button class="tab on" data-c="Semua">Semua</button>
        <button class="tab" data-c="Makanan">🍛 Makanan</button>
        <button class="tab" data-c="Pelengkap">🌽 Pelengkap</button>
        <button class="tab" data-c="Minuman">🧋 Minuman</button>
      </div>
      <button class="ib sq" id="sort" aria-label="Urutkan menu" title="Urutkan"><?= icon('sort', 20) ?></button>
    </div>

    <div id="list" class="grid">
<?php foreach ($items as $m): $x = extra($m['name']); ?>
      <a class="cd" href="detail.php?id=<?= (int)$m['id'] ?>" data-p="<?= (int)$m['price'] ?>" data-c="<?= e($m['category']) ?>" data-n="<?= e(mb_strtolower($m['name'])) ?>">
        <div class="th"><?= photo($m) ?><?php if ($m['is_hot']): ?><em class="pd">🔥 Pedas</em><?php endif; ?><?php if (!empty($x['tag'])): ?><em class="fv"><?= e($x['tag']) ?></em><?php endif; ?></div>
        <div class="in">
          <span class="cat"><?= e($m['category']) ?></span>
          <h3><?= e($m['name']) ?></h3>
          <p><?= e($m['description']) ?></p>
          <div class="rw"><b><?= rp($m['price']) ?></b><span class="ad" aria-hidden="true">+</span></div>
        </div>
      </a>
<?php endforeach; ?>
      <p class="empty" id="none" hidden>Menu tidak ditemukan. Coba kata kunci lain.</p>
    </div>
  </section>

  <!-- FLYER MENU (sesuai design brief: latar hitam, judul MENU oranye bergaya brush) -->
  <section class="wrap" id="flyer">
    <div class="flyer">
      <svg class="leaf" viewBox="0 0 100 100" aria-hidden="true"><path fill="currentColor" d="M50 2l8 20 14-10-3 20 20-4-12 16 18 8-20 6 8 18-20-8-3 22-10-16-10 16-3-22-20 8 8-18-20-6 18-8L12 28l20 4-3-20 14 10z"/><path d="M50 60v38" stroke="currentColor" stroke-width="4"/></svg>
      <svg class="splash" viewBox="0 0 200 200" aria-hidden="true"><path fill="currentColor" d="M40 120c-30-10-38-50-6-62 24-9 30 14 52-4s16-50 52-40 30 44 10 60 6 30 26 30 18 40-16 44-40-18-62-6-30 30-52 18 16-30-4-40z"/><circle cx="170" cy="40" r="9" fill="currentColor"/><circle cx="22" cy="170" r="6" fill="currentColor"/><circle cx="185" cy="90" r="5" fill="currentColor"/></svg>

      <div class="fl-head">
        <div>
          <span class="fl-kop">Rumah Makan Manado</span>
          <h2 class="brush">MENU</h2>
          <p class="fl-tag">Made with Love, Served with Proud</p>
        </div>
        <div class="fl-logo"><img src="assets/images/logoricadabu.png" alt="Logo Rica Dabu"></div>
      </div>

      <div class="fl-cols">
<?php foreach ($groups as $cat => $list): ?>
        <div class="fl-cat fl-<?= strtolower($cat) ?>">
          <h3 class="brush"><?= e(strtoupper($cat)) ?></h3>
          <ul>
<?php foreach ($list as $m): ?>
            <li><a href="detail.php?id=<?= (int)$m['id'] ?>"><span><?= e($m['name']) ?><?= $m['is_hot'] ? ' <i>🌶</i>' : '' ?></span><b><?= e(number_format((int)$m['price'] / 1000, 0)) ?>K</b></a></li>
<?php endforeach; ?>
          </ul>
        </div>
<?php endforeach; ?>
      </div>

      <div class="fl-foot">
        <div class="fl-photo"><div class="ph"><img src="assets/images/nasi-ikan-bakar.jpg" alt="Nasi ikan bakar dabu-dabu" loading="lazy" onerror="this.remove()"><span>🐟</span></div></div>
        <p>Harga dalam ribuan rupiah · Klik menu untuk memesan<br><b>Bayar mudah pakai QRIS</b> — GoPay, BCA, Mandiri, BNI, BRI &amp; bank lainnya</p>
      </div>
    </div>
  </section>
</main>
<?php include __DIR__ . '/includes/cart.php'; include __DIR__ . '/includes/nav.php'; include __DIR__ . '/includes/footer.php'; ?>
