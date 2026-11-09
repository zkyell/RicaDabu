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

  <section class="blk hello"><span class="avt" id="avt">👤</span><div><h2 id="hName">Halo!</h2><p class="sub">Data ini hanya disimpan di HP kamu dan dipakai untuk mengisi checkout otomatis.</p></div></section>

  <form class="blk" id="prof">
    <h2>Data saya</h2>
    <label class="lb" for="pName">Nama</label><input class="f" id="pName" maxlength="80" autocomplete="name">
    <label class="lb" for="pPhone">Nomor HP / WhatsApp</label><input class="f" id="pPhone" inputmode="tel" maxlength="16" autocomplete="tel">
    <label class="lb" for="pAddr">Alamat pengantaran</label><textarea class="f" id="pAddr" rows="3" maxlength="250"></textarea>
    <button class="red full">Simpan</button>
  </form>

  <section class="blk">
    <h2>Tentang <?= e($s['nama']) ?></h2>
    <dl class="meta">
      <dt>Alamat</dt><dd><?= e($s['alamat']) ?></dd>
      <dt>Jam buka</dt><dd><?= e($s['jam_buka']) ?></dd>
      <dt>WhatsApp</dt><dd><a class="lnk" href="<?= e(wa_link('Halo Rica Dabu, saya mau tanya.')) ?>" target="_blank" rel="noopener">+<?= e($s['whatsapp']) ?></a></dd>
      <dt>Pembayaran</dt><dd><?= e(implode(', ', array_column(payment_methods(), 0)) ?: '-') ?></dd>
    </dl>
  </section>

  <section class="blk links">
    <a href="riwayat.php">🧾 Riwayat pesanan</a>
    <button type="button" id="clear">🗑 Hapus data di HP ini</button>
    <a href="admin/" class="muted">🔐 Masuk sebagai kasir/admin</a>
  </section>
</main>
<div id="toast" role="status"></div>
<?php include __DIR__ . '/includes/nav.php'; include __DIR__ . '/includes/footer.php'; ?>
