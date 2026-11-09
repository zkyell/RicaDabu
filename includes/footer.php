</div>
<?php if (empty($wide) && empty($bare) && !empty($siteFooter)): $s = store(); ?>
<footer class="sfoot">
  <div class="wrap sfoot-in">
    <div class="sf-brand"><img src="assets/images/logoricadabu.png" alt=""><div><b>Rica <i>Dabu</i></b><p>Made with Love, Served with Proud.</p></div></div>
    <ul>
      <li><?= icon('pin', 18) ?><?= e($s['alamat']) ?></li>
      <li><?= icon('clock', 18) ?><?= e($s['jam_buka']) ?></li>
      <li><?= icon('chat', 18) ?><a href="<?= e(wa_link('Halo Rica Dabu, saya mau tanya.')) ?>" target="_blank" rel="noopener">WhatsApp kasir</a></li>
    </ul>
    <small>© <?= date('Y') ?> Rica Dabu · Rumah Makan Manado</small>
  </div>
</footer>
<?php endif; ?>
<?php foreach (($js ?? []) as $j): ?><script src="<?= $root ?? '' ?>assets/js/<?= e($j) ?>.js"></script>
<?php endforeach; ?></body>
</html>
