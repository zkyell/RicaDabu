<?php $navOn = $navOn ?? 'home'; ?>
<nav class="bn" id="bn">
  <a class="<?= $navOn === 'home' ? 'on' : '' ?>" href="menu.php"><span>🏠</span>Beranda</a>
  <a class="<?= $navOn === 'cart' ? 'on' : '' ?>" href="checkout.php"><span>🛍</span>Keranjang<em class="nb" id="navQty" hidden></em></a>
  <a class="<?= $navOn === 'orders' ? 'on' : '' ?>" href="riwayat.php"><span>🧾</span>Pesanan</a>
  <a class="<?= $navOn === 'prof' ? 'on' : '' ?>" href="profil.php"><span>👤</span>Profil</a>
</nav>
