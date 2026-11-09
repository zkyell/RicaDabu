<?php
// Tampilan awal (splash) → otomatis pindah ke menu.php
require __DIR__ . '/includes/functions.php';
$title = 'Rica Dabu – Rumah Makan Manado';
$css = ['splash'];
$js  = ['splash'];
$bare = true;
include __DIR__ . '/includes/header.php';
?>
<section id="splash">
  <div class="sp-in">
    <span class="sp-kop">Rumah Makan Manado</span>
    <div class="lg"><img src="assets/images/logoricadabu.png" alt="Logo Rica Dabu"></div>
    <h1 class="brush">Rica <span>Dabu</span></h1>
    <p class="q">Made with Love, Served with Proud</p>
    <div class="pg"><span></span></div>
    <b class="ld">Menyiapkan menu pedas khas Manado…</b>
    <noscript><a class="red btn" href="menu.php">Lihat Menu</a></noscript>
  </div>
  <span class="sp-loc">📍 <?= e(store()['alamat']) ?></span>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
