<?php
require __DIR__ . '/../includes/functions.php';
$items = get_menu();
if ($items === null) json_out(['error' => 'Gagal mengambil menu'], 502);
json_out($items);
