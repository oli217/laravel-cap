# laravel-cap

A Laravel wrapper for [Cap](https://github.com/tiagozip/cap) — the self-hosted, privacy-friendly CAPTCHA alternative based on Proof-of-Work.

Cap works without tracking, cookies, or third-party services. This package integrates server-side token verification into Laravel through a service, a facade, a middleware, a validation rule, and Blade directives.

## Requirements

- PHP **^8.2**
- Laravel **11, 12, or 13**
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

Publish the JS, CSS, and WASM assets (required for `@capScripts` and `@capStyles`):

```bash
php artisan vendor:publish --tag=cap-assets
```

This publishes the following files to `public/vendor/cap/`:

| File | Description |
|------|-------------|
| `cap-widget.js` | Cap widget (custom element + programmatic API) |
| `cap-widget.css` | Default widget styles |
| `cap_wasm_bg.wasm` | WebAssembly module for proof-of-work (served locally, no CDN required) |

Publish the translation files (optional — to override messages):

```bash
php artisan vendor:publish --tag=cap-lang
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

| Variable          | Description                                                                    | Default     |
|-------------------|--------------------------------------------------------------------------------|-------------|
| `CAP_ENDPOINT`    | Full URL of your Cap instance including the site key (trailing slash required) | —           |
| `CAP_SECRET`      | Secret key from your Cap dashboard                                             | —           |
| `CAP_TOKEN_FIELD` | Name of the hidden field injected by the Cap widget                            | `cap-token` |
| `CAP_TIMEOUT`     | HTTP timeout in seconds for the `/siteverify` request                          | `5`         |
| `CAP_FAIL_OPEN`   | When `true`, let requests through on network/server errors (see below)         | `false`     |

### Fail-open mode

By default, any communication error with the Cap instance (network failure, timeout, HTTP 5xx) blocks the request, just like an invalid token would.

Setting `CAP_FAIL_OPEN=true` inverts this: communication errors silently pass, so a Cap outage does not take your forms down with it.

**An explicitly invalid token (`success: false`) is always rejected regardless of this setting.** Fail-open only covers infrastructure failures, not verification failures.

## Translations

Server-side messages (validation rule, middleware) are translatable. English and French are included out of the box.

To override or add a language, publish the translation files and edit `lang/vendor/cap/{locale}/messages.php`:

```bash
php artisan vendor:publish --tag=cap-lang
```

```php
// lang/vendor/cap/fr/messages.php
return [
    'validation_failed' => 'La vérification :attribute a échoué. Veuillez réessayer.',
    'middleware_failed'  => 'La vérification Cap a échoué.',
];
```

Laravel selects the right file automatically based on `App::getLocale()`.

## Widget styling

Edit `public/vendor/cap/cap-widget.css` to override the CSS custom properties exposed by the widget:

```css
cap-widget {
    --cap-color-primary:  #6366f1;
    --cap-color-success:  #22c55e;
    --cap-border-radius:  0.5rem;
    --cap-font-family:    inherit;
    /* ... */
}
```

## Usage

### Blade directives

| Directive | Description |
|-----------|-------------|
| `@cap` | Renders `<cap-widget>` with the configured endpoint |
| `@capScripts` | Injects `window.CAP_CUSTOM_WASM_URL` + `<script type="module">` for the widget |
| `@capStyles` | `<link>` loading the theme from `public/vendor/cap/cap-widget.css` |
| `@capConfig` | `<script>` exposing `window.CAP_API_ENDPOINT` and `window.CAP_TOKEN_FIELD` |

#### Standard widget mode

Include the Cap widget in any Blade form:

```blade
@capStyles
@capScripts

<form method="POST" action="/contact">
    @csrf
    @cap
    <button type="submit">Submit</button>
</form>
```

The widget automatically injects a hidden `cap-token` field (or the value of `CAP_TOKEN_FIELD`) into its parent form upon successful verification.

`@capScripts` always injects `window.CAP_CUSTOM_WASM_URL` pointing to the locally published WASM, so no external CDN is contacted at runtime.

#### Programmatic mode

Use `@capConfig` to expose the endpoint to JavaScript, then instantiate `Cap` directly without rendering a visible widget:

```blade
@capConfig
@capScripts

<form method="POST" action="/contact">
    @csrf
    <input type="hidden" name="cap-token" id="cap-token">
    <button type="submit" id="submit-btn">Submit</button>
</form>

<script type="module">
document.getElementById('submit-btn').addEventListener('click', async (e) => {
    e.preventDefault();

    const cap = new Cap({ apiEndpoint: window.CAP_API_ENDPOINT });
    const { token } = await cap.solve();

    document.getElementById('cap-token').value = token;
    e.target.closest('form').submit();
});
</script>
```

`Cap` creates a hidden `cap-widget` element in the background and exposes a `solve()` method that returns `{ token }`. No visible widget is rendered.

`window.CAP_API_ENDPOINT` and `window.CAP_TOKEN_FIELD` are set by `@capConfig` from your PHP configuration, so you never need to hard-code the endpoint in JavaScript.

#### CSP nonce support

All directives accept an optional nonce for strict Content Security Policies:

```blade
@capConfig(Vite::cspNonce())
@capScripts(Vite::cspNonce())
@cap(Vite::cspNonce())
```

`@cap` passes the nonce as `data-cap-csp-nonce` on the widget element, which Cap uses internally for its workers and inline scripts.

#### CSP headers

Cap's widget relies on Web Workers and WebAssembly. A strict CSP must account for this:

```
Content-Security-Policy:
  script-src 'nonce-{nonce}' 'strict-dynamic';
  worker-src blob:;
  wasm-unsafe-eval;
  connect-src 'self';
```

`worker-src blob:` — required because the widget spawns workers via `Blob` URLs.
`wasm-unsafe-eval` — required for the WebAssembly hash computation.
`connect-src 'self'` — sufficient for WASM since assets are served locally after `vendor:publish`.

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
