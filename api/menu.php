<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../core/supabase.php';

$r = sb('GET', 'menu_items?select=id,name,price,category,description,image,emoji,is_hot'
             . '&is_available=eq.true&order=sort.asc');
if ($r['code'] !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'Gagal mengambil menu', 'debug' => $r]);
    exit;
}
echo json_encode($r['data']);
