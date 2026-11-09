<?php $navOn = $navOn ?? 'home'; ?>
<nav class="bn" id="bn" aria-label="Navigasi bawah">
  <a class="<?= $navOn === 'home' ? 'on' : '' ?>" href="menu.php"><?= icon('home') ?>Beranda</a>
  <a class="<?= $navOn === 'cart' ? 'on' : '' ?>" href="checkout.php"><?= icon('bag') ?>Keranjang<em class="nb" id="navQty" data-qty hidden></em></a>
  <a class="<?= $navOn === 'orders' ? 'on' : '' ?>" href="riwayat.php"><?= icon('receipt') ?>Pesanan</a>
  <a class="<?= $navOn === 'prof' ? 'on' : '' ?>" href="profil.php"><?= icon('user') ?>Profil</a>
</nav>
