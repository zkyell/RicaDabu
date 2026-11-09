# Rica Dabu – Web Pemesanan Makanan & Minuman

PHP + Supabase. Tampilan mobile untuk pelanggan, panel kasir untuk admin.

## Alur pelanggan
Splash → Menu → Detail menu → Keranjang → **Checkout** (makan di tempat / ambil sendiri / diantar
+ pilih metode bayar) → **Status pesanan** (instruksi bayar, upload bukti, status real-time) →
**Riwayat** & **Profil**.

## Metode pembayaran
| Metode | Cara kerja | Konfirmasi |
|---|---|---|
| Tunai | Bayar di kasir / ke kurir (COD) | Kasir klik "Tandai lunas" |
| QRIS | QRIS statis toko (`assets/images/qris.png`) | Pelanggan upload bukti → kasir verifikasi |
| Transfer Bank | Daftar rekening di `config/toko.php` | Pelanggan upload bukti → kasir verifikasi |
| E-Wallet | DANA/GoPay/OVO di `config/toko.php` | Pelanggan upload bukti → kasir verifikasi |
| Bayar Online (opsional) | Midtrans Snap: QRIS dinamis, VA, e-wallet, kartu | Otomatis lewat webhook |

## Cara memasang
1. **Database** – Supabase > SQL Editor:
   - Database sudah ada (versi lama) → jalankan `database/upgrade-pembayaran.sql`
   - Instal baru → jalankan `database/schema.sql`
2. **Konfigurasi** – salin `config/.env.example` → `config/.env`, isi `SUPABASE_URL`, `SUPABASE_KEY`,
   dan **`ADMIN_PASSWORD`**. Di Vercel isi di *Environment Variables*.
3. **Data toko & rekening** – edit `config/toko.php` (nomor rekening, e-wallet, WhatsApp, ongkir, jam buka).
4. **QRIS** – simpan gambar QRIS asli toko sebagai `assets/images/qris.png`
   (selama file belum ada, opsi QRIS otomatis disembunyikan).
5. **Midtrans (opsional)** – isi `MIDTRANS_SERVER_KEY` & `MIDTRANS_CLIENT_KEY` (Sandbox dulu untuk uji coba).
   Di dashboard Midtrans set *Payment Notification URL* ke `https://DOMAIN-KAMU/api/midtrans-notify.php`.
6. Jalankan lokal: `php -S localhost:8000` lalu buka http://localhost:8000
   Panel kasir: http://localhost:8000/admin/

## Struktur baru
```
checkout.php   pesanan.php   riwayat.php   profil.php
admin/         index.php (pesanan) · menu.php (stok & harga) · aksi.php · bukti.php · logout.php
api/           order.php · status.php · upload-bukti.php · midtrans-token.php · midtrans-notify.php
config/        toko.php (data toko & pembayaran) · .env.example
core/          midtrans.php · supabase.php (+ Storage)
includes/      auth.php (login admin)
```

## Hosting di Vercel
Sudah disiapkan: `vercel.json` (runtime vercel-php, region Singapura), `api/index.php` (router),
`.vercelignore`.
1. Push ke GitHub → vercel.com → **Add New → Project** → import repo → **Deploy**.
2. **Settings → Environment Variables**: isi `SUPABASE_URL`, `SUPABASE_KEY`, `ADMIN_PASSWORD`
   (opsional `APP_SECRET`, `MIDTRANS_*`), lalu **Redeploy**.
3. Buka `https://NAMA-PROJECT.vercel.app` (panel kasir: `/admin/`).
Uji lokal seperti di Vercel: `php -S localhost:8000 api/index.php`
