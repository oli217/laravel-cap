<?php

namespace LaravelCap\Tests\Unit;

use Illuminate\Support\Facades\Blade;
use LaravelCap\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CapBladeDirectivesTest extends TestCase
{
    #[Test]
    public function cap_directive_renders_widget_with_configured_endpoint(): void
    {
        $html = Blade::render('@cap');

        $this->assertStringContainsString('<cap-widget', $html);
        $this->assertStringContainsString('https://cap.test/site-key/', $html);
        $this->assertStringNotContainsString('data-cap-csp-nonce', $html);
    }

    #[Test]
    public function cap_directive_renders_widget_with_nonce(): void
    {
        $html = Blade::render('@cap("abc123")');

        $this->assertStringContainsString('data-cap-csp-nonce="abc123"', $html);
        $this->assertStringContainsString('data-cap-api-endpoint=', $html);
    }

    #[Test]
    public function cap_directive_escapes_nonce_value(): void
    {
        $html = Blade::render('@cap("<script>alert(1)</script>")');

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    #[Test]
    public function capScripts_directive_renders_script_tag_without_nonce(): void
    {
        $html = Blade::render('@capScripts');

        $this->assertStringContainsString('<script type="module"', $html);
        $this->assertStringContainsString('vendor/cap/cap-widget.js', $html);
        $this->assertStringNotContainsString('nonce', $html);
    }

    #[Test]
    public function capScripts_directive_renders_script_tag_with_nonce(): void
    {
        $html = Blade::render('@capScripts("abc123")');

        $this->assertStringContainsString('nonce="abc123"', $html);
        $this->assertStringContainsString('vendor/cap/cap-widget.js', $html);
    }

    #[Test]
    public function capScripts_directive_escapes_nonce_value(): void
    {
        $html = Blade::render('@capScripts("<script>alert(1)</script>")');

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    #[Test]
    public function capStyles_directive_renders_link_tag(): void
    {
        $html = Blade::render('@capStyles');

        $this->assertStringContainsString('<link rel="stylesheet"', $html);
        $this->assertStringContainsString('vendor/cap/cap-widget.css', $html);
    }
}
