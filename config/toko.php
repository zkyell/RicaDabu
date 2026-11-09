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
        // Taruh gambar QRIS statis toko di assets/images/qris.png
        // Selama file itu belum ada, opsi QRIS otomatis disembunyikan.
        'gambar' => 'assets/images/qris.png',
        'nama_merchant' => 'RICA DABU MANADO',
    ],
    'transfer' => [
        'aktif' => true,
        'rekening' => [
            ['bank' => 'BRI',     'nomor' => '0000-01-000000-50-0', 'atas_nama' => 'Rica Dabu'],
            ['bank' => 'BNI',     'nomor' => '0000000000',          'atas_nama' => 'Rica Dabu'],
            ['bank' => 'SulutGo', 'nomor' => '000.00.00.00000-0',   'atas_nama' => 'Rica Dabu'],
        ],
    ],
    'ewallet' => [
        'aktif' => true,
        'akun' => [
            ['nama' => 'DANA',   'nomor' => '081234567890', 'atas_nama' => 'Rica Dabu'],
            ['nama' => 'GoPay',  'nomor' => '081234567890', 'atas_nama' => 'Rica Dabu'],
            ['nama' => 'OVO',    'nomor' => '081234567890', 'atas_nama' => 'Rica Dabu'],
        ],
    ],
    // Midtrans aktif otomatis kalau MIDTRANS_SERVER_KEY & MIDTRANS_CLIENT_KEY diisi di .env
];
