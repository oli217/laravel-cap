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
```

| Variable          | Description                                                                 | Default     |
|-------------------|-----------------------------------------------------------------------------|-------------|
| `CAP_ENDPOINT`    | Full URL of your Cap instance including the site key (trailing slash required) | —           |
| `CAP_SECRET`      | Secret key from your Cap dashboard                                          | —           |
| `CAP_TOKEN_FIELD` | Name of the hidden field injected by the Cap widget                         | `cap-token` |
| `CAP_TIMEOUT`     | HTTP timeout in seconds for the `/siteverify` request                       | `5`         |

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
