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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/cap.php' => config_path('cap.php'),
            ], 'cap-config');
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
        Blade::directive('cap', function () {
            return "<?php echo '<cap-widget data-cap-api-endpoint=\"' . e(config('cap.endpoint')) . '\"></cap-widget>'; ?>";
        });

        Blade::directive('capScripts', function (string $expression) {
            if (empty(trim($expression))) {
                return "<?php echo '<script type=\"module\" src=\"https://cdn.jsdelivr.net/npm/cap-widget\"></script>'; ?>";
            }
            return "<?php echo '<script type=\"module\" nonce=\"' . e({$expression}) . '\" src=\"https://cdn.jsdelivr.net/npm/cap-widget\"></script>'; ?>";
        });
    }
}
