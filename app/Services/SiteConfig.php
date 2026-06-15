<?php

namespace App\Services;

use App\Models\SiteContent;

class SiteConfig
{
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        // Start with config file defaults
        $config = config('site', []);

        // Override with database values if table exists and has data
        try {
            $dbConfig = SiteContent::allAsConfig();
            $config = array_replace_recursive($config, $dbConfig);
        } catch (\Exception $e) {
            // Table might not exist yet, fall back to config file
        }

        self::$cache = $config;
        return $config;
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

    /**
     * Clear the config cache (call after DB update).
     */
    public static function clearCache(): void
    {
        self::$cache = null;
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
