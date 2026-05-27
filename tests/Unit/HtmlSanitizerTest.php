<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_it_keeps_editor_tables_and_removes_xss_vectors(): void
    {
        $html = '<script>alert(1)</script><table onclick="alert(1)" style="color:red; background-image:url(javascript:alert(1))"><tr><td align="center" style="text-align:center"><a href="javascript:alert(1)">link</a></td></tr></table>';

        $clean = HtmlSanitizer::clean($html);

        $this->assertStringContainsString('<table style="color: red">', $clean);
        $this->assertStringContainsString('<td align="center" style="text-align: center">', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('background-image', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function test_it_keeps_safe_links_and_adds_noopener_to_blank_targets(): void
    {
        $clean = HtmlSanitizer::clean('<a href="https://example.com" target="_blank">safe</a>');

        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringContainsString('target="_blank"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }
}
