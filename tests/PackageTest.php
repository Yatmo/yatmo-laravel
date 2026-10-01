<?php

declare(strict_types=1);

namespace Yatmo\Laravel\Tests;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase;
use Yatmo\Client;
use Yatmo\Laravel\Facades\Yatmo;
use Yatmo\Laravel\YatmoServiceProvider;
use Yatmo\Transport\TransportInterface;

/** Hermetic: the Yatmo transport is faked in the container, the API is never called. */
final class PackageTest extends TestCase
{
    private const TEXT = ['Paragraphs' => [
        ['IconId' => 'shopping', 'Title' => ['FR' => 'Commerces', 'EN' => 'Shops'], 'TitleBis' => ['FR' => 'Commerces près de la Rue de la Loi', 'EN' => 'Shops near Rue de la Loi'], 'TitleTer' => ['FR' => 'Commerces à Bruxelles', 'EN' => 'Shops in Brussels'],
            'Sentences' => [['FR' => 'Un [STRONG]Carrefour[/STRONG] à 3 minutes.', 'EN' => 'A [STRONG]Carrefour[/STRONG] 3 minutes away.']], 'List' => []],
        ['IconId' => 'education', 'Title' => ['FR' => 'Écoles', 'EN' => 'Schools'], 'Sentences' => [['FR' => 'Deux écoles.', 'EN' => 'Two schools.']], 'List' => []],
    ]];
    private const SUMMARY = ['AvailableCategoriesAroundPosition' => [
        ['ct' => 1, 'l' => 'Education', 'sc' => [['st' => 4, 'l' => 'Nurseries', 'lb' => 'Nursery', 'd' => [['n' => 'Little Bears', 'la' => 50.84, 'lo' => 4.36, 'td' => [['hti' => true, 'tm' => 2, 'ptdsl' => '300 m', 'ttsl' => "4'"]]]]]]],
        ['ct' => 3, 'l' => 'Shopping', 'sc' => [['st' => 9, 'l' => 'Supermarkets', 'lb' => 'Supermarket', 'd' => [['n' => 'Carrefour', 'la' => 50.84, 'lo' => 4.36, 'td' => []], ['n' => 'Delhaize', 'la' => 50.84, 'lo' => 4.36, 'td' => []]]]]],
    ], 'CloseCities' => [], 'PlaceInformation' => null];

    private FakeTransport $transport;

