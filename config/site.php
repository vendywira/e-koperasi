<?php

return [
    'name' => 'e-Koperasi',
    'tagline' => 'Platform Digital untuk Koperasi Indonesia',
    'description' => 'Kelola tunai, kasbon, titipan, pinjaman, gaji, dan tracking lapangan dalam satu platform. Dengan dukungan AI dan rule engine.',

    'contact' => [
        'email' => 'halo@e-koperasi.com',
        'whatsapp' => '+6281234567890',
        'address' => 'Tabanan, Bali, Indonesia',
    ],

    'demo' => [
        'portal_url' => env('DEMO_PORTAL_URL', 'https://demo.e-koperasi.com'),
        'accounts' => [
            ['role' => 'Admin (Ketua)', 'email' => 'admin@demo.e-koperasi.com', 'password' => 'demo123'],
            ['role' => 'Koordinator', 'email' => 'koord@demo.e-koperasi.com', 'password' => 'demo123'],
            ['role' => 'Pimpinan', 'email' => 'pimpinan@demo.e-koperasi.com', 'password' => 'demo123'],
            ['role' => 'Kepala Mantri', 'email' => 'mantri@demo.e-koperasi.com', 'password' => 'demo123'],
            ['role' => 'Cashier', 'email' => 'kasir@demo.e-koperasi.com', 'password' => 'demo123'],
            ['role' => 'Penagih (PDL)', 'email' => 'penagih@demo.e-koperasi.com', 'password' => 'demo123'],
        ],
    ],

    'pricing' => [
        'starter' => [
            'name' => 'Starter',
            'price' => 'Rp 499K',
            'period' => 'per bulan',
            'highlight' => false,
            'features' => [
                'Hingga 100 anggota',
                '1 unit koperasi',
                'Manajemen tunai & kasbon',
                'Simpanan & titipan',
                'Dashboard dasar',
                'Support email',
            ],
        ],
        'bisnis' => [
            'name' => 'Bisnis',
            'price' => 'Rp 1.5jt',
            'period' => 'per bulan',
            'highlight' => true,
            'features' => [
                'Hingga 1000 anggota',
                'Multi-unit (3 resort)',
                'Semua fitur Starter',
                'AI risk scoring pinjaman',
                'Rule engine dinamis',
                'Slip gaji digital',
                'Absensi foto',
                'Support WhatsApp',
            ],
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 'Custom',
            'period' => 'hubungi kami',
            'highlight' => false,
            'features' => [
                'Anggota tidak terbatas',
                'Multi-resort tidak terbatas',
                'Semua fitur Bisnis',
                'Dedicated account manager',
                'Custom integrasi',
                'SLA 99.9%',
                'On-site training',
            ],
        ],
    ],

    'stats' => [
        ['value' => '50+', 'label' => 'Koperasi Aktif'],
        ['value' => '1.000+', 'label' => 'Anggota Terdaftar'],
        ['value' => 'Rp 25M+', 'label' => 'Pinjaman Dikelola/Bulan'],
        ['value' => '4.9/5', 'label' => 'Rating Kepuasan'],
    ],
];
