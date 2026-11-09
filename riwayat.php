<?php
// Daftar pesanan yang pernah dibuat dari HP ini (kodenya disimpan di browser)
require __DIR__ . '/includes/functions.php';
$title = 'Pesanan Saya – Rica Dabu';
$css = ['page'];
$js  = ['common', 'riwayat'];
$navOn = 'orders';
include __DIR__ . '/includes/header.php';
?>
<main class="pg">
  <header class="top"><div class="br"><img src="assets/images/logoricadabu.png" alt=""><b>Pesanan Saya</b></div></header>

  <form class="blk find" id="find">
    <label class="lb" for="kode">Lacak dengan kode pesanan</label>
    <div class="row"><input class="f" id="kode" placeholder="RD-XXXXXX" maxlength="9" autocapitalize="characters"><button class="red">Lacak</button></div>
  </form>

  <div id="list"><p class="hint center">Memuat pesanan…</p></div>
</main>
<div id="toast" role="status"></div>
<?php include __DIR__ . '/includes/nav.php'; include __DIR__ . '/includes/footer.php'; ?>
