<?php

namespace LaravelCap;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use LaravelCap\Middleware\VerifyCap;

class CapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/cap.php', 'cap');

        $this->app->singleton(Cap::class, function ($app) {
            return new Cap(
                http: $app->make(HttpFactory::class),
                config: $app->make('config')->get('cap'),
            );
        });
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'cap');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/cap.php' => config_path('cap.php'),
            ], 'cap-config');

            $this->publishes([
                __DIR__ . '/../resources/js/cap-widget.js'         => public_path('vendor/cap/cap-widget.js'),
                __DIR__ . '/../resources/css/cap-widget.css'        => public_path('vendor/cap/cap-widget.css'),
                __DIR__ . '/../resources/wasm/cap_wasm_bg.wasm'     => public_path('vendor/cap/cap_wasm_bg.wasm'),
            ], 'cap-assets');

            $this->publishes([
                __DIR__ . '/../resources/lang' => lang_path('vendor/cap'),
            ], 'cap-lang');
        }

        $this->registerMiddleware();
        $this->registerBladeDirectives();
    }

    private function registerMiddleware(): void
    {
        $this->app->make('router')->aliasMiddleware('cap.verify', VerifyCap::class);
    }

    private function registerBladeDirectives(): void
    {
        Blade::directive('cap', function (string $expression) {
            if (empty(trim($expression))) {
                return "<?php echo '<cap-widget data-cap-api-endpoint=\"' . e(config('cap.endpoint')) . '\"></cap-widget>'; ?>";
            }
            return "<?php echo '<cap-widget data-cap-api-endpoint=\"' . e(config('cap.endpoint')) . '\" data-cap-csp-nonce=\"' . e({$expression}) . '\"></cap-widget>'; ?>";
        });

        Blade::directive('capScripts', function (string $expression) {
            if (empty(trim($expression))) {
                return "<?php echo '<script>window.CAP_CUSTOM_WASM_URL=' . json_encode(asset('vendor/cap/cap_wasm_bg.wasm')) . '</script>' . '<script type=\"module\" src=\"' . e(asset('vendor/cap/cap-widget.js')) . '\"></script>'; ?>";
            }
            return "<?php echo '<script nonce=\"' . e({$expression}) . '\">window.CAP_CUSTOM_WASM_URL=' . json_encode(asset('vendor/cap/cap_wasm_bg.wasm')) . '</script>' . '<script type=\"module\" nonce=\"' . e({$expression}) . '\" src=\"' . e(asset('vendor/cap/cap-widget.js')) . '\"></script>'; ?>";
        });

        Blade::directive('capStyles', function () {
            return "<?php echo '<link rel=\"stylesheet\" href=\"' . e(asset('vendor/cap/cap-widget.css')) . '\">'; ?>";
        });

        Blade::directive('capConfig', function (string $expression) {
            if (empty(trim($expression))) {
                return "<?php echo '<script>'
                    . 'window.CAP_API_ENDPOINT=' . json_encode(config('cap.endpoint')) . ';'
                    . 'window.CAP_TOKEN_FIELD=' . json_encode(config('cap.token_field', 'cap-token')) . ';'
                    . '</script>'; ?>";
            }
            return "<?php echo '<script nonce=\"' . e({$expression}) . '\">'
                . 'window.CAP_API_ENDPOINT=' . json_encode(config('cap.endpoint')) . ';'
                . 'window.CAP_TOKEN_FIELD=' . json_encode(config('cap.token_field', 'cap-token')) . ';'
                . '</script>'; ?>";
        });
    }
}
