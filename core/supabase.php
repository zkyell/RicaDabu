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

// Jalankan request cURL. Beberapa provider internet di Indonesia memblokir DNS *.supabase.co
// ("Could not resolve host"). Kalau itu terjadi, ulangi sekali dengan DNS-over-HTTPS Cloudflare
// supaya tetap tersambung. Di hosting (Vercel) biasanya tidak perlu, jadi tidak berpengaruh.
function sb_exec($ch) {
    $res = curl_exec($ch);
    if ($res === false && curl_errno($ch) === CURLE_COULDNT_RESOLVE_HOST && defined('CURLOPT_DOH_URL')) {
        curl_setopt($ch, CURLOPT_DOH_URL, 'https://1.1.1.1/dns-query');
        $res = curl_exec($ch);
    }
    return $res;
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
    $res  = sb_exec($ch);
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
    sb_exec($ch);
    return curl_getinfo($ch, CURLINFO_HTTP_CODE) < 300;
}

function sb_download(string $path): ?array {
    $ch = curl_init(SUPABASE_URL . '/storage/v1/object/' . BUKTI_BUCKET . '/' . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => sb_headers(),
        CURLOPT_TIMEOUT        => 30,
    ]);
    $bytes = sb_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200 || $bytes === false) return null;
    return ['bytes' => $bytes, 'mime' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'image/jpeg'];
}
