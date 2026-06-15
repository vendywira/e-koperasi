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
        'description' => 'Kelola simpanan, pinjaman, dan absensi anggota dalam satu aplikasi mobile-first.',
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
        'description' => 'Solusi digital untuk koperasi modern Indonesia. Simpanan, pinjaman, dan absensi dalam satu platform.',
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
        'headline' => 'Simpanan & Pinjaman Koperasi Lebih Cerdas, Lebih Transparan',
        'subheadline' => 'Kelola semua produk keuangan koperasi — simpanan pokok, wajib, sukarela, pinjaman harian, mingguan, tempo — dalam satu platform. Didukung AI risk scoring, dashboard real-time, dan mobile app untuk anggota.',
        'primary_cta' => [
            'label' => 'Coba Demo Gratis',
            'href' => '/demo',
        ],
        'secondary_cta' => [
            'label' => 'Lihat Produk',
            'href' => '/#produk',
        ],
        'floating_badge' => 'UU PDP Compliant',
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
            'title' => 'Untuk Anggota',
            'subtitle' => 'Pengguna mobile',
            'description' => 'Cek simpanan, ajukan pinjaman, absensi kehadiran — semua dari HP. Tidak perlu ke kantor koperasi.',
            'icon' => 'smartphone',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Showcase (NEW)
    |--------------------------------------------------------------------------
    */
    'products' => [
        'highlights' => [
            ['value' => 'Enkripsi AES-256', 'label' => 'Keamanan Data', 'icon' => 'shield'],
            ['value' => 'Real-time', 'label' => 'Update Otomatis', 'icon' => 'zap'],
            ['value' => '24/7', 'label' => 'Akses Kapan Saja', 'icon' => 'clock'],
            ['value' => 'AI-Powered', 'label' => 'Risk Scoring', 'icon' => 'sparkles'],
        ],                'saving' => [
            [
                'name' => 'Simpanan Pokok',
                'tagline' => 'Investasi jangka panjang anggota koperasi',
                'icon' => 'landmark',
                'color' => 'emerald',
                'screenshot' => 'deposit.webp',
                'benefits' => [
                    'Setoran tetap per bulan',
                    'Tidak dapat ditarik sebelum keluar',
                    'Membangun fondasi keuangan koperasi',
                    'Laporan saldo real-time di app',
                ],
            ],
            [
                'name' => 'Simpanan Wajib',
                'tagline' => 'Kewajiban rutin untuk setiap anggota',
                'icon' => 'wallet',
                'color' => 'blue',
                'screenshot' => 'deposit.webp',
                'benefits' => [
                    'Setoran otomatis dari gaji',
                    'Bisa dijadikan jaminan pinjaman',
                    'Potongan langsung dari slip gaji',
                    'Tracking saldo via mobile app',
                ],
            ],
            [
                'name' => 'Simpanan Sukarela',
                'tagline' => 'Tabungan fleksibel kapan saja',
                'icon' => 'piggy-bank',
                'color' => 'purple',
                'screenshot' => 'deposit.webp',
                'benefits' => [
                    'Setor & tarik kapan saja',
                    'Bunga kompetitif',
                    'Minimum setoran rendah',
                    'Tersedia di mobile app anggota',
                ],
            ],
            [
                'name' => 'Titipan',
                'tagline' => 'Dana titipan penagih & anggota',
                'icon' => 'gift',
                'color' => 'amber',
                'screenshot' => 'list-angsuran.webp',
                'benefits' => [
                    'Titipan harian dari PDL',
                    'Auto-debet untuk cicilan',
                    'Rekonsiliasi otomatis',
                    'Audit trail lengkap',
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
                'screenshot' => 'jatuh-tempo.webp',
                'rate' => '20%',
                'tenor' => '14–60 hari',
                'approval' => '1×24 jam',
                'benefits' => [
                    'Proses approval cepat',
                    'AI risk scoring otomatis',
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
                'screenshot' => 'list-angsuran.webp',
                'rate' => '20%',
                'tenor' => '4–24 minggu',
                'approval' => '1×24 jam',
                'benefits' => [
                    'Cicilan per minggu',
                    'Jadwal pembayaran fleksibel',
                    'GPS tracking penagih',
                    'Slip gaji terintegrasi',
                ],
            ],
            [
                'name' => 'Pinjaman Tempo',
                'description' => 'Pinjaman jangka panjang dengan pembayaran sekaligus saat jatuh tempo. Untuk kebutuhan besar.',
                'badge' => 'Tempo',
                'icon' => 'trending-down',
                'accent' => 'violet',
                'screenshot' => 'AI-recomendation-approve.webp',
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
            'title' => 'Titipan & Simpanan',
            'description' => 'Simpanan pokok, wajib, sukarela. Titipan penagih. Approval flow.',
            'icon' => 'badge-dollar-sign',
        ],
        [
            'title' => 'Kontrol Pinjaman + AI',
            'description' => 'Pinjaman gadai/agunan (foto+video), AI risk scoring, GPS tracking, route optimization.',
            'icon' => 'trending-up',
        ],
        [
            'title' => 'Rule Engine Dinamis',
            'description' => 'Atur logika bisnis tanpa coding. Insentif, pinjaman, approval bertingkat.',
            'icon' => 'cog',
        ],
        [
            'title' => 'Slip Gaji Digital',
            'description' => 'Cetak PDF, tunjangan & insentif otomatis, integrasi simpanan & kasbon.',
            'icon' => 'file-text',
        ],
        [
            'title' => 'Real-time Dashboard',
            'description' => 'Performance, saldo lapangan, NPL, target harian, nasabah aktif — semua real-time.',
            'icon' => 'bar-chart',
        ],
        [
            'title' => 'Multi-Resort & Multi-Unit',
            'description' => '6 role bertingkat, budgeting per unit, laporan lintas resort.',
            'icon' => 'building',
        ],
        [
            'title' => 'Absensi Foto + Kalender',
            'description' => 'Upload foto absen anggota di meja kerja. Set hari libur untuk loan schedule.',
            'icon' => 'camera',
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
                'description' => 'Mulai catat simpanan, pinjaman, dan absensi. Dukungan WhatsApp 1x24 jam.',
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
                    'Manajemen tunai & kasbon',
                    'Simpanan & titipan',
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
                    'AI risk scoring pinjaman',
                    'Rule engine dinamis',
                    'Slip gaji digital',
                    'Kontrol pinjaman + GPS tracking',
                    'Real-time dashboard & laporan',
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
                'quote' => 'Sebelum pakai e-Koperasi, kami rekap simpanan & pinjaman di Excel. Sekarang semua real-time dan NPL turun 40%.',
                'name' => 'I Wayan S.',
                'role' => 'Ketua Koperasi, Tabanan',
            ],
            [
                'quote' => 'Approval pinjaman dulu butuh 3 hari. Sekarang 15 menit, dengan AI scoring yang objektif. Tim nggak bisa main favorit lagi.',
                'name' => 'Ni Kadek P.',
                'role' => 'Koordinator, Badung',
            ],
            [
                'quote' => 'Anggota kami suka banget sama mobile app. Absensi tinggal foto, simpanan bisa dicek sendiri. Operasional kantor turun drastis.',
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
                'a' => 'AI menganalisis histori pembayaran, pola pinjaman, dan cashflow anggota. Hasilnya adalah skor 0–100 yang membantu approval lebih objektif — bukan menggantikan keputusan pengurus.',
            ],
            [
                'q' => 'Apakah ada biaya setup atau tersembunyi?',
                'a' => 'Tidak ada biaya setup. Harga di paket sudah termasuk onboarding, training, dan support. Batalkan kapan saja tanpa penalty.',
            ],
            [
                'q' => 'Apakah anggota perlu install app?',
                'a' => 'Untuk paket Premium ke atas, ya — anggota dapat mobile app iOS & Android. Untuk Starter, bisa pakai WhatsApp bot sebagai alternatif.',
            ],
            [
                'q' => 'Bagaimana cara request demo?',
                'a' => 'Klik tombol "Request Demo" di atas, isi form singkat, dan tim kami akan menghubungi via WhatsApp dalam 1x24 jam untuk jadwalkan sesi 30 menit.',
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
            'story'      => 'e-Koperasi lahir dari kebutuhan nyata KSU Tabanan Jaya — koperasi serba usaha yang melayani 3000+ anggota di Bali. Sistem manual Excel dan WhatsApp tidak lagi memadai. Kami membangun platform all-in-one: dari pencatatan kasbon, pinjaman, slip gaji, sampai tracking GPS penagih. Setelah terbukti di internal, kami membuka platform ini untuk koperasi lain di Indonesia.',
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
        'description'   => 'Kelola simpanan, pinjaman, dan absensi koperasi dalam satu platform mobile-first. AI risk scoring, dashboard real-time, dan kepatuhan UU PDP.',
        'keywords'      => 'koperasi, aplikasi koperasi, simpan pinjam, absensi digital, UU PDP, Indonesia',
    ],

];
