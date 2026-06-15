<?php

namespace App\Services;

class SiteConfig
{
    public static function all(): array
    {
        return [
            'pricing' => self::pricing(),
            'demo_accounts' => self::demoAccounts(),
            'stats' => self::stats(),
            'contact' => self::contact(),
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $data = self::all();
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (!isset($data[$k])) return $default;
            $data = $data[$k];
        }
        return $data;
    }

    public static function pricing(): array
    {
        return [
            [
                'name' => 'Starter',
                'price' => 'Rp 1.5jt',
                'period' => 'bulan',
                'highlight' => false,
                'features' => [
                    'Hingga 100 anggota',
                    '1 unit koperasi',
                    'Web dashboard',
                    'Modul pinjaman & simpanan',
                    'Support email',
                ],
            ],
            [
                'name' => 'Bisnis',
                'price' => 'Rp 4jt',
                'period' => 'bulan',
                'highlight' => true,
                'features' => [
                    'Hingga 500 anggota',
                    'Sampai 3 unit',
                    'Web + mobile app',
                    'Semua modul (BON, gaji, dll)',
                    'Rule engine',
                    'Support WhatsApp 8/5',
                ],
            ],
            [
                'name' => 'Enterprise',
                'price' => 'Custom',
                'period' => 'hubungi kami',
                'highlight' => false,
                'features' => [
                    'Unlimited anggota & unit',
                    'Multi-resort & multi-role',
                    'Custom rule engine',
                    'On-premise deployment option',
                    'SLA 99.9% & on-site training',
                    'Dedicated account manager',
                ],
            ],
        ];
    }

    public static function demoAccounts(): array
    {
        return [
            ['role' => 'Admin (Ketua)', 'email' => 'admin@demo.e-koperasi.com', 'pin' => '123456'],
            ['role' => 'Koordinator', 'email' => 'koordinator@demo.e-koperasi.com', 'pin' => '123456'],
            ['role' => 'Pimpinan', 'email' => 'pimpinan@demo.e-koperasi.com', 'pin' => '123456'],
            ['role' => 'Co-Lead', 'email' => 'colead@demo.e-koperasi.com', 'pin' => '123456'],
            ['role' => 'Cashier', 'email' => 'cashier@demo.e-koperasi.com', 'pin' => '123456'],
            ['role' => 'PDL (Penagih)', 'email' => 'pdl@demo.e-koperasi.com', 'pin' => '123456'],
        ];
    }

    public static function stats(): array
    {
        return [
            ['value' => '50+', 'label' => 'Koperasi Aktif'],
            ['value' => '10K+', 'label' => 'Anggota Terdaftar'],
            ['value' => '40%', 'label' => 'Penurunan NPL'],
            ['value' => '24/7', 'label' => 'Support & Uptime'],
        ];
    }

    public static function contact(): array
    {
        return [
            'email' => 'halo@e-koperasi.com',
            'whatsapp' => '+62 812-3456-7890',
            'address' => 'Tabanan, Bali, Indonesia',
        ];
    }
}
