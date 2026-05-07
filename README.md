# laravel-cap

A Laravel wrapper for [Cap](https://github.com/tiagozip/cap) — the self-hosted, privacy-friendly CAPTCHA alternative based on Proof-of-Work.

Cap works without tracking, cookies, or third-party services. This package integrates server-side token verification into Laravel through a service, a facade, a middleware, a validation rule, and Blade directives.

## Requirements

- PHP **^8.2**
- Laravel **11 or 12**
- A running [Cap instance](https://trycap.dev/guide/) (self-hosted via Docker)

## Installation

```bash
composer require oliweb/laravel-cap
```

The service provider and facade are registered automatically via Laravel's package auto-discovery.

Publish the configuration file:

```bash
php artisan vendor:publish --tag=cap-config
```

## Configuration

Add the following variables to your `.env` file:

```env
CAP_ENDPOINT=https://cap.example.com/your-site-key/
CAP_SECRET=your-secret-key
CAP_TOKEN_FIELD=cap-token
CAP_TIMEOUT=5
CAP_FAIL_OPEN=false
```

| Variable          | Description                                                                 | Default     |
|-------------------|-----------------------------------------------------------------------------|-------------|
| `CAP_ENDPOINT`    | Full URL of your Cap instance including the site key (trailing slash required) | —           |
| `CAP_SECRET`      | Secret key from your Cap dashboard                                          | —           |
| `CAP_TOKEN_FIELD` | Name of the hidden field injected by the Cap widget                         | `cap-token` |
| `CAP_TIMEOUT`     | HTTP timeout in seconds for the `/siteverify` request                       | `5`         |
| `CAP_FAIL_OPEN`   | When `true`, let requests through on network/server errors (see below)      | `false`     |

### Fail-open mode

By default, any communication error with the Cap instance (network failure, timeout, HTTP 5xx) blocks the request, just like an invalid token would.

Setting `CAP_FAIL_OPEN=true` inverts this: communication errors silently pass, so a Cap outage does not take your forms down with it.

**An explicitly invalid token (`success: false`) is always rejected regardless of this setting.** Fail-open only covers infrastructure failures, not verification failures.

## Usage

### Blade directives

Include the Cap widget and its script in any Blade form:

```blade
@capScripts

<form method="POST" action="/contact">
    @csrf
    @cap
    <button type="submit">Submit</button>
</form>
```

`@cap` renders the `<cap-widget>` element with the configured endpoint.
`@capScripts` renders the `<script>` tag loading the widget from jsDelivr.

The widget automatically injects a hidden `cap-token` field into its parent form upon successful verification.

#### CSP nonce support

Both directives accept an optional nonce for strict Content Security Policies:

```blade
{{-- Laravel Vite --}}
@capScripts(Vite::cspNonce())
@cap(Vite::cspNonce())

{{-- Spatie CSP or custom nonce --}}
@capScripts($nonce)
@cap($nonce)
```

`@cap` passes the nonce as `data-cap-csp-nonce` on the widget element, which Cap uses internally for its workers and inline scripts.
`@capScripts` passes the nonce as the standard `nonce` attribute on the `<script>` tag.

#### CSP headers

Cap's widget relies on Web Workers and WebAssembly for the Proof-of-Work computation. A strict CSP must account for this beyond the script nonce:

```
Content-Security-Policy:
  script-src 'nonce-{nonce}' 'strict-dynamic';
  worker-src blob:;
  wasm-unsafe-eval;
```

`worker-src blob:` is required because the widget spawns workers via `Blob` URLs.
`wasm-unsafe-eval` is required for the WebAssembly hash computation.

### Middleware

Protect any route by applying the `cap.verify` middleware:

```php
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('cap.verify');
```

Returns HTTP `422` with the message `Cap verification failed.` if the token is missing or invalid.

### Validation rule

Use `CapRule` inside a Form Request or an inline validator:

```php
use LaravelCap\Rules\CapRule;

public function rules(): array
{
    return [
        'cap-token' => ['required', new CapRule],
        // other fields...
    ];
}
```

### Facade

```php
use LaravelCap\Facades\Cap;

if (Cap::verify($request->input('cap-token'))) {
    // token is valid
}
```

### Service (dependency injection)

```php
use LaravelCap\Cap;

class ContactController extends Controller
{
    public function __construct(private readonly Cap $cap) {}

    public function store(Request $request): RedirectResponse
    {
        $this->cap->verifyOrFail($request->input('cap-token'));
        // ...
    }
}
```

`verifyOrFail()` throws a `CapVerificationException` if the token is invalid.

## Testing

```bash
composer install
./vendor/bin/phpunit
```

## License

MIT
