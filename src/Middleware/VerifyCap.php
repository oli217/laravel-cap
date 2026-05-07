<?php

namespace LaravelCap\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelCap\Cap;
use Symfony\Component\HttpFoundation\Response;

class VerifyCap
{
    public function __construct(private readonly Cap $cap) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) $request->input(config('cap.token_field', 'cap-token'), '');

        if (! $this->cap->verify($token)) {
            abort(422, trans('cap::messages.middleware_failed'));
        }

        return $next($request);
    }
}
