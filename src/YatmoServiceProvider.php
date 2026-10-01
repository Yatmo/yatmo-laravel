<?php

declare(strict_types=1);

namespace Yatmo\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Yatmo\Client;
use Yatmo\Laravel\View\Components\YatmoMap;
use Yatmo\Laravel\View\Components\YatmoPois;
use Yatmo\Laravel\View\Components\YatmoText;
use Yatmo\Transport\TransportInterface;

final class YatmoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/yatmo.php', 'yatmo');

        // One Yatmo\Client per request, configured from config/yatmo.php; the language follows the app locale by default.
        $this->app->singleton(Client::class, function ($app): Client {
            $config = $app['config']['yatmo'];
            $key = (string) ($config['key'] ?? '');
            if ($key === '') {
                throw new \RuntimeException('Yatmo: set YATMO_KEY in .env (your backend key), see config/yatmo.php');
            }
            $language = $config['language'] ?: $app->getLocale();
            $transport = $app->bound(TransportInterface::class) ? $app->make(TransportInterface::class) : null;

            return new Client($key, (string) $config['country'], strtoupper(substr((string) $language, 0, 3)), $transport, (int) ($config['timeout'] ?? 15));
        });

        $this->app->singleton(Yatmo::class, fn ($app) => new Yatmo($app->make(Client::class), $app['config']['yatmo'], $app['cache']));
        $this->app->alias(Yatmo::class, 'yatmo');
    }

    public function boot(): void
    {
        $this->publishes([__DIR__ . '/../config/yatmo.php' => $this->app->configPath('yatmo.php')], 'yatmo-config');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'yatmo');
        $this->publishes([__DIR__ . '/../resources/views' => $this->app->resourcePath('views/vendor/yatmo')], 'yatmo-views');

        Blade::component('yatmo-map', YatmoMap::class);
        Blade::component('yatmo-text', YatmoText::class);
        Blade::component('yatmo-pois', YatmoPois::class);
    }
}
