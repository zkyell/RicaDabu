</div>
<?php foreach (($js ?? []) as $j): ?><script src="<?= $root ?? '' ?>assets/js/<?= e($j) ?>.js"></script>
<?php endforeach; ?></body>
</html>
