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
        'description' => 'Platform monitoring dan operasional koperasi untuk pinjaman, kasbon, absensi, dan slip gaji dalam satu aplikasi internal.',
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
        'description' => 'Platform monitoring dan operasional koperasi untuk pinjaman, kasbon, absensi, slip gaji, dan evaluasi kinerja.',
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
        'subheadline' => 'Platform all-in-one untuk internal koperasi. Kelola pinjaman harian, mingguan, dan tempoan, kasbon, absensi foto, slip gaji, dan evaluasi kinerja. Didukung AI risk scoring, rule engine dinamis, dan dashboard real-time.',
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
            'title' => 'Untuk Admin',
            'subtitle' => 'Ketua koperasi',
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
            'title' => 'Untuk Penagih',
            'subtitle' => 'Tim di lapangan',
            'description' => 'Absensi foto, GPS tracking, catat titipan harian, dan lihat jadwal tagihan. Semua dari HP.',
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
                'tenor' => 'flexible sesuai keperluan',
                'approval' => '1x24 jam',
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
                'tenor' => 'flexible sesuai keperluan',
                'approval' => '1x24 jam',
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
                'tenor' => '3 hingga 12 bulan',
                'approval' => '2x24 jam',
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
            'title' => 'Manajemen Transaksi Harian',
            'description' => 'Catat pemasukan dan pengeluaran unit atau resort secara digital. Rekonsiliasi real-time dan laporan harian otomatis. Dilengkapi workflow approval untuk setiap transaksi.',
            'icon' => 'banknote',
        ],
        [
            'title' => 'Pinjaman 4 Jenis + Agunan Digital',
            'description' => 'Harian, mingguan, bulanan, dan tempo. Masing-masing dilengkapi simulasi dana bersih instan dan approval workflow. Unggah foto dan video agunan langsung dari HP. Ada fitur early termination untuk pelunasan dipercepat.',
            'icon' => 'credit-card',
        ],
        [
            'title' => 'Tabungan, Deposito & Simpanan',
            'description' => 'Setoran dan penarikan tunai dengan mutasi detail. Fitur pending debit yang perlu approval admin. Auto-apply deposito ke angsuran. Pantau total tabungan per periode.',
            'icon' => 'badge-dollar-sign',
        ],
        [
            'title' => 'AI Risk Scoring & Analisis Kredit',
            'description' => 'Sistem AI menganalisis histori pembayaran nasabah, memberi skor risiko objektif, dan rekomendasi approve, review, atau reject. Deteksi dini NPL dan evaluasi kinerja real-time.',
            'icon' => 'trending-up',
        ],
        [
            'title' => 'Rule Engine & Insentif No-Code',
            'description' => 'Buat aturan bisnis dan insentif tanpa coding. Multi fact type, prioritas dan kondisi logika. Toggle aktif atau nonaktif kapan saja. Evaluasi aturan berjalan otomatis dan real-time.',
            'icon' => 'cog',
        ],
        [
            'title' => 'Slip Gaji & Kasbon Digital',
            'description' => 'Generate, publish, dan bagikan slip gaji dalam format PDF. Kelola kasbon sementara dan titipan gaji. Tambah insentif atau potongan manual per slip. Download langsung dari HP.',
            'icon' => 'file-text',
        ],
        [
            'title' => 'Dashboard & Laporan Investor',
            'description' => 'Ringkasan outstanding, target PDL, laba rugi per produk, arus kas, analisis NPL, dan budget versus realisasi. Semua real-time. Laporan investor eksklusif untuk pimpinan.',
            'icon' => 'bar-chart',
        ],
        [
            'title' => 'Multi Perusahaan & Struktur Hirarkis',
            'description' => 'Kelola beberapa koperasi dalam satu platform. Struktur organisasi terdiri dari Perusahaan, Unit, Resort, dan Koordinator. Enam role berbeda dengan akses dan tampilan yang disesuaikan.',
            'icon' => 'building',
        ],
        [
            'title' => 'Absensi, Tracking & Portal Nasabah',
            'description' => 'Absensi dengan foto dan deskripsi. Tracking lokasi petugas secara real-time. Portal publik untuk nasabah cek pinjaman dan tabungan sendiri via NIK. Transparan tanpa login internal.',
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
        'subtitle' => 'Bukan testimonial berbayar, ini pengalaman nyata dari pengguna kami.',
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
                'q' => 'Fitur apa saja yang tersedia di aplikasi ini?',
                'a' => 'Ada 15 fitur unggulan. Mulai dari dashboard multi-role yang menyesuaikan level pengguna, manajemen nasabah dengan OCR KTP, pinjaman 4 jenis (harian, mingguan, bulanan, tempo) dengan agunan digital berupa foto dan video, AI risk scoring untuk analisis kredit, tabungan dan deposito digital, laporan keuangan dan investor real-time, slip gaji dan kasbon digital, rule engine no-code, tracking lokasi dan absensi lapangan, manajemen organisasi multi-perusahaan, push notifikasi, budget dan expense management, hingga portal publik untuk nasabah.',
            ],
            [
                'q' => 'Jenis pinjaman apa saja yang didukung?',
                'a' => 'Ada 4 jenis. Pinjaman Harian dengan tenor 14 hingga 60 hari dan angsuran harian. Pinjaman Mingguan dengan angsuran per minggu. Pinjaman Bulanan untuk jangka menengah. Pinjaman Tempo untuk jangka panjang dengan pembayaran sekaligus saat jatuh tempo. Semuanya dilengkapi simulasi dana bersih instan dan fitur pelunasan dipercepat.',
            ],
            [
                'q' => 'Apakah ada fitur agunan untuk pinjaman?',
                'a' => 'Ada. Setiap pengajuan pinjaman bisa disertai agunan digital berupa foto dan video. Tipe agunan yang didukung meliputi kendaraan, tanah atau bangunan, elektronik, dan lainnya. Petugas lapangan bisa langsung memotret agunan dari HP saat survei. Sistem otomatis menyimpan nilai agunan dan menghitung rasio pinjaman terhadap nilai agunan.',
            ],
            [
                'q' => 'Bagaimana sistem AI risk scoring bekerja?',
                'a' => 'Sistem AI menganalisis histori pembayaran nasabah, mencakup riwayat pinjaman sebelumnya, pola pembayaran apakah tepat waktu atau telat, jumlah tunggakan, dan status NPL. Hasilnya berupa skor risiko dengan 3 rekomendasi yaitu approve untuk yang layak, review untuk yang perlu verifikasi manual, atau reject untuk yang berisiko tinggi. Sistem ini membantu mengurangi kredit macet dan menjaga objektivitas keputusan.',
            ],
            [
                'q' => 'Siapa saja yang bisa menggunakan aplikasi ini?',
                'a' => 'Aplikasi ini untuk tim internal koperasi dengan 6 level pengguna. Admin atau Ketua dengan akses penuh ke seluruh fitur dan laporan investor. Koordinator untuk memantau beberapa unit binaan. Lead dan Co-Lead sebagai pimpinan unit. Kasir yang fokus pada transaksi harian dan budget. Serta PDL atau Penagih yang prioritasnya pada tagihan, target, dan rute penagihan. Setiap level melihat dashboard yang berbeda sesuai tugasnya.',
            ],
            [
                'q' => 'Apakah anggota bisa cek data mereka sendiri?',
                'a' => 'Bisa. Kami menyediakan Portal Publik Nasabah. Anggota cukup memasukkan ID nasabah dan NIK, mereka bisa melihat status pinjaman berjalan, riwayat tabungan, dan informasi akun langsung dari HP tanpa perlu login ke aplikasi internal koperasi.',
            ],
            [
                'q' => 'Apakah bisa mengelola lebih dari satu koperasi?',
                'a' => 'Bisa. Satu platform mendukung multi-perusahaan. Struktur organisasinya terdiri dari Perusahaan, Koordinator, Unit, dan Resort. Masing-masing entitas bisa memiliki pengaturan bunga, biaya admin, dan parameter bisnis yang berbeda, semuanya dari satu akun admin.',
            ],
            [
                'q' => 'Bagaimana cara mengelola slip gaji karyawan?',
                'a' => 'Slip gaji dikelola langsung dari aplikasi. Generate slip dengan rincian gaji pokok, tunjangan, insentif, dan potongan seperti kasbon, titipan gaji, atau tabungan. Bisa tambah insentif atau potongan manual per slip. Setelah publish, karyawan bisa download PDF langsung dari HP atau bagikan via WhatsApp. Semua terintegrasi tanpa aplikasi terpisah.',
            ],
            [
                'q' => 'Apa itu Rule Engine?',
                'a' => 'Rule Engine adalah fitur untuk membuat aturan bisnis tanpa coding. Anda bisa membuat aturan insentif, logika approval, dan keputusan bisnis lainnya dengan menentukan jenis keputusan, prioritas evaluasi, kondisi logika, dan hasil yang diinginkan. Aturan bisa diaktifkan atau dinonaktifkan kapan saja. Sistem mengevaluasi aturan secara real-time, cocok untuk perhitungan insentif PDL, bonus, dan kebijakan dinamis lainnya.',
            ],
            [
                'q' => 'Bagaimana fitur tracking lokasi dan absensi untuk tim lapangan?',
                'a' => 'Petugas lapangan atau PDL melakukan absensi dengan foto bukti kehadiran dan deskripsi kegiatan. Admin bisa mengirim permintaan lokasi ke petugas, dan saat direspon posisi real-time tercatat di peta. Riwayat pergerakan tim lapangan tersimpan lengkap dan bisa difilter per tanggal. Ada juga fitur Nearest Customer yang menampilkan nasabah terdekat berdasarkan GPS untuk efisiensi kunjungan.',
            ],
            [
                'q' => 'Berapa lama proses setup dan migrasi data?',
                'a' => 'Untuk koperasi dengan data di Excel atau ledger, migrasi biasanya selesai dalam 7 hingga 14 hari. Termasuk import data nasabah, pinjaman berjalan, tabungan, dan saldo, plus training untuk admin dan tim operasional. Tidak ada biaya setup.',
            ],
            [
                'q' => 'Apakah data koperasi aman?',
                'a' => 'Aman. Setiap akun pengguna diautentikasi dengan token. Data sensitif dienkripsi menggunakan standar AES-256. Ada backup harian otomatis, audit trail untuk setiap transaksi, dan log aktivitas pengguna yang lengkap. Sistem dirancang sesuai prinsip keamanan UU PDP Indonesia.',
            ],
            [
                'q' => 'Bagaimana cara memulai atau request demo?',
                'a' => 'Bisa langsung mencoba demo interaktif di halaman Demo dengan akun yang sudah tersedia tanpa perlu daftar. Atau klik tombol Request Demo, isi form singkat, dan tim kami akan menghubungi via WhatsApp dalam 1x24 jam untuk sesi konsultasi 30 menit sesuai kebutuhan koperasi Anda.',
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
        'subtitle' => 'Aplikasi mobile all-in-one untuk tim koperasi. Kelola simpan pinjam, lacak tim lapangan, atur insentif tanpa coding, dan akses laporan keuangan secara real-time dari genggaman.',
        'features' => [
            [
                'title' => 'Multi-Role Dashboard & Simpan Pinjam',
                'description' => 'Dashboard yang menyesuaikan peran pengguna, baik Admin, Kasir, PDL, maupun Koordinator. Masing-masing melihat data yang relevan. Cek saldo tabungan, status pinjaman, dan riwayat transaksi dalam satu layar. Tersedia dark mode.',
                'screenshot' => '/images/app-screenshots/homepage.png',
                'icon' => 'credit-card',
            ],
            [
                'title' => 'AI Risk Scoring & Rekomendasi Kredit',
                'description' => 'Sistem AI menganalisis histori pembayaran nasabah, memberikan skor risiko objektif, dan rekomendasi approve, review, atau reject. Membantu mengurangi risiko kredit macet dengan keputusan berbasis data.',
                'screenshot' => '/images/app-screenshots/AI-recomendation-approve.png',
                'icon' => 'brain',
            ],
            [
                'title' => 'Laporan Keuangan & Analitik Real-Time',
                'description' => 'Pantau grafik performa keuangan, laba rugi per produk, analisis NPL, arus kas, dan budget versus realisasi secara real-time. Dilengkapi laporan investor eksklusif untuk pimpinan.',
                'screenshot' => '/images/app-screenshots/chart.png',
                'icon' => 'bar-chart',
            ],
            [
                'title' => 'Manajemen Tagihan & Optimasi Rute Penagihan',
                'description' => 'Daftar jatuh tempo terurut berdasarkan urgensi dengan status pembayaran real-time. Route optimization untuk penagih lapangan untuk menghemat waktu dan biaya operasional.',
                'screenshot' => '/images/app-screenshots/jatuh-tempo.png',
                'icon' => 'trending-down',
            ],
            [
                'title' => 'Rule Engine & Insentif No-Code',
                'description' => 'Buat aturan bisnis dan insentif tanpa coding. Tentukan kondisi logika, prioritas, dan hasil secara mandiri. Toggle aktif atau nonaktif kapan saja. Sistem mengevaluasi aturan secara real-time.',
                'screenshot' => '/images/app-screenshots/list-angsuran.png',
                'icon' => 'list-ordered',
            ],
            [
                'title' => 'Absensi, Tracking & Portal Nasabah',
                'description' => 'Absensi dengan foto kehadiran. Tracking lokasi petugas secara real-time via GPS. Portal Publik Nasabah untuk cek pinjaman dan tabungan sendiri via NIK. Tanpa perlu login ke aplikasi internal.',
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
        'subtitle' => 'Gunakan akun demo di bawah untuk masuk ke aplikasi. Tidak perlu daftar, semua perubahan akan di-reset harian.',
        'reset_notice' => 'Akun demo di-reset setiap hari pukul 00:00 WIB.',
        'accounts_intro' => 'Akun Demo Tersedia',
        'accounts' => [
            ['key' => 'admin', 'label' => 'Ketua Koperasi', 'email' => 'admin@demo.e-koperasi.com', 'pin' => '123456'],
            ['key' => 'koordinator', 'label' => 'Koordinator Unit', 'email' => 'koordinator@demo.e-koperasi.com', 'pin' => '123456'],
            ['key' => 'pimpinan', 'label' => 'Pimpinan', 'email' => 'pimpinan@demo.e-koperasi.com', 'pin' => '123456'],
            ['key' => 'colead', 'label' => 'Co-Lead', 'email' => 'colead@demo.e-koperasi.com', 'pin' => '123456'],
            ['key' => 'cashier', 'label' => 'Cashier', 'email' => 'cashier@demo.e-koperasi.com', 'pin' => '123456'],
            ['key' => 'pdl', 'label' => 'Penagih (PDL)', 'email' => 'pdl@demo.e-koperasi.com', 'pin' => '123456'],
        ],
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
            'story'      => 'e-Koperasi lahir dari kebutuhan nyata KSU Tabanan Jaya, koperasi serba usaha yang melayani lebih dari 3000 anggota di Bali. Sistem manual Excel dan WhatsApp tidak lagi memadai. Kami membangun platform all-in-one mulai dari pencatatan kasbon, pinjaman, slip gaji, GPS lokasi nasabah, rute tercepat penagihan, sampai evaluasi kinerja PDL. Setelah terbukti menurunkan NPL hingga 40% di internal, kami membuka platform ini untuk koperasi lain di Indonesia.',
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
        'description'   => 'Platform monitoring dan operasional koperasi untuk pinjaman, kasbon, absensi, slip gaji, dan evaluasi kinerja. Dilengkapi AI risk scoring, rule engine, dan dashboard real-time.',
        'keywords'      => 'koperasi, aplikasi koperasi, pinjaman, kasbon, absensi digital, slip gaji, evaluasi kinerja, Indonesia',
    ],

];
