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
        $demoConfig = self::get('demo', []);
        $roleLabels = $demoConfig['roles_intro'] ?? [];
        $cmsAccounts = $demoConfig['accounts'] ?? [];

        // Use CMS accounts if available, otherwise fallback to defaults
        if (!empty($cmsAccounts) && is_array($cmsAccounts)) {
            return array_map(function ($account) {
                return [
                    'email' => $account['email'] ?? '',
                    'pin' => $account['pin'] ?? '',
                    'role' => $account['label'] ?? $account['key'] ?? '',
                ];
            }, $cmsAccounts);
        }

        $accounts = [
            ['key' => 'admin', 'email' => 'admin@demo.e-koperasi.com', 'pin' => '123456'],
            ['key' => 'koordinator', 'email' => 'koordinator@demo.e-koperasi.com', 'pin' => '123456'],
            ['key' => 'pimpinan', 'email' => 'pimpinan@demo.e-koperasi.com', 'pin' => '123456'],
            ['key' => 'colead', 'email' => 'colead@demo.e-koperasi.com', 'pin' => '123456'],
            ['key' => 'cashier', 'email' => 'cashier@demo.e-koperasi.com', 'pin' => '123456'],
            ['key' => 'pdl', 'email' => 'pdl@demo.e-koperasi.com', 'pin' => '123456'],
        ];

        return array_map(function ($account) use ($roleLabels) {
            $account['role'] = $roleLabels[$account['key']] ?? $account['key'];
            unset($account['key']);
            return $account;
        }, $accounts);
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
