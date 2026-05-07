<?php

namespace LaravelCap\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use LaravelCap\Cap;

class CapRule implements ValidationRule
{
    public function __construct(private readonly ?Cap $cap = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cap = $this->cap ?? app(Cap::class);

        if (! $cap->verify((string) $value)) {
            $fail(trans('cap::messages.validation_failed'));
        }
    }
}
