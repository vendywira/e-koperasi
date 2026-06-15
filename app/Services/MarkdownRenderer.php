<?php

namespace App\Services;

use Parsedown;

class MarkdownRenderer
{
    public static function render(string $markdown): string
    {
        $parser = new Parsedown();
        $parser->setSafeMode(true);
        return $parser->text($markdown);
    }

    public static function file(string $path): string
    {
        $fullPath = base_path($path);
        if (! file_exists($fullPath)) {
            throw new \RuntimeException("Markdown file not found: {$path}");
        }
        return self::render(file_get_contents($fullPath));
    }
}
