<?php
require __DIR__ . '/../config/config.php';

// Pemanggil REST API Supabase (PostgREST) lewat cURL.
function sb(string $method, string $path, ?array $body = null, array $extra = []): array {
    $headers = ['apikey: ' . SUPABASE_KEY, 'Content-Type: application/json'];
    // Key lama (JWT) juga dikirim sebagai Bearer; key baru (sb_...) cukup lewat apikey.
    if (!str_starts_with(SUPABASE_KEY, 'sb_')) {
        $headers[] = 'Authorization: Bearer ' . SUPABASE_KEY;
    }
    $ch = curl_init(SUPABASE_URL . '/rest/v1/' . $path);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => array_merge($headers, $extra),
        CURLOPT_TIMEOUT        => 15,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return ['code' => $code, 'data' => json_decode($res ?: 'null', true)];
}
