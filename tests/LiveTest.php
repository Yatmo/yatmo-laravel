<?php

declare(strict_types=1);

namespace Yatmo\Laravel\Tests;

use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Group;
use Yatmo\Laravel\Facades\Yatmo;
use Yatmo\Laravel\YatmoServiceProvider;

/** Calls the real API with the key of YATMO_LIVE_KEY; skipped without it. `composer test:live`. */
#[Group('live')]
final class LiveTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [YatmoServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $key = getenv('YATMO_LIVE_KEY') ?: '';
        $app['config']->set('yatmo.key', $key);
        $app['config']->set('yatmo.frontend_key', $key);
        $app['config']->set('yatmo.country', 'BE');
        $app['config']->set('yatmo.language', 'FR');
        $app['config']->set('cache.default', 'array');
    }

    protected function setUp(): void
    {
        parent::setUp();
        if ((getenv('YATMO_LIVE_KEY') ?: '') === '') {
            self::markTestSkipped('YATMO_LIVE_KEY is not set');
        }
    }

    public function testListingPageAgainstTheApi(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-yatmo-map :latitude="50.8461" :longitude="4.3664" marker="circle" isochrone="right" />
            <x-yatmo-pois :latitude="50.8461" :longitude="4.3664" categories="education,transport,shopping" />
            <x-yatmo-text :latitude="50.8461" :longitude="4.3664" />
            BLADE);
        self::assertStringContainsString('map.yatmo.com/plugin.html?licenseKey=', $html);
        self::assertStringContainsString('<div class="yatmo-pois">', $html);
        self::assertStringContainsString('<div class="yatmo-text"><h3>', $html);
        self::assertStringContainsString('<strong>', $html);
        self::assertGreaterThan(5, \count(Yatmo::text(50.8461, 4.3664)->paragraphs));
        self::assertNotEmpty(Yatmo::scores(50.8461, 4.3664)->scores);
        fwrite(STDERR, "\n" . strip_tags(str_replace(['</h3>', '</li>', '</p>'], "\n", $html)) . "\n");
    }
}
