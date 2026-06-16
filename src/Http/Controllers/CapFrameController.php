<?php

namespace LaravelCap\Http\Controllers;

use Illuminate\Http\Response;

class CapFrameController
{
    public function __invoke(): Response
    {
        return response()
            ->view('cap::frame')
            ->withHeaders([
                'Content-Security-Policy' =>
                    "default-src 'none'; " .
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval' 'wasm-unsafe-eval' blob:; " .
                    "style-src 'self' 'unsafe-inline'; " .
                    "connect-src *; " .
                    "worker-src blob:; " .
                    "frame-ancestors 'self';",
                'X-Frame-Options' => 'SAMEORIGIN',
                'Cache-Control'   => 'no-store',
            ]);
    }
}
