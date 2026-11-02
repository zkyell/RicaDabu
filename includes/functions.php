<?php
require_once __DIR__ . '/../core/supabase.php';

const MENU_FIELDS = 'id,name,price,category,description,image,emoji,is_hot';

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n): string { return 'Rp ' . number_format((int)$n, 0, ',', '.'); }

function get_menu(): ?array {
    $r = sb('GET', 'menu_items?select=' . MENU_FIELDS . '&is_available=eq.true&order=sort.asc');
    return ($r['code'] === 200 && is_array($r['data'])) ? $r['data'] : null;
}
function get_item(int $id): ?array {
    $r = sb('GET', 'menu_items?select=' . MENU_FIELDS . '&is_available=eq.true&id=eq.' . $id . '&limit=1');
    return ($r['code'] === 200 && !empty($r['data'][0])) ? $r['data'][0] : null;
}
function photo(array $m): string {
    return '<div class="ph"><img src="assets/images/' . e($m['image']) . '.jpg" alt="' . e($m['name'])
         . '" loading="lazy" onerror="this.remove()"><span>' . e($m['emoji'] ?: '🍽️') . '</span></div>';
}
// Tambahan tampilan dari desain Stitch. Kosongkan array ini kalau tidak mau menampilkan
// harga coret, rating, dan estimasi waktu.
function extra(string $name): array {
    static $x = [
        'Nasi Ikan Bakar Dabu-Dabu' => ['top' => 1, 'fav' => 1, 'old' => 32000, 'rate' => '4.9 (1.2k ulasan)', 'time' => '15–20 Menit',
            'desc' => 'Ikan laut bakar segar dengan bumbu rempah Manado, disajikan dengan seporsi nasi pulen hangat, lalapan timun segar, dan siraman sambal dabu-dabu iris (tomat merah & hijau, cabai rawit, bawang merah, jeruk lemon cui asli).'],
        'Rahang Tuna Dabu-Dabu' => ['tag' => 'FAVORIT'],
    ];
    return $x[$name] ?? [];
}
