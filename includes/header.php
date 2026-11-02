<?php require_once __DIR__ . '/functions.php'; $css = $css ?? []; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title ?? 'Rica Dabu – Rumah Makan Manado') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/base.css">
<?php foreach ($css as $c): ?><link rel="stylesheet" href="assets/css/<?= e($c) ?>.css">
<?php endforeach; ?></head>
<body>
<div id="app">
