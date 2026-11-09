<?php
// Status pesanan + instruksi pembayaran. pesanan.php?kode=RD-XXXXXX
require __DIR__ . '/includes/functions.php';
$s = store();
$code = strtoupper(trim((string)($_GET['kode'] ?? '')));
$o = get_order($code);
$title = ($o ? 'Pesanan ' . $o['code'] : 'Pesanan tidak ditemukan') . ' – Rica Dabu';
$css = ['page'];
$js  = ['common', 'pesanan'];
$navOn = 'orders';
include __DIR__ . '/includes/header.php';

if (!$o): ?>
<main class="pg">
  <header class="top"><a class="bk" href="riwayat.php" aria-label="Kembali">←</a><div class="br"><b>Status Pesanan</b></div><span style="width:38px"></span></header>
  <section class="blk empty-st"><p class="big">🔎</p><h2>Pesanan tidak ditemukan</h2>
    <p>Periksa lagi kode pesananmu (contoh: RD-7KQ2MX).</p><a class="red btn" href="riwayat.php">Lihat pesanan saya</a></section>
</main>
<?php include __DIR__ . '/includes/nav.php'; include __DIR__ . '/includes/footer.php'; exit; endif;

$pm   = $o['payment_method'];
$ps   = $o['payment_status'];
$st   = $o['status'];
$paid = $ps === 'lunas';
$steps = $o['order_type'] === 'delivery' ? ['baru', 'diproses', 'diantar', 'selesai'] : ['baru', 'diproses', 'siap', 'selesai'];
$cur  = array_search($st, $steps, true);
$methodLabel = payment_methods()[$pm][0] ?? ['tunai' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer Bank', 'ewallet' => 'E-Wallet', 'midtrans' => 'Bayar Online'][$pm];
$canUpload = needs_proof($pm) && in_array($ps, ['belum_bayar', 'ditolak', 'menunggu_verifikasi'], true) && $st !== 'batal';
$waText = "Halo Rica Dabu, saya {$o['customer_name']} dengan pesanan {$o['code']} (total " . rp($o['total']) . ").";
?>
<main class="pg" id="order" data-code="<?= e($o['code']) ?>" data-sig="<?= e($st . '|' . $ps) ?>">
  <header class="top">
    <a class="bk" href="riwayat.php" aria-label="Kembali">←</a>
    <div class="br"><b>Status Pesanan</b></div>
    <a class="ib sm" href="<?= e(wa_link($waText)) ?>" target="_blank" rel="noopener" aria-label="Hubungi kasir via WhatsApp">💬</a>
  </header>

<?php if (isset($_GET['baru'])): ?>
  <div class="ok-banner" role="status"><b>Pesanan berhasil dibuat!</b> Simpan kode ini untuk melacak pesananmu.</div>
<?php endif; ?>

  <section class="blk code-blk">
    <small>Kode pesanan</small>
    <div class="code"><b><?= e($o['code']) ?></b><button class="cp" data-copy="<?= e($o['code']) ?>">Salin</button></div>
    <small><?= e(tgl($o['created_at'])) ?> · <?= e(ORDER_TYPES[$o['order_type']][0] ?? '') ?></small>
  </section>

  <section class="blk">
<?php if ($st === 'batal'): ?>
    <div class="st-cancel"><b>Pesanan dibatalkan</b><p>Hubungi kasir lewat WhatsApp kalau ada pertanyaan.</p></div>
<?php else: ?>
    <h2><?= e(ORDER_STATUS[$st][0]) ?></h2><p class="sub"><?= e(ORDER_STATUS[$st][1]) ?></p>
    <ol class="steps">
<?php foreach ($steps as $i => $k): ?>
      <li class="<?= $i < $cur ? 'done' : ($i === $cur ? 'now' : '') ?>"><i></i><span><?= e(ORDER_STATUS[$k][0]) ?></span></li>
<?php endforeach; ?>
    </ol>
<?php endif; ?>
  </section>

  <section class="blk pay">
    <div class="bh"><h2>Pembayaran</h2><span class="badge b-<?= e($ps) ?>"><?= e(PAY_STATUS[$ps] ?? $ps) ?></span></div>
    <div class="amt"><small><?= e($methodLabel) ?></small><b><?= rp($o['total']) ?></b>
      <?php if (!$paid && $pm !== 'tunai' && $pm !== 'midtrans'): ?><button class="cp" data-copy="<?= (int)$o['total'] ?>">Salin nominal</button><?php endif; ?></div>

<?php if ($paid): ?>
    <p class="ok-txt">✓ Pembayaran sudah diterima<?= $o['paid_at'] ? ' pada ' . e(tgl($o['paid_at'])) : '' ?>. Terima kasih!</p>

<?php elseif ($st === 'batal'): ?>
    <p class="hint">Pesanan dibatalkan, tidak perlu melakukan pembayaran.</p>

<?php elseif ($pm === 'tunai'): ?>
    <p class="hint"><?= $o['order_type'] === 'delivery'
        ? 'Bayar tunai ke kurir saat pesanan sampai. Siapkan uang pas, ya.'
        : 'Bayar tunai di kasir dengan menyebutkan kode pesanan di atas.' ?></p>

<?php elseif ($pm === 'midtrans'): ?>
    <?php if ($ps === 'gagal'): ?><p class="warn">Pembayaran sebelumnya gagal atau kedaluwarsa. Silakan coba lagi.</p><?php endif; ?>
    <p class="hint">Bayar lewat QRIS, Virtual Account bank, e-wallet, atau kartu. Status otomatis berubah jadi Lunas setelah pembayaran berhasil.</p>
    <button class="red full" id="payOnline" data-snap="<?= e(midtrans_snap_js()) ?>" data-client="<?= e(MIDTRANS_CLIENT_KEY) ?>">Bayar Sekarang</button>

<?php else: ?>
    <?php if ($ps === 'ditolak'): ?><p class="warn">Bukti pembayaran sebelumnya ditolak kasir. Pastikan nominal sesuai, lalu unggah ulang bukti yang benar.</p><?php endif; ?>
    <?php if ($ps === 'menunggu_verifikasi'): ?><p class="info">Bukti pembayaran sudah terkirim. Kasir sedang memeriksanya — halaman ini akan berubah otomatis.</p><?php endif; ?>

    <?php if ($ps !== 'menunggu_verifikasi'): ?>
      <?php if ($pm === 'qris'): ?>
    <div class="qris"><img src="<?= e($s['qris']['gambar']) ?>" alt="Kode QRIS <?= e($s['qris']['nama_merchant']) ?>">
      <b><?= e($s['qris']['nama_merchant']) ?></b><small>Scan dengan aplikasi bank atau e-wallet apa pun, lalu masukkan nominal <?= rp($o['total']) ?>.</small>
      <a class="lnk" href="<?= e($s['qris']['gambar']) ?>" download="QRIS-RicaDabu">Simpan gambar QRIS</a></div>
      <?php else: foreach (($pm === 'transfer' ? $s['transfer']['rekening'] : $s['ewallet']['akun']) as $a): ?>
    <div class="acc"><span class="ab"><?= e($a['bank'] ?? $a['nama']) ?></span>
      <div><b><?= e($a['nomor']) ?></b><small>a.n. <?= e($a['atas_nama']) ?></small></div>
      <button class="cp" data-copy="<?= e(preg_replace('/\D/', '', $a['nomor'])) ?>">Salin</button></div>
      <?php endforeach; endif; ?>
    <p class="hint"><?= $pm === 'transfer' ? 'Transfer' : 'Bayar' ?> <b>tepat <?= rp($o['total']) ?></b>, lalu unggah foto/screenshot bukti pembayaran di bawah.</p>
    <?php endif; ?>

    <?php if ($canUpload): ?>
    <form id="upForm" class="up" enctype="multipart/form-data">
      <input type="hidden" name="kode" value="<?= e($o['code']) ?>">
      <label class="drop" for="bukti"><span id="upTxt"><?= $ps === 'menunggu_verifikasi' ? '📎 Ganti foto bukti pembayaran' : '📎 Pilih foto bukti pembayaran' ?></span>
        <small>JPG, PNG, atau WEBP · maks. 3 MB</small><img id="upPrev" alt="" hidden></label>
      <input type="file" id="bukti" name="bukti" accept="image/jpeg,image/png,image/webp" hidden>
      <button class="red full" id="upBtn" disabled>Kirim Bukti Pembayaran</button>
      <p id="upMsg" role="status"></p>
    </form>
    <?php endif; ?>
<?php endif; ?>
  </section>

  <section class="blk">
    <h2>Detail pesanan</h2>
    <dl class="meta">
      <dt>Nama</dt><dd><?= e($o['customer_name']) ?> · <?= e($o['phone']) ?></dd>
<?php if ($o['table_no']): ?><dt>Nomor meja</dt><dd><?= e($o['table_no']) ?></dd><?php endif; ?>
<?php if ($o['address']): ?><dt>Alamat</dt><dd><?= e($o['address']) ?></dd><?php endif; ?>
<?php if ($o['note']): ?><dt>Catatan</dt><dd><?= e($o['note']) ?></dd><?php endif; ?>
    </dl>
<?php foreach ($o['order_items'] as $it): ?>
    <div class="li"><span class="q"><?= (int)$it['qty'] ?>×</span>
      <div><b><?= e($it['name']) ?></b><small><?= e(implode(' • ', array_filter([$it['spice'], $it['addons'] ? '+ ' . $it['addons'] : null, $it['note']]))) ?></small></div>
      <b><?= rp($it['unit_price'] * $it['qty']) ?></b></div>
<?php endforeach; ?>
    <div class="sum">
      <div><span>Subtotal</span><b><?= rp($o['subtotal'] ?: $o['total']) ?></b></div>
<?php if ($o['order_type'] === 'delivery'): ?><div><span>Ongkos kirim</span><b><?= $o['delivery_fee'] ? rp($o['delivery_fee']) : 'Gratis' ?></b></div><?php endif; ?>
      <div class="gt"><span>Total</span><b><?= rp($o['total']) ?></b></div>
    </div>
  </section>

  <a class="wa" href="<?= e(wa_link($waText)) ?>" target="_blank" rel="noopener">💬 Hubungi kasir lewat WhatsApp</a>
</main>
<div id="toast" role="status"></div>
<?php include __DIR__ . '/includes/nav.php'; include __DIR__ . '/includes/footer.php'; ?>
