<?php
// Tampilan detail satu menu: detail.php?id=4
require __DIR__ . '/includes/functions.php';
$m = get_item((int)($_GET['id'] ?? 0));
if (!$m) { header('Location: menu.php'); exit; }
$x = extra($m['name']);
$isDrink = $m['category'] === 'Minuman';
$isFood  = $m['category'] === 'Makanan';
$title = $m['name'] . ' – Rica Dabu';
$css = ['detail'];
$js  = ['common', 'detail'];
include __DIR__ . '/includes/header.php';
?>
<section id="detail" data-id="<?= (int)$m['id'] ?>" data-name="<?= e($m['name']) ?>" data-price="<?= (int)$m['price'] ?>">
  <header class="top">
    <a class="bk" href="menu.php" aria-label="Kembali" style="text-decoration:none;display:grid;place-items:center">←</a>
    <div class="br"><img src="assets/images/logoricadabu.png" alt=""><b>Detail Menu</b></div>
    <button class="av" aria-label="Akun"><svg viewBox="0 0 24 24" width="18" fill="#fff"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg></button>
  </header>
  <div class="db">
    <div class="sp"><span class="chip gy">🍴 Khas Manado Asli</span><button class="ib" id="fav" aria-label="Favorit">♡</button></div>
    <div class="hp"><?= photo($m) ?>
      <?php if (!empty($x['top'])): ?><em class="pd lg2">🔥 Terlaris</em><?php endif; ?>
      <?php if (!empty($x['rate'])): ?><span class="rt">⭐ <?= e($x['rate']) ?></span><?php endif; ?></div>
    <h1 id="dName"><?= e($m['name']) ?></h1>
    <div class="pr"><b><?= rp($m['price']) ?></b><?php if (!empty($x['old'])): ?><s><?= rp($x['old']) ?></s><?php endif; ?></div>
    <div class="tg">
      <?php if ($m['is_hot']): ?><span class="tg1">🌶️ Pedas Khas Manado</span><?php endif; ?>
      <?php if (!empty($x['fav'])): ?><span class="tg2">⭐ Favorit Pelanggan</span><?php endif; ?>
      <?php if (!empty($x['time'])): ?><span class="tg2">⏱ <?= e($x['time']) ?></span><?php endif; ?>
    </div>
    <p class="ds"><?= e($x['desc'] ?? $m['description']) ?></p>

<?php if (!$isDrink): ?>
    <div id="dSpice">
      <h4>📈 Tingkat Kepedasan <em class="wj">Wajib</em></h4>
      <label class="op"><input type="radio" name="sp" value="Tidak Pedas"><span class="rdo"></span><span class="tx"><b>Tidak Pedas</b><small>Sambal dipisah / dabu tanpa cabai</small></span><span class="em">🌿</span></label>
      <label class="op"><input type="radio" name="sp" value="Sedang" checked><span class="rdo"></span><span class="tx"><b>Sedang</b><small>Pedas standar nikmat dabu-dabu</small></span><span class="em">🌶️</span></label>
      <label class="op"><input type="radio" name="sp" value="Pedas Mantap"><span class="rdo"></span><span class="tx"><b>Pedas Mantap <em class="rk">Rekomendasi</em></b><small>Ekstra cabai rawit Manado berlimpah</small></span><span class="em">🌶️🔥</span></label>
    </div>
<?php endif; if ($isFood): ?>
    <div id="dAdd">
      <h4>⊕ Pilihan Pelengkap <em>Opsional</em></h4>
<?php foreach ([['Perkedel Jagung','Perkedel Jagung Bakwan Crispy','Renyah manis khas Tomohon (2 pcs)',8000],
                ['Tumis Kangkung','Tumis Kangkung Bunga Pepaya','Gurih segar tanpa rasa pahit',8000],
                ['Dabu-Dabu','Ekstra Sambal Dabu-Dabu','Satu porsi mangkuk kecil sambal segar',5000]] as [$val,$label,$sub,$p]): ?>
      <label class="op"><input type="checkbox" value="<?= e($val) ?>" data-p="<?= $p ?>"><span class="rdo sqr"></span><span class="tx"><b><?= e($label) ?></b><small><?= e($sub) ?></small></span><span class="pp">+<?= rp($p) ?></span></label>
<?php endforeach; ?>
    </div>
<?php endif; ?>
    <h4>📝 Catatan untuk Dapur</h4>
    <div class="nt"><textarea id="note" rows="3" maxlength="120" placeholder="Contoh: Jeruk peras dipisah, kurangi minyak, sambal banyak tomat..."></textarea><small id="cnt">Maks. 120 huruf</small></div>
  </div>
  <div class="buy">
    <div class="qty"><button id="minus" aria-label="Kurangi">−</button><b id="qty">1</b><button id="plus" aria-label="Tambah">+</button></div>
    <button class="red" id="addCart"></button>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
