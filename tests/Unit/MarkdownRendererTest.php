<?php

namespace Tests\Unit;

use App\Services\MarkdownRenderer;
use Tests\TestCase;

class MarkdownRendererTest extends TestCase
{
    public function test_renders_basic_markdown_to_html(): void
    {
        $result = MarkdownRenderer::render('# Hello');
        $this->assertStringContainsString('<h1>Hello</h1>', $result);
    }

    public function test_throws_for_missing_file(): void
    {
        $this->expectException(\RuntimeException::class);
        MarkdownRenderer::file('content/does-not-exist.md');
    }
}
