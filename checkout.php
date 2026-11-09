<?php
// Checkout: isi keranjang, cara pesan, data pemesan, dan metode pembayaran
require __DIR__ . '/includes/functions.php';
$s = store();
$methods = payment_methods();
$title = 'Checkout – Rica Dabu';
$css = ['page'];
$js  = ['common', 'checkout'];
$navOn = 'cart';
include __DIR__ . '/includes/header.php';
?>
<main id="checkout" class="pg" data-fee="<?= (int)$s['ongkir'] ?>" data-free="<?= (int)($s['gratis_ongkir_min'] ?? 0) ?>">
  <header class="top">
    <a class="bk" href="menu.php" aria-label="Kembali ke menu"><?= icon('back') ?></a>
    <div class="br"><b>Keranjang &amp; Pembayaran</b></div>
    <span class="m-only" style="width:44px"></span>
  </header>

  <div class="cols">
    <div class="col-main">
      <section class="blk">
        <div class="bh"><h2>Pesananmu</h2><a href="menu.php" class="lnk">+ Tambah menu</a></div>
        <div id="items"></div>
      </section>

      <form id="form" novalidate>
        <section class="blk">
          <h2><span class="step">1</span>Cara pesan</h2>
          <div class="seg" role="radiogroup" aria-label="Cara pesan">
<?php $first = true; foreach (ORDER_TYPES as $k => [$label, $ico]): ?>
            <label><input type="radio" name="order_type" value="<?= $k ?>"<?= $first ? ' checked' : '' ?>><span><i><?= $ico ?></i><?= e($label) ?></span></label>
<?php $first = false; endforeach; ?>
          </div>
          <div data-for="dine_in"><label class="lb" for="table_no">Nomor meja</label>
            <input class="f" id="table_no" name="table_no" inputmode="numeric" maxlength="10" placeholder="Contoh: 7"></div>
          <div data-for="delivery" hidden><label class="lb" for="address">Alamat pengantaran</label>
            <textarea class="f" id="address" name="address" rows="3" maxlength="250" placeholder="Nama jalan, nomor rumah, patokan (contoh: depan masjid, pagar hijau)"></textarea>
            <p class="hint">Ongkir <?= rp($s['ongkir']) ?><?php if (!empty($s['gratis_ongkir_min'])): ?>, gratis untuk belanja minimal <?= rp($s['gratis_ongkir_min']) ?><?php endif; ?>.</p></div>
          <div data-for="takeaway" hidden><p class="hint">Ambil pesananmu di <?= e($s['alamat']) ?>. Kami kabari lewat halaman status saat pesanan siap.</p></div>
        </section>

        <section class="blk">
          <h2><span class="step">2</span>Data pemesan</h2>
          <div class="two">
            <div><label class="lb" for="name">Nama</label>
              <input class="f" id="name" name="name" maxlength="80" autocomplete="name" placeholder="Nama kamu"></div>
            <div><label class="lb" for="phone">Nomor HP / WhatsApp</label>
              <input class="f" id="phone" name="phone" inputmode="tel" maxlength="16" autocomplete="tel" placeholder="08xxxxxxxxxx"></div>
          </div>
          <label class="lb" for="note">Catatan pesanan <em>(opsional)</em></label>
          <input class="f" id="note" name="note" maxlength="200" placeholder="Contoh: sendok garpu dipisah">
        </section>

        <section class="blk">
          <h2><span class="step">3</span>Metode pembayaran</h2>
<?php if (!$methods): ?>
          <p class="hint">Belum ada metode pembayaran yang aktif. Atur di config/toko.php.</p>
<?php endif; $first = true; foreach ($methods as $k => [$label, $ico, $sub]): ?>
          <label class="pm"><input type="radio" name="payment_method" value="<?= $k ?>"<?= $first ? ' checked' : '' ?>>
            <span class="pi<?= $k === 'qris' ? ' pi-qris' : '' ?>"><?= $k === 'qris' ? 'QRIS' : $ico ?></span><span class="tx"><b><?= e($label) ?><?php if ($k === 'qris'): ?> <em class="rec">Rekomendasi</em><?php endif; ?></b><small><?= e($sub) ?></small></span><span class="rd"></span></label>
<?php if ($k === 'qris'): $q = $s['qris']; ?>
          <div class="qcard" data-pay="qris">
            <img src="<?= e($q['gambar']) ?>" alt="Kode QRIS <?= e($q['nama_merchant']) ?>" width="160" height="160">
            <div>
              <span class="qbadge">QRIS · <?= e($q['penyedia'] ?? '') ?></span>
              <b><?= e($q['nama_merchant']) ?></b>
              <small>NMID: <?= e($q['nmid'] ?? '-') ?></small>
              <p>Setelah klik <b>Buat Pesanan</b>, scan QR ini dengan GoPay atau m-banking (BCA, Mandiri, BNI, BRI, dll), bayar <b>sesuai total</b>, lalu unggah bukti bayarnya.</p>
            </div>
          </div>
<?php endif; $first = false; endforeach; ?>
          <p class="hint" id="cashHint" hidden>Bayar langsung saat pesanan diantar. Siapkan uang pas, ya.</p>
        </section>
        <p id="msg" role="alert"></p>
      </form>
    </div>

    <aside class="col-side" id="side">
      <section class="blk sum">
        <h2>Ringkasan</h2>
        <div><span>Subtotal</span><b id="sSub">Rp 0</b></div>
        <div id="rowFee" hidden><span>Ongkos kirim</span><b id="sFee">Rp 0</b></div>
        <div class="gt"><span>Total bayar</span><b id="sTot">Rp 0</b></div>
      </section>
      <div class="bar">
        <div><small>Total</small><b id="bTot">Rp 0</b></div>
        <button class="red" id="submit" type="submit" form="form">Buat Pesanan</button>
      </div>
    </aside>
  </div>
</main>
<div id="toast" role="status"></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
