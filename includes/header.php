<?php require_once __DIR__ . '/functions.php'; $css = $css ?? []; $root = $root ?? ''; $navOn = $navOn ?? 'home'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="theme-color" content="#3D2418">
<link rel="icon" href="<?= $root ?>assets/images/logoricadabu.png">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="Rica Dabu – Rumah Makan Manado. Made with Love, Served with Proud. Pesan rica-rica & dabu-dabu khas Manado secara online.">
<title><?= e($title ?? 'Rica Dabu – Rumah Makan Manado') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Permanent+Marker&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $root ?>assets/css/base.css">
<?php foreach ($css as $c): ?><link rel="stylesheet" href="<?= $root ?>assets/css/<?= e($c) ?>.css">
<?php endforeach; ?></head>
<body>
<?php if (empty($wide) && empty($bare)): ?>
<header class="site">
  <div class="wrap site-in">
    <a class="logo" href="<?= $root ?>menu.php"><img src="<?= $root ?>assets/images/logoricadabu.png" alt=""><span><b>Rica <i>Dabu</i></b><small>Rumah Makan Manado</small></span></a>
    <nav class="snav" aria-label="Navigasi utama">
      <a class="<?= $navOn === 'home' ? 'on' : '' ?>" href="<?= $root ?>menu.php">Beranda</a>
      <a href="<?= $root ?>menu.php#flyer">Daftar Menu</a>
      <a class="<?= $navOn === 'orders' ? 'on' : '' ?>" href="<?= $root ?>riwayat.php">Pesanan</a>
      <a class="<?= $navOn === 'prof' ? 'on' : '' ?>" href="<?= $root ?>profil.php">Profil</a>
    </nav>
    <a class="scart<?= $navOn === 'cart' ? ' on' : '' ?>" href="<?= $root ?>checkout.php"><?= icon('bag') ?><span>Keranjang</span><em class="nb" data-qty hidden></em></a>
  </div>
</header>
<?php endif; ?>
<div id="app"<?= !empty($wide) ? ' class="wide"' : '' ?>>
