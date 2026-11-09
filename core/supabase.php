<?php
require_once __DIR__ . '/../config/config.php';

function sb_headers(): array {
    $h = ['apikey: ' . SUPABASE_KEY];
    // Key lama (JWT) juga dikirim sebagai Bearer; key baru (sb_...) cukup lewat apikey.
    if (!str_starts_with(SUPABASE_KEY, 'sb_')) {
        $h[] = 'Authorization: Bearer ' . SUPABASE_KEY;
    }
    return $h;
}

// Pemanggil REST API Supabase (PostgREST) lewat cURL.
function sb(string $method, string $path, ?array $body = null, array $extra = []): array {
    $ch = curl_init(SUPABASE_URL . '/rest/v1/' . $path);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => array_merge(sb_headers(), ['Content-Type: application/json'], $extra),
        CURLOPT_TIMEOUT        => 15,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return ['code' => $code, 'data' => json_decode($res ?: 'null', true)];
}

// ---------- Supabase Storage (untuk foto bukti pembayaran) ----------
const BUKTI_BUCKET = 'bukti-bayar';

function sb_upload(string $path, string $bytes, string $mime): bool {
    $ch = curl_init(SUPABASE_URL . '/storage/v1/object/' . BUKTI_BUCKET . '/' . $path);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => array_merge(sb_headers(), ['Content-Type: ' . $mime, 'x-upsert: true']),
        CURLOPT_POSTFIELDS     => $bytes,
        CURLOPT_TIMEOUT        => 30,
    ]);
    curl_exec($ch);
    return curl_getinfo($ch, CURLINFO_HTTP_CODE) < 300;
}

function sb_download(string $path): ?array {
    $ch = curl_init(SUPABASE_URL . '/storage/v1/object/' . BUKTI_BUCKET . '/' . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => sb_headers(),
        CURLOPT_TIMEOUT        => 30,
    ]);
    $bytes = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200 || $bytes === false) return null;
    return ['bytes' => $bytes, 'mime' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'image/jpeg'];
}
