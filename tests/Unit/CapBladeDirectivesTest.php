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
    }

    #[Test]
    public function capScripts_directive_renders_script_tag_without_nonce(): void
    {
        $html = Blade::render('@capScripts');

        $this->assertStringContainsString('<script type="module"', $html);
        $this->assertStringContainsString('src="https://cdn.jsdelivr.net/npm/cap-widget"', $html);
        $this->assertStringNotContainsString('nonce', $html);
    }

    #[Test]
    public function capScripts_directive_renders_script_tag_with_nonce(): void
    {
        $html = Blade::render('@capScripts("abc123")');

        $this->assertStringContainsString('nonce="abc123"', $html);
        $this->assertStringContainsString('src="https://cdn.jsdelivr.net/npm/cap-widget"', $html);
    }

    #[Test]
    public function capScripts_directive_escapes_nonce_value(): void
    {
        $html = Blade::render('@capScripts("<script>alert(1)</script>")');

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
