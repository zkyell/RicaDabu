<?php
// ============================================================
//  PENGATURAN TOKO & METODE PEMBAYARAN
//  Ubah nilai di bawah sesuai data asli Rica Dabu.
//  (Nomor rekening memang tampil ke pelanggan, jadi aman di file ini.)
// ============================================================
return [
    'nama'      => 'Rica Dabu',
    'alamat'    => 'Bumi Beringin, Jln. 45, Manado',
    'jam_buka'  => 'Setiap hari, 09.00 – 21.00 WITA',
    // Nomor WhatsApp kasir, format internasional tanpa + (contoh 6281234567890)
    'whatsapp'  => '6281234567890',

    // Ongkos kirim untuk pesanan "Diantar" (rupiah). 0 = gratis ongkir.
    'ongkir'          => 5000,
    'gratis_ongkir_min' => 100000,   // gratis ongkir kalau subtotal ≥ ini (0 = tidak ada promo)

    // ---------- Metode pembayaran ----------
    // Set 'aktif' => false untuk menyembunyikan metode tertentu.
    'tunai' => [
        'aktif' => true,
    ],
    'qris' => [
        'aktif'  => true,
        // Kode QR saja (dipotong dari poster) – tampil besar di checkout & halaman status pesanan
        'gambar' => 'assets/images/qris-kode.png',
        // Poster QRIS lengkap dari GoPay Merchant – untuk tombol "Simpan gambar QRIS"
        'poster' => 'assets/images/qris.jpg',
        'nama_merchant' => 'VANIA EWVANGELINA ABIGAIL',
        'nmid'     => 'ID1026631209248',
        'penyedia' => 'GoPay Merchant',
    ],
    // Transfer & e-wallet dimatikan karena nomornya masih contoh (0000…).
    // Isi nomor asli dulu, baru ubah 'aktif' => true.
    'transfer' => [
        'aktif' => false,
        'rekening' => [
            ['bank' => 'BRI',     'nomor' => '0000-01-000000-50-0', 'atas_nama' => 'Rica Dabu'],
            ['bank' => 'BNI',     'nomor' => '0000000000',          'atas_nama' => 'Rica Dabu'],
            ['bank' => 'SulutGo', 'nomor' => '000.00.00.00000-0',   'atas_nama' => 'Rica Dabu'],
        ],
    ],
    'ewallet' => [
        'aktif' => false,
        'akun' => [
            ['nama' => 'DANA',   'nomor' => '081234567890', 'atas_nama' => 'Rica Dabu'],
            ['nama' => 'GoPay',  'nomor' => '081234567890', 'atas_nama' => 'Rica Dabu'],
            ['nama' => 'OVO',    'nomor' => '081234567890', 'atas_nama' => 'Rica Dabu'],
        ],
    ],
    // Midtrans aktif otomatis kalau MIDTRANS_SERVER_KEY & MIDTRANS_CLIENT_KEY diisi di .env
];
