<?php
// Profil pelanggan (disimpan di browser) + info toko
require __DIR__ . '/includes/functions.php';
$s = store();
$title = 'Profil – Rica Dabu';
$css = ['page'];
$js  = ['common', 'profil'];
$navOn = 'prof';
include __DIR__ . '/includes/header.php';
?>
<main class="pg">
  <header class="top"><div class="br"><img src="assets/images/logoricadabu.png" alt=""><b>Profil</b></div></header>

  <div class="cols">
  <div class="col-main">
  <section class="blk hello"><span class="avt" id="avt">👤</span><div><h2 id="hName">Halo!</h2><p class="sub">Data ini hanya disimpan di HP kamu dan dipakai untuk mengisi checkout otomatis.</p></div></section>

  <form class="blk" id="prof">
    <h2>Data saya</h2>
    <label class="lb" for="pName">Nama</label><input class="f" id="pName" maxlength="80" autocomplete="name">
    <label class="lb" for="pPhone">Nomor HP / WhatsApp</label><input class="f" id="pPhone" inputmode="tel" maxlength="16" autocomplete="tel">
    <label class="lb" for="pAddr">Alamat pengantaran</label><textarea class="f" id="pAddr" rows="3" maxlength="250"></textarea>
    <button class="red full">Simpan</button>
  </form>

  </div>
  <aside class="col-side">
  <section class="blk">
    <h2>Tentang <?= e($s['nama']) ?></h2>
    <dl class="meta">
      <dt>Alamat</dt><dd><?= e($s['alamat']) ?></dd>
      <dt>Jam buka</dt><dd><?= e($s['jam_buka']) ?></dd>
      <dt>WhatsApp</dt><dd><a class="lnk" href="<?= e(wa_link('Halo Rica Dabu, saya mau tanya.')) ?>" target="_blank" rel="noopener">+<?= e($s['whatsapp']) ?></a></dd>
      <dt>Pembayaran</dt><dd><?= e(implode(', ', array_column(payment_methods(), 0)) ?: '-') ?></dd>
    </dl>
  </section>

<?php if (isset(payment_methods()['qris'])): $q = $s['qris']; ?>
  <section class="blk">
    <h2>Bayar pakai QRIS</h2>
    <div class="qcard">
      <img src="<?= e($q['gambar']) ?>" alt="Kode QRIS <?= e($q['nama_merchant']) ?>" width="160" height="160" loading="lazy">
      <div><span class="qbadge">QRIS · <?= e($q['penyedia'] ?? '') ?></span><b><?= e($q['nama_merchant']) ?></b><small>NMID: <?= e($q['nmid'] ?? '-') ?></small>
        <a class="lnk dl" href="<?= e($q['poster'] ?? $q['gambar']) ?>" download="QRIS-RicaDabu.jpg"><?= icon('download', 16) ?> Simpan QRIS</a></div>
    </div>
  </section>
<?php endif; ?>
  <section class="blk links">
    <a href="riwayat.php">🧾 Riwayat pesanan</a>
    <button type="button" id="clear">🗑 Hapus data di HP ini</button>
    <a href="admin/" class="muted">🔐 Masuk sebagai kasir/admin</a>
  </section>
  </aside>
  </div>
</main>
<div id="toast" role="status"></div>
<?php include __DIR__ . '/includes/nav.php'; include __DIR__ . '/includes/footer.php'; ?>
