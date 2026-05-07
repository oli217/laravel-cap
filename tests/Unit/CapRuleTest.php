<?php

namespace LaravelCap\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use LaravelCap\Cap;
use LaravelCap\Rules\CapRule;
use LaravelCap\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CapRuleTest extends TestCase
{
    #[Test]
    public function it_passes_validation_when_token_is_valid(): void
    {
        Http::fake([
            'https://cap.test/site-key/siteverify' => Http::response(['success' => true]),
        ]);

        $validator = Validator::make(
            ['cap-token' => 'valid-token'],
            ['cap-token' => [new CapRule]],
        );

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function it_fails_validation_when_token_is_invalid(): void
    {
        Http::fake([
            'https://cap.test/site-key/siteverify' => Http::response(['success' => false]),
        ]);

        $validator = Validator::make(
            ['cap-token' => 'invalid-token'],
            ['cap-token' => [new CapRule]],
        );

        $this->assertTrue($validator->fails());
        $this->assertNotEmpty($validator->errors()->get('cap-token'));
    }

    #[Test]
    public function it_includes_attribute_name_in_error_message(): void
    {
        Http::fake([
            'https://cap.test/site-key/siteverify' => Http::response(['success' => false]),
        ]);

        $validator = Validator::make(
            ['cap-token' => 'invalid-token'],
            ['cap-token' => [new CapRule]],
        );

        $this->assertStringContainsString('cap-token', $validator->errors()->first('cap-token'));
    }

    #[Test]
    public function it_uses_injected_cap_instance(): void
    {
        $cap = $this->createStub(Cap::class);
        $cap->method('verify')->willReturn(true);

        $validator = Validator::make(
            ['cap-token' => 'any-token'],
            ['cap-token' => [new CapRule($cap)]],
        );

        $this->assertTrue($validator->passes());
    }
}
