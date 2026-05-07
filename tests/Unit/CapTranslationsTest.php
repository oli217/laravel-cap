<?php

namespace LaravelCap\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use LaravelCap\Rules\CapRule;
use LaravelCap\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelCap\Middleware\VerifyCap;

class CapTranslationsTest extends TestCase
{
    #[Test]
    public function rule_uses_english_message_by_default(): void
    {
        Http::fake([
            'https://cap.test/site-key/siteverify' => Http::response(['success' => false]),
        ]);

        $validator = Validator::make(
            ['cap-token' => 'invalid'],
            ['cap-token' => [new CapRule]],
        );

        $this->assertStringContainsString(
            'verification failed',
            $validator->errors()->first('cap-token'),
        );
    }

    #[Test]
    public function rule_uses_french_message_when_locale_is_fr(): void
    {
        $this->app->setLocale('fr');

        Http::fake([
            'https://cap.test/site-key/siteverify' => Http::response(['success' => false]),
        ]);

        $validator = Validator::make(
            ['cap-token' => 'invalid'],
            ['cap-token' => [new CapRule]],
        );

        $this->assertStringContainsString(
            'échoué',
            $validator->errors()->first('cap-token'),
        );
    }

    #[Test]
    public function middleware_uses_english_message_by_default(): void
    {
        Http::fake([
            'https://cap.test/site-key/siteverify' => Http::response(['success' => false]),
        ]);

        $request = Request::create('/test', 'POST', ['cap-token' => 'invalid']);

        try {
            app(VerifyCap::class)->handle($request, fn($r) => new Response('ok'));
            $this->fail('HttpException attendue non levée.');
        } catch (HttpException $e) {
            $this->assertStringContainsString('Cap verification failed', $e->getMessage());
        }
    }

    #[Test]
    public function middleware_uses_french_message_when_locale_is_fr(): void
    {
        $this->app->setLocale('fr');

        Http::fake([
            'https://cap.test/site-key/siteverify' => Http::response(['success' => false]),
        ]);

        $request = Request::create('/test', 'POST', ['cap-token' => 'invalid']);

        try {
            app(VerifyCap::class)->handle($request, fn($r) => new Response('ok'));
            $this->fail('HttpException attendue non levée.');
        } catch (HttpException $e) {
            $this->assertStringContainsString('échoué', $e->getMessage());
        }
    }
}
