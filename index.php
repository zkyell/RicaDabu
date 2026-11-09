<?php
// Tampilan awal (splash) → otomatis pindah ke menu.php
require __DIR__ . '/includes/functions.php';
$title = 'Rica Dabu – Rumah Makan Manado';
$css = ['splash'];
$js  = ['splash'];
include __DIR__ . '/includes/header.php';
?>
<section id="splash">
  <span class="chip pk">🔥 CITA RASA OTENTIK MANADO</span>
  <div class="lg"><img src="assets/images/logoricadabu.png" alt="Logo Rica Dabu"><i class="fire">✺</i></div>
  <h1>Rica Dabu</h1>
  <span class="chip gy">🍴 Rumah Makan Manado Asli</span>
  <p class="q">“Makan enak, harga hemat, bikin nagih! Dijamin puas!”</p>
  <div class="dv"><i></i><span>∴</span><i></i></div>
  <b class="ld">🍲 Memuat menu lezat…</b>
  <div class="pg"><span></span></div>
  <span class="chip gy">📍 Bumi Beringin, Jln. 45</span>
  <noscript><a class="red" href="menu.php" style="text-decoration:none">Lihat Menu</a></noscript>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
