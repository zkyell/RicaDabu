<div class="cbar" id="cbar">
  <span class="bag"><?= icon('bag', 20) ?><em id="bq">0</em></span>
  <div><b id="ct"></b><small id="cs"></small></div>
  <button id="openCart">Lihat Keranjang</button>
</div>
<div class="ov" id="ov">
  <div class="sheet" role="dialog" aria-modal="true" aria-label="Keranjang">
    <div class="shh"><h3>Keranjang Kamu</h3><button class="ib" id="cc" aria-label="Tutup"><?= icon('x', 20) ?></button></div>
    <div id="cl"></div>
    <div class="tot"><span>Subtotal</span><b id="tt"></b></div>
    <a class="red full btn" href="checkout.php" id="toCheckout">Lanjut ke Pembayaran <?= icon('arrow', 18) ?></a>
  </div>
</div>
<div id="toast" role="status"></div>
