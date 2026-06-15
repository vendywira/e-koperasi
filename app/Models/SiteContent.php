<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    protected $fillable = ['section', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get a section's value by key path.
     * e.g. getValue('hero.headline') returns the headline from hero section.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $section = $parts[0];

        $record = static::where('section', $section)->first();
        if (!$record) {
            return $default;
        }

        $data = $record->value;
        for ($i = 1; $i < count($parts); $i++) {
            if (!is_array($data) || !array_key_exists($parts[$i], $data)) {
                return $default;
            }
            $data = $data[$parts[$i]];
        }

        return $data;
    }

    /**
     * Get all sections as a config-like array.
     */
    public static function allAsConfig(): array
    {
        $result = [];
        $contents = static::all();

        foreach ($contents as $content) {
            $result[$content->section] = $content->value;
        }

        return $result;
    }

    /**
     * Save or update a section.
     */
    public static function saveSection(string $section, array $value): static
    {
        return static::updateOrCreate(
            ['section' => $section],
            ['value' => $value]
        );
    }
}
