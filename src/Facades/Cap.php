<?php

namespace LaravelCap\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool verify(string $token)
 * @method static void verifyOrFail(string $token)
 *
 * @see \LaravelCap\Cap
 */
class Cap extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LaravelCap\Cap::class;
    }
}
