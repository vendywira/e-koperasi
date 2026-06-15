<?php

namespace App\Services;

class SiteConfig
{
    public static function all(): array
    {
        return config('site', []);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $data = self::all();
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (!is_array($data) || !array_key_exists($k, $data)) {
                return $default;
            }
            $data = $data[$k];
        }
        return $data;
    }

    public static function pricing(): array
    {
        return self::get('pricing.tiers', []);
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
        return self::get('stats', []);
    }

    public static function contact(): array
    {
        return self::get('contact', []);
    }
}
