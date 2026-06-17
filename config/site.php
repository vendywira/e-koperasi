<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand & Identity
    |--------------------------------------------------------------------------
    */
    'brand' => [
        'name' => 'e-Koperasi',
        'tagline' => 'Platform Digital Koperasi Indonesia',
        'description' => 'Platform monitoring & operasional koperasi — pinjaman, kasbon, absensi, dan slip gaji dalam satu aplikasi internal.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Site Header / Footer
    |--------------------------------------------------------------------------
    */
    'nav' => [
        'items' => [
            ['label' => 'Produk',      'href' => '/#produk'],
            ['label' => 'Aplikasi',    'href' => '/#aplikasi'],
            ['label' => 'Fitur',       'href' => '/#fitur'],
            ['label' => 'Harga',       'href' => '/#harga'],
            ['label' => 'Testimoni',   'href' => '/#testimoni'],
            ['label' => 'Bantuan',     'href' => '/#faq'],
        ],
    ],

    'footer' => [
        'description' => 'Platform monitoring & operasional koperasi — pinjaman, kasbon, absensi, slip gaji, dan evaluasi kinerja.',
        'tagline' => 'Untuk Koperasi Indonesia',
        'columns' => [
            [
                'title' => 'Produk',
                'links' => [
                    ['label' => 'Produk Keuangan', 'href' => '/#produk'],
                    ['label' => 'Aplikasi Mobile', 'href' => '/#aplikasi'],
                    ['label' => 'Fitur',     'href' => '/#fitur'],
                    ['label' => 'Harga',     'href' => '/#harga'],
                    ['label' => 'Demo',      'href' => '/demo'],
                ],
            ],
            [
                'title' => 'Dukungan',
                'links' => [
                    ['label' => 'FAQ',         'href' => '/#faq'],
                    ['label' => 'Konsultasi',  'href' => '/demo#konsultasi'],
                    ['label' => 'Email Kami',  'href' => 'mailto:halo@e-koperasi.com'],
                    ['label' => 'WhatsApp',    'href' => 'https://wa.me/6281234567890'],
                ],
            ],
            [
                'title' => 'Hukum',
                'links' => [
                    ['label' => 'Privasi',         'href' => '/legal/privasi'],
                    ['label' => 'Syarat & Ketentuan', 'href' => '/legal/syarat'],
                    ['label' => 'Kepatuhan UU PDP', 'href' => '/legal/pdp'],
                ],
            ],
        ],
        'copyright' => '© 2026 e-Koperasi. Dibuat dengan ❤️ di Bali.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hero
    |--------------------------------------------------------------------------
    */
    'hero' => [
        'badge' => 'Dipercaya 50+ Koperasi · 10K+ Anggota',
        'headline' => 'Operasional Koperasi Lebih Cerdas, Lebih Transparan',
        'subheadline' => 'Platform all-in-one untuk internal koperasi — pinjaman harian/mingguan/tempo, kasbon, absensi foto, slip gaji, dan evaluasi kinerja. Didukung AI risk scoring, rule engine dinamis, dan dashboard real-time.',
        'primary_cta' => [
            'label' => 'Coba Demo Gratis',
            'href' => '/demo',
        ],
        'secondary_cta' => [
            'label' => 'Lihat Produk',
            'href' => '/#produk',
        ],
        'floating_badge' => 'UU PDP Compliant',
        'carousel_slides' => [
            ['src' => '/images/app-screenshots/webp/homepage.webp',         'src_small' => '/images/app-screenshots/webp/homepage-sm.webp',         'alt' => 'e-Koperasi App - Dashboard utama'],
            ['src' => '/images/app-screenshots/webp/budget-dashboard.webp', 'src_small' => '/images/app-screenshots/webp/budget-dashboard-sm.webp', 'alt' => 'e-Koperasi App - Budget dashboard'],
            ['src' => '/images/app-screenshots/webp/chart.webp',            'src_small' => '/images/app-screenshots/webp/chart-sm.webp',            'alt' => 'e-Koperasi App - Grafik performa'],
            ['src' => '/images/app-screenshots/webp/transaction.webp',      'src_small' => '/images/app-screenshots/webp/transaction-sm.webp',      'alt' => 'e-Koperasi App - Transaksi'],
            ['src' => '/images/app-screenshots/webp/list-angsuran.webp',    'src_small' => '/images/app-screenshots/webp/list-angsuran-sm.webp',    'alt' => 'e-Koperasi App - Daftar angsuran'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Trust Bar
    |--------------------------------------------------------------------------
    */
    'trust_bar' => [
        'title' => 'Dipercaya oleh koperasi di berbagai sektor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Stats
    |--------------------------------------------------------------------------
    */
    'stats' => [
        ['value' => '50+',   'label' => 'Koperasi Aktif'],
        ['value' => '10K+',  'label' => 'Anggota Terdaftar'],
        ['value' => '40%',   'label' => 'Penurunan NPL'],
        ['value' => '24/7',  'label' => 'Support & Uptime'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Persona Cards
    |--------------------------------------------------------------------------
    */
    'personas' => [
        [
            'title' => 'Untuk Ketua & Admin',
            'subtitle' => 'Pemimpin koperasi',
            'description' => 'Pantau seluruh operasional dari dashboard terpusat. Lihat saldo, pinjaman, dan NPL dalam hitungan detik.',
            'icon' => 'briefcase',
        ],
        [
            'title' => 'Untuk Koordinator',
            'subtitle' => 'Pengawas unit',
            'description' => 'Koordinasi multi-unit dengan visibilitas real-time. Approval pinjaman jadi lebih cepat dan terukur.',
            'icon' => 'users',
        ],
        [
            'title' => 'Untuk PDL & Penagih',
            'subtitle' => 'Tim di lapangan',
            'description' => 'Absensi foto, GPS tracking, catat titipan harian, dan lihat jadwal tagihan — semua dari HP.',
            'icon' => 'smartphone',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Showcase (NEW)
    |--------------------------------------------------------------------------
    */
    'products' => [
        'youtube_url' => 'https://www.youtube.com/watch?v=your-video-id',
        'highlights' => [
            ['value' => 'Enkripsi AES-256', 'label' => 'Keamanan Data', 'icon' => 'shield'],
            ['value' => 'Real-time', 'label' => 'Update Otomatis', 'icon' => 'zap'],
            ['value' => '24/7', 'label' => 'Akses Kapan Saja', 'icon' => 'clock'],
            ['value' => 'AI-Powered', 'label' => 'Risk Scoring', 'icon' => 'sparkles'],
        ],        'saving' => [
            [
                'name' => 'Titipan Harian',
                'tagline' => 'Catatan titipan PDL & anggota real-time',
                'icon' => 'gift',
                'color' => 'amber',
                'screenshot' => '/images/app-screenshots/webp/list-angsuran.webp',
                'benefits' => [
                    'Input titipan harian dari PDL',
                    'Rekonsiliasi otomatis',
                    'Audit trail lengkap',
                    'Tracking saldo per anggota',
                ],
            ],
            [
                'name' => 'Kasbon & BON',
                'tagline' => 'Pinjaman internal dengan approval flow',
                'icon' => 'wallet',
                'color' => 'blue',
                'screenshot' => '/images/app-screenshots/webp/deposit.webp',
                'benefits' => [
                    'Ajukan kasbon dari app',
                    'Approval bertingkat',
                    'Potong otomatis dari gaji',
                    'Tracking sisa saldo BON',
                ],
            ],
            [
                'name' => 'Tabungan Gaji',
                'tagline' => 'Potongan gaji untuk tabungan anggota',
                'icon' => 'piggy-bank',
                'color' => 'purple',
                'screenshot' => '/images/app-screenshots/webp/deposit.webp',
                'benefits' => [
                    'Auto potong dari slip gaji',
                    'Credit & debit tercatat',
                    'Status pending & complete',
                    'Laporan tabungan real-time',
                ],
            ],
        ],
        'loan' => [
            [
                'name' => 'Pinjaman Harian',
                'description' => 'Pinjaman dengan cicilan harian untuk anggota yang membutuhkan dana cepat dengan tenor pendek.',
                'badge' => 'Harian',
                'icon' => 'calendar-days',
                'accent' => 'primary',
                'screenshot' => '/images/app-screenshots/webp/jatuh-tempo.webp',
                'rate' => '20%',
                'tenor' => '14–60 hari',
                'approval' => '1×24 jam',
                'benefits' => [
                    'Proses approval cepat',
                    'AI risk scoring via analisis nomor HP',
                    'Cicilan harian fleksibel',
                    'Collateral photo & video',
                ],
            ],
            [
                'name' => 'Pinjaman Mingguan',
                'description' => 'Cicilan mingguan yang cocok untuk anggota dengan penghasilan mingguan. Tenor lebih panjang.',
                'badge' => 'Mingguan',
                'icon' => 'calendar-clock',
                'accent' => 'amber',
                'screenshot' => '/images/app-screenshots/webp/list-angsuran.webp',
                'rate' => '20%',
                'tenor' => '4–24 minggu',
                'approval' => '1×24 jam',
                'benefits' => [
                    'Cicilan per minggu',
                    'Jadwal pembayaran fleksibel',
                    'GPS tracking & route optimization',
                    'Slip gaji terintegrasi',
                ],
            ],
            [
                'name' => 'Pinjaman Tempo',
                'description' => 'Pinjaman jangka panjang dengan pembayaran sekaligus saat jatuh tempo. Untuk kebutuhan besar.',
                'badge' => 'Tempo',
                'icon' => 'trending-down',
                'accent' => 'violet',
                'screenshot' => '/images/app-screenshots/webp/AI-recomendation-approve.webp',
                'rate' => '20%',
                'tenor' => '3–12 bulan',
                'approval' => '2×24 jam',
                'benefits' => [
                    'Tenor panjang',
                    'Auto-debet deposit saat jatuh tempo',
                    'Agunan/gadai tercatat',
                    'Monitoring status real-time',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Grid
    |--------------------------------------------------------------------------
    */
    'features' => [
        [
            'title' => 'Manajemen Tunai Harian',
            'description' => 'Catat kas masuk/keluar, rekonsiliasi real-time, laporan harian otomatis.',
            'icon' => 'wallet',
        ],
        [
            'title' => 'Manajemen Kasbon (BON)',
            'description' => 'Ajukan, approval, tracking saldo BON, potong otomatis dari gaji.',
            'icon' => 'trending-up',
        ],
        [
            'title' => 'Titipan & Tabungan Gaji',
            'description' => 'Catat titipan harian PDL, tabungan gaji dengan auto potong dari slip.',
            'icon' => 'badge-dollar-sign',
        ],
        [
            'title' => 'Kontrol Pinjaman + AI',
            'description' => 'Pinjaman harian/mingguan/tempo, AI risk scoring via nomor HP, GPS tracking.',
            'icon' => 'trending-up',
        ],
        [
            'title' => 'Rule Engine Dinamis',
            'description' => 'Atur logika bisnis tanpa coding. Insentif, pinjaman, approval bertingkat.',
            'icon' => 'cog',
        ],
        [
            'title' => 'Slip Gaji Digital',
            'description' => 'Cetak PDF, tunjangan & insentif otomatis, potong kasbon & tabungan.',
            'icon' => 'file-text',
        ],
        [
            'title' => 'Real-time Dashboard',
            'description' => 'Performance, saldo lapangan, NPL, target harian, nasabah aktif — semua real-time.',
            'icon' => 'bar-chart',
        ],
        [
            'title' => 'Multi-Resort & Multi-Unit',
            'description' => '6 role bertingkat (Admin sampai PDL), budgeting per unit, laporan lintas resort.',
            'icon' => 'building',
        ],
        [
            'title' => 'Absensi Foto + GPS',
            'description' => 'Upload foto absen, GPS tracking posisi PDL di lapangan, nearest customer.',
            'icon' => 'camera',
        ],
        [
            'title' => 'GPS Lokasi Nasabah',
            'description' => 'Simpan koordinat GPS rumah & tempat usaha nasabah. Lihat lokasi di peta saat penagihan.',
            'icon' => 'map-pin',
        ],
        [
            'title' => 'Rute Tercepat Penagihan',
            'description' => 'Optimasi rute kunjungan PDL berdasarkan jarak & jadwal jatuh tempo. Hemat waktu & BBM.',
            'icon' => 'route',
        ],
        [
            'title' => 'Evaluasi Kinerja PDL',
            'description' => 'Penilaian otomatis berdasarkan pencapaian target, tren performa, dan perbandingan antar PDL.',
            'icon' => 'bar-chart',
        ],
        [
            'title' => 'Laporan Investor',
            'description' => 'Laporan keuangan lengkap: kas, profit, kondisi pinjaman, dan budget untuk investor.',
            'icon' => 'file-text',
        ],
        [
            'title' => 'Closing Harian Otomatis',
            'description' => 'Operational close otomatis setiap akhir hari. Rekap transaksi harian tanpa effort.',
            'icon' => 'clock',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | How It Works
    |--------------------------------------------------------------------------
    */
    'how_it_works' => [
        'title' => 'Cara Kerja',
        'subtitle' => 'Dari setup hingga operasional, butuh kurang dari 14 hari.',
        'steps' => [
            [
                'step' => 1,
                'title' => 'Konsultasi & Setup',
                'description' => 'Tim kami membantu migrasi data dari Excel/ledger lama. Tidak ada setup fee.',
            ],
            [
                'step' => 2,
                'title' => 'Onboarding Anggota',
                'description' => 'Undang anggota via WhatsApp. Aktivasi mobile app iOS & Android.',
            ],
            [
                'step' => 3,
                'title' => 'Operasional Harian',
                'description' => 'Mulai catat pinjaman, kasbon, titipan, dan absensi. Dukungan WhatsApp 1x24 jam.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing
    |--------------------------------------------------------------------------
    */
    'pricing' => [
        'title' => 'Harga Sederhana & Transparan',
        'subtitle' => 'Bayar bulanan. Tanpa biaya setup tersembunyi. Batalkan kapan saja.',
        'tiers' => [
            'starter' => [
                'name' => 'Starter',
                'price' => 'Rp 499K',
                'period' => 'per bulan',
                'highlight' => false,
                'tagline' => 'Untuk koperasi kecil yang baru mulai digitalisasi',
                'features' => [
                    'Hingga 100 anggota',
                    '1 unit koperasi',
                    'Manajemen tunai, kasbon & titipan',
                    'Dashboard dasar',
                    'Absensi foto',
                    'Support email',
                ],
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 'Rp 1.5jt',
                'period' => 'per bulan',
                'highlight' => true,
                'tagline' => 'Untuk koperasi berkembang dengan multi-unit',
                'features' => [
                    'Hingga 1.000 anggota',
                    'Multi-unit (hingga 3 resort)',
                    'Semua fitur Starter',
                    'AI risk scoring (analisis nomor HP)',
                    'Rule engine dinamis',
                    'Slip gaji digital',
                    'GPS lokasi nasabah + rute penagihan',
                    'Evaluasi kinerja & laporan investor',
                    'Support WhatsApp (1x24 jam)',
                ],
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 'Custom',
                'period' => 'hubungi kami',
                'highlight' => false,
                'tagline' => 'Untuk koperasi besar & multi-resort',
                'features' => [
                    'Anggota tidak terbatas',
                    'Multi-resort tidak terbatas',
                    'Semua fitur Premium',
                    'Dedicated account manager',
                    'Custom integrasi & fitur',
                    'On-site training & migrasi data',
                    'SLA 99.9% & backup harian',
                    'Konsultasi strategi operasional',
                ],
            ],
        ],
        'footer_note' => 'Semua paket include: dashboard real-time, mobile app iOS/Android, backup otomatis, dan kepatuhan UU PDP.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Testimonials
    |--------------------------------------------------------------------------
    */
    'testimonials' => [
        'title' => 'Cerita dari Koperasi yang Sudah Bertransformasi',
        'subtitle' => 'Bukan testimonial berbayar — ini pengalaman nyata dari pengguna kami.',
        'items' => [
            [
                'quote' => 'Sebelum pakai e-Koperasi, kami rekap pinjaman & kasbon di Excel. Sekarang semua real-time dan NPL turun 40%.',
                'name' => 'I Wayan S.',
                'role' => 'Ketua Koperasi, Tabanan',
            ],
            [
                'quote' => 'Approval pinjaman dulu butuh 3 hari. Sekarang 15 menit, dengan AI scoring yang objektif. Tim nggak bisa main favorit lagi.',
                'name' => 'Ni Kadek P.',
                'role' => 'Koordinator, Badung',
            ],
            [
                'quote' => 'GPS lokasi nasabah & rute penagihan jadi game changer. PDL hemat waktu 30% lebih, tagihan jatuh tempo turun drastis. Admin bisa pantau semua dari dashboard.',
                'name' => 'I Putu A.',
                'role' => 'Admin Koperasi, Gianyar',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | FAQ
    |--------------------------------------------------------------------------
    */
    'faqs' => [
        'title' => 'Pertanyaan yang Sering Diajukan',
        'items' => [
            [
                'q' => 'Berapa lama proses setup & migrasi data?',
                'a' => 'Untuk koperasi dengan data di Excel/ledger, kami biasanya selesaikan migrasi dalam 7–14 hari. Termasuk training admin dan anggota.',
            ],
            [
                'q' => 'Apakah data koperasi aman?',
                'a' => 'Ya. Kami menggunakan enkripsi AES-256, backup harian, dan audit trail sesuai UU PDP. Data disimpan di cloud region Singapore dengan SLA 99.9%.',
            ],
            [
                'q' => 'Bagaimana sistem AI risk scoring bekerja?',
                'a' => 'Sistem kami menganalisis data berdasarkan nomor HP pengaju — mencakap riwayat pinjaman, pola pembayaran, dan risiko kredit. Hasilnya berupa skor risk yang membantu approval lebih objektif.',
            ],
            [
                'q' => 'Apakah ada biaya setup atau tersembunyi?',
                'a' => 'Tidak ada biaya setup. Harga di paket sudah termasuk onboarding, training, dan support. Batalkan kapan saja tanpa penalty.',
            ],
            [
                'q' => 'Apakah anggota perlu install app?',
                'a' => 'Aplikasi ini untuk internal koperasi (Admin, Koordinator, Lead, Cashier, PDL). Anggota bisa cek info via WhatsApp bot.',
            ],
            [
                'q' => 'Bagaimana cara request demo?',
                'a' => 'Klik tombol "Request Demo" di atas, isi form singkat, dan tim kami akan menghubungi via WhatsApp dalam 1x24 jam untuk jadwalkan sesi 30 menit.',
            ],
            [
                'q' => 'Bagaimana fitur GPS lokasi nasabah & rute penagihan bekerja?',
                'a' => 'Saat PDL mengajukan pinjaman untuk nasabah, GPS rumah & tempat usaha nasabah otomatis tersimpan. Admin bisa lihat semua lokasi nasabah di peta. Fitur rute tercepat mengoptimalkan kunjungan PDL berdasarkan jarak dan jadwal jatuh tempo — hemat waktu & BBM hingga 30%.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Final CTA
    |--------------------------------------------------------------------------
    */
    'cta' => [
        'title' => 'Siap Bawa Koperasi Anda ke Level Berikutnya?',
        'subtitle' => 'Konsultasi gratis 30 menit. Tidak ada komitmen. Tim kami akan demo sesuai kebutuhan koperasi Anda.',
        'primary' => [
            'label' => 'Request Demo Sekarang',
            'href' => '/demo#konsultasi',
        ],
        'secondary' => [
            'label' => 'WhatsApp Tim',
            'href' => 'https://wa.me/6281234567890',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | App Features (Mobile App Showcase)
    |--------------------------------------------------------------------------
    */
    'app_features' => [
        'badge' => 'Mobile App',
        'title' => 'Koperasi di Saku Anggota',
        'subtitle' => 'Aplikasi mobile untuk anggota koperasi. Cek tabungan, ajukan pinjaman, dan absensi — semua dari HP.',
        'features' => [
            [
                'title' => 'Cek Tabungan & Pinjaman',
                'description' => 'Anggota bisa melihat saldo tabungan dan potongan pinjaman, status pinjaman, dan riwayat transaksi kapan saja — tanpa perlu ke kantor.',
                'screenshot' => '/images/app-screenshots/homepage.png',
                'icon' => 'credit-card',
            ],
            [
                'title' => 'AI Risk Scoring & Rekomendasi',
                'description' => 'Sistem AI menganalisis histori pembayaran dan memberikan rekomendasi approve, review, atau reject pinjaman secara objektif.',
                'screenshot' => '/images/app-screenshots/AI-recomendation-approve.png',
                'icon' => 'brain',
            ],
            [
                'title' => 'Dashboard & Laporan',
                'description' => 'Pantau grafik performa keuangan, profit, dan budget real-time dari HP.',
                'screenshot' => '/images/app-screenshots/chart.png',
                'icon' => 'bar-chart',
            ],
            [
                'title' => 'Jatuh Tempo & Penagihan',
                'description' => 'Lihat daftar jatuh tempo, status pembayaran, dan route optimization untuk penagih.',
                'screenshot' => '/images/app-screenshots/jatuh-tempo.png',
                'icon' => 'trending-down',
            ],
            [
                'title' => 'List Angsuran & Transaksi',
                'description' => 'Kelola daftar angsuran, riwayat transaksi, dan pencatatan harian dalam satu layar.',
                'screenshot' => '/images/app-screenshots/list-angsuran.png',
                'icon' => 'list-ordered',
            ],
            [
                'title' => 'Slip Gaji Digital',
                'description' => 'Download slip gaji langsung dari HP. Lihat rincian gaji pokok, tunjangan, potongan kasbon, dan insentif.',
                'screenshot' => '/images/app-screenshots/pay-slip-dashboard.png',
                'icon' => 'file-text',
            ],
        ],
        'cta_label' => 'Coba Demo Sekarang',
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo Page
    |--------------------------------------------------------------------------
    */
    'demo' => [
        'badge' => 'Demo Interaktif',
        'title' => 'Coba e-Koperasi Sekarang',
        'subtitle' => 'Gunakan akun demo di bawah untuk masuk ke aplikasi. Tidak perlu daftar — semua perubahan akan di-reset harian.',
        'reset_notice' => 'Akun demo di-reset setiap hari pukul 00:00 WIB.',
        'accounts_intro' => 'Akun Demo Tersedia',
        'roles_intro' => [
            'admin'      => 'Ketua Koperasi',
            'koordinator'=> 'Koordinator Unit',
            'pimpinan'   => 'Pimpinan',
            'colead'     => 'Co-Lead',
            'cashier'    => 'Cashier',
            'pdl'        => 'Penagih (PDL)',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact / Company
    |--------------------------------------------------------------------------
    */
    'contact' => [
        'email'     => 'halo@e-koperasi.com',
        'whatsapp'  => '+62 812-3456-7890',
        'whatsapp_url' => 'https://wa.me/6281234567890',
        'address'   => 'Tabanan, Bali, Indonesia',
    ],

    /*
    |--------------------------------------------------------------------------
    | About Page
    |--------------------------------------------------------------------------
    */
    'about' => [
        'company' => [
            'name'       => 'e-Koperasi',
            'legal_name' => 'CV Tabanan Digital Nusantara',
            'founded'    => '2022',
            'origin'     => 'Tabanan, Bali',
            'mission'    => 'Mendigitalisasi koperasi Indonesia agar lebih efisien, transparan, dan modern.',
            'vision'     => 'Menjadi platform standar operasional koperasi Indonesia pada 2030.',
            'story'      => 'e-Koperasi lahir dari kebutuhan nyata KSU Tabanan Jaya — koperasi serba usaha yang melayani 3000+ anggota di Bali. Sistem manual Excel dan WhatsApp tidak lagi memadai. Kami membangun platform all-in-one: dari pencatatan kasbon, pinjaman, slip gaji, GPS lokasi nasabah, rute tercepat penagihan, sampai evaluasi kinerja PDL. Setelah terbukti menurunkan NPL 40% di internal, kami membuka platform ini untuk koperasi lain di Indonesia.',
            'values'     => [
                ['title' => 'Trasparan',   'desc' => 'Setiap transaksi tercatat real-time, audit-ready.'],
                ['title' => 'Indonesia-First', 'desc' => 'Dibangun oleh orang Indonesia, untuk koperasi Indonesia.'],
                ['title' => 'Pragmatis',    'desc' => 'Fitur yang solving masalah nyata, bukan nice-to-have.'],
            ],
            'team'       => [
                ['name' => 'I Wayan Sudirta', 'role' => 'CEO & Co-Founder', 'bio' => '15 tahun di industri koperasi, Ketua KSU Tabanan Jaya.'],
                ['name' => 'I Made Wirawan',  'role' => 'CTO & Co-Founder', 'bio' => 'Full-stack engineer, membangun sistem dari 0.'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Legal Pages
    |--------------------------------------------------------------------------
    */
    'legal' => [
        'last_updated' => '15 Juni 2026',
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Defaults
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'default_title' => 'e-Koperasi - Platform Digital Koperasi Indonesia',
        'description'   => 'Platform monitoring & operasional koperasi — pinjaman, kasbon, absensi, slip gaji, evaluasi kinerja. AI risk scoring, rule engine, dashboard real-time.',
        'keywords'      => 'koperasi, aplikasi koperasi, pinjaman, kasbon, absensi digital, slip gaji, evaluasi kinerja, Indonesia',
    ],

];