    protected function getPackageProviders($app): array
    {
        return [YatmoServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['Yatmo' => Yatmo::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('yatmo.key', 'backend-key');
        $app['config']->set('yatmo.frontend_key', 'frontend-key');
        $app['config']->set('yatmo.country', 'BE');
        $app['config']->set('yatmo.language', 'FR');
        $app['config']->set('cache.default', 'array');
        $this->transport = new FakeTransport(['/Summary/text?' => self::TEXT, '/summary?' => self::SUMMARY]);
        $app->instance(TransportInterface::class, $this->transport);
    }

    public function testClientAndFacade(): void
    {
        $client = $this->app->make(Client::class);
        self::assertSame('FR', $client->language());
        self::assertSame('https://be.yatmo.com/summary?language=FR', $client->url('summary'));
        self::assertSame('BE', Yatmo::country());
        self::assertSame('frontend-key', Yatmo::frontendKey());
        self::assertSame($client, Yatmo::client());
    }

    public function testTextIsCachedAndRendered(): void
    {
        $html = Yatmo::textHtml(50.8461, 4.3664);
        self::assertSame('<div class="yatmo-text"><h3>Commerces près de la Rue de la Loi</h3><p>Un <strong>Carrefour</strong> à 3 minutes.</p><h3>Écoles</h3><p>Deux écoles.</p></div>', $html);
        self::assertSame('backend-key', $this->transport->calls[0]['headers']['LicenseKey']);
        Yatmo::text(50.8461, 4.3664, 'EN');   // same cache entry, every language is cached
        self::assertCount(1, $this->transport->calls);
        self::assertSame('Shops near Rue de la Loi', Yatmo::text(50.8461, 4.3664, 'EN')->paragraphs[0]->titleStreet);
        Yatmo::forget(50.8461, 4.3664);
        Yatmo::text(50.8461, 4.3664);
        self::assertCount(2, $this->transport->calls);
    }

    public function testCacheCanBeDisabled(): void
    {
        config()->set('yatmo.cache_days', 0);
        Yatmo::summary(50.8461, 4.3664);
        Yatmo::summary(50.8461, 4.3664);
        self::assertCount(2, $this->transport->calls);
        self::assertNull(Cache::get('yatmo|summary|BE|50.846100|4.366400|FR'));
    }

    public function testCacheHoldsPlainArraysOnly(): void
    {
        // Laravel 13 cache stores unserialize an allow-list of classes only: models never go in the cache.
        Yatmo::summary(50.8461, 4.3664);
        Yatmo::text(50.8461, 4.3664);
        self::assertIsArray(Cache::get('yatmo|summary|BE|50.846100|4.366400|FR'));
        self::assertIsArray(Cache::get('yatmo|text|BE|50.846100|4.366400'));
        self::assertSame('Carrefour', Yatmo::summary(50.8461, 4.3664)->categories[1]->subCategories[0]->places[0]->name);
        self::assertCount(2, $this->transport->calls);
    }

    public function testBladeComponents(): void
    {
        $html = Blade::render('<x-yatmo-map :latitude="50.8461" :longitude="4.3664" mode="map-top" marker="circle" :circle-radius="300" isochrone="right" :rounded="8" height="400" class="mine" />');
        self::assertStringContainsString('src="https://map.yatmo.com/plugin.html?licenseKey=frontend-key&amp;country=BE&amp;language=FR&amp;latitude=50.8461000&amp;longitude=4.3664000&amp;mode=map-top&amp;zoom=15&amp;marker=circle&amp;circleRadiusInMeters=300&amp;rounded=8px&amp;isochrone=right"', $html);
        self::assertStringContainsString('class="yatmo-map mine"', $html);
        self::assertStringContainsString('height:400px', $html);

        $text = Blade::render('<x-yatmo-text :latitude="50.8461" :longitude="4.3664" heading="h2" titles="city" paragraphs="education" />');
        self::assertStringContainsString('<div class="yatmo-text-wrapper"><div class="yatmo-text"><h2>Écoles</h2><p>Deux écoles.</p></div></div>', $text);

        $pois = Blade::render('<x-yatmo-pois :latitude="50.8461" :longitude="4.3664" categories="shopping" :limit="2" heading="h4" />');
        self::assertStringContainsString('<h4>Supermarkets</h4><ul>', $pois);
        self::assertStringContainsString('<li>Carrefour</li>', $pois);
        self::assertStringContainsString('<li>Delhaize</li>', $pois);
        self::assertStringNotContainsString('Nursery', $pois);

        $walk = Blade::render('<x-yatmo-pois :latitude="50.8461" :longitude="4.3664" categories="education" />');
        self::assertStringContainsString("<li>Little Bears (300 m, 4&#039;)</li>", $walk);
    }

    public function testComponentsRenderNothingWhenYatmoFails(): void
    {
        $this->app->instance(TransportInterface::class, new FakeTransport([]));
        $this->app->forgetInstance(Client::class);
        $this->app->forgetInstance(\Yatmo\Laravel\Yatmo::class);
        config()->set('yatmo.cache_days', 0);
        self::assertSame('', trim(Blade::render('<x-yatmo-text :latitude="50.8461" :longitude="4.3664" />')));
        self::assertSame('', trim(Blade::render('<x-yatmo-pois :latitude="50.8461" :longitude="4.3664" />')));
    }

    public function testMissingKeyIsExplained(): void
    {
        config()->set('yatmo.key', null);
        $this->app->forgetInstance(Client::class);
        $this->expectExceptionMessage('YATMO_KEY');
        $this->app->make(Client::class);
    }
}
