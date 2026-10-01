# yatmo/laravel

Add real estate maps, points of interest and neighbourhood data to Laravel applications.
`yatmo/laravel` is the official Laravel integration of [Yatmo](https://yatmo.com): Blade components for the
interactive property map, the nearest schools, nurseries, supermarkets and public transport with real travel
times, and a written neighbourhood text rendered on the server and cached, so search engines index it with the
listing. One `.env` line per key, a `Yatmo` facade or an injectable service for the rest of the API (scores,
isochrones, routes, geocoding, static maps), in 25 countries and 23 languages. Laravel 10 to 13.

```bash
composer require yatmo/laravel
```

```blade
<x-yatmo-map :latitude="$listing->latitude" :longitude="$listing->longitude" marker="circle" isochrone="right" />
<x-yatmo-pois :latitude="$listing->latitude" :longitude="$listing->longitude" categories="education,transport,shopping" />
<x-yatmo-text :latitude="$listing->latitude" :longitude="$listing->longitude" />
```

<p align="center">
  <img src="https://raw.githubusercontent.com/Yatmo/yatmo-laravel/main/docs/screenshot.png" width="720" alt="A Laravel property page with the Yatmo map, the nearest places and the neighbourhood text">
</p>

## Install in 5 minutes

1. Get a Yatmo licence at [yatmo.com](https://yatmo.com): it comes with a **backend** key (server to server,
   kept secret) and a **frontend** key (locked to your domains, embedded in the map)
   ([keys explained](https://documentation.yatmo.com/license)).
2. `composer require yatmo/laravel`. The service provider and the `Yatmo` facade are discovered automatically.
3. Add the keys and the country of your properties to `.env`:

   ```dotenv
   YATMO_KEY=your_backend_key
   YATMO_FRONTEND_KEY=your_frontend_key
   YATMO_COUNTRY=BE
   YATMO_LANGUAGE=FR    # optional, follows the application locale when absent
   ```

4. Drop the three components into the property view. That is all: the text and the places are fetched once per
   property and cached for 30 days in your default cache store.

Full example: [examples/](examples/) holds the routes and the views of the demo app in the screenshot (home grid,
listing page with meta description taken from the neighbourhood text, discreet listing with a circle and city-only
titles); copy them into a fresh `laravel new` application.
`php artisan vendor:publish --tag=yatmo-config` copies `config/yatmo.php` if you want to change the defaults
(cache duration and store, timeout, map defaults).

## Blade components

### `<x-yatmo-map>`: the interactive neighbourhood map

The [Yatmo iframe plugin](https://documentation.yatmo.com/plugins/iframe): points of interest by category, travel
times on foot, by bike, car and public transport, isochrones, a summary panel, 7 map styles. Built with the
frontend key, no JavaScript to load.

```blade
<x-yatmo-map :latitude="50.8461" :longitude="4.3664"
             mode="overlay" :zoom="15" :map-style="1" accent-color="#1f6f8b"
             marker="circle" :circle-radius="300" isochrone="right" :rounded="12"
             height="520" class="my-map" />
```

| Attribute | Default | Meaning |
|-----------|---------|---------|
| `latitude`, `longitude` | required | Position of the property |
| `mode` | `overlay` | `overlay`, `map-top`, `map-left`, `map-only`... |
| `zoom` | `15` | Initial zoom |
| `map-style` | | 1 Liberty, 2 Basic, 3 Bright, 4 3D, 5 Positron, 6 Dark, 7 Liberty Stonehedge |
| `accent-color` | | Colour of the interface |
| `marker` | | `pin` or `circle` (discreet listing); `circle-radius` in metres |
| `isochrone` | | `right`, `left`... position of the isochrone control |
| `rounded` | | Corner radius in pixels |
| `route-from` | | `lat,lng` of a place to draw the route to |
| `height` | `560px` | CSS height, a bare number is pixels |
| `language`, `country`, `key` | from config | Overrides per component |
| `:extra` | | Array of any other iframe parameter |

Any other attribute (`class`, `id`, `data-*`) goes on the `<iframe>`. Defaults live in `config('yatmo.map')`.

### `<x-yatmo-text>`: the neighbourhood text, indexable

The written description of the surroundings in the language of the page (shops, schools, transport, leisure,
each as a heading and a paragraph with the key places in `<strong>`), produced by Yatmo, rendered on the
server by `yatmo/yatmo-php` and cached. Renders nothing, and logs the exception, when the API cannot be reached:
a listing page never breaks because of it.

```blade
<x-yatmo-text :latitude="50.8461" :longitude="4.3664" heading="h3" titles="street-city" paragraphs="shopping,education" />
```

| Attribute | Default | Meaning |
|-----------|---------|---------|
| `heading` | `h3` | Heading tag, empty for paragraphs only |
| `titles` | `street-city` | First title mentions the street, second the city. `city`: never the street (discreet listing). `generic`: neither |
| `paragraphs` | all | Comma separated icon ids to keep (`shopping`, `education`, `transport`, `tourism`, `health`...) |
| `strong` | `true` | Keep the `<strong>` emphasis |
| `language` | from config | One text per language is cached from the same API call |

### `<x-yatmo-pois>`: the nearest places with travel times

Nurseries, schools, supermarkets, bus and train stops... grouped by sub-category with the distance and the
travel time of the chosen mode, as headings and lists you style with your own CSS.

```blade
<x-yatmo-pois :latitude="50.8461" :longitude="4.3664" categories="education,transport,shopping" mode="walking" :limit="2" heading="h4" />
```

| Attribute | Default | Meaning |
|-----------|---------|---------|
| `categories` | all | `education`, `transport`, `shopping`, `tourism` |
| `mode` | `walking` | `walking`, `bicycling`, `driving`, `transit` |
| `limit` | `1` | Places per sub-category |
| `heading` | `h3` | Heading tag of each group |

## Facade and service

`Yatmo\Laravel\Yatmo` is a singleton (facade `Yatmo`, or inject it in a controller). It wraps the configured
[`Yatmo\Client`](https://github.com/Yatmo/yatmo-php) and caches what a property page needs.

```php
use Yatmo\Laravel\Facades\Yatmo;
use Yatmo\TravelMode;

$text = Yatmo::text($lat, $lng);                        // Yatmo\Model\SummaryText, cached (every language)
$html = Yatmo::textHtml($lat, $lng, ['titles' => 'city']); // ready for {!! !!}
$summary = Yatmo::summary($lat, $lng);                  // nearest places by category, cached
$scores = Yatmo::scores($lat, $lng);                    // 0 to 10 per category, cached
Yatmo::forget($lat, $lng);                              // drop the cache of a position

$client = Yatmo::client();                              // the rest of the API, not cached
$client->isochrones($lat, $lng, TravelMode::Walking);
$client->route($lat, $lng, 50.8503, 4.3517, TravelMode::Transit);
$client->geocode('Rue de la Loi 16, Bruxelles');
$client->enrichment($lat, $lng);                        // ready-made listing attributes
$client->staticMapUrl($lat, $lng, ['width' => 600, 'height' => 400]);
```

Meta description from the text, in a controller:

```php
$description = Str::limit(Yatmo::text($listing->latitude, $listing->longitude)->paragraphs[0]->text(), 155);
```

Need another HTTP layer (Guzzle, a proxy)? Bind `Yatmo\Transport\TransportInterface` in the container and the
client uses it.

## Configuration

`config/yatmo.php`, every value from `.env`:

| Key | `.env` | Default |
|-----|--------|---------|
| `key` | `YATMO_KEY` | backend key, required |
| `frontend_key` | `YATMO_FRONTEND_KEY` | frontend key, for the map |
| `country` | `YATMO_COUNTRY` | `BE` |
| `language` | `YATMO_LANGUAGE` | the application locale |
| `cache_days` | `YATMO_CACHE_DAYS` | `30`, `0` disables the cache |
| `cache_store` | `YATMO_CACHE_STORE` | the default store |
| `timeout` | `YATMO_TIMEOUT` | `15` seconds |
| `map` | | defaults of `<x-yatmo-map>` |

Countries: BE, FR, NL, LU, CH, DE, IT, ES, PT, IE, UK, AT, CA, GR, MA, AU, HR, MT, SI, RS, CY, BA, ME, BG, AL.
A multi-country site creates one client per country: `new \Yatmo\Client(config('yatmo.key'), 'FR', 'FR')`.

## Why

A listing page that describes the neighbourhood, with real travel times to real places, is more useful to the
visitor and brings long-tail search traffic that a map alone never brings. Yatmo computes the text and the
places from its own data (official registers, transport feeds, brand networks) and routing engines, and your
Laravel app serves them as plain HTML.

## Tests

```bash
composer test                                   # hermetic, the API is faked
YATMO_LIVE_KEY=your_backend_key composer test:live   # one real call per endpoint
```

## Links

- [Yatmo](https://yatmo.com) and its [documentation](https://documentation.yatmo.com/api)
- [`yatmo/yatmo-php`](https://github.com/Yatmo/yatmo-php), the PHP client this package builds on
- [WordPress plugin](https://wordpress.org/plugins/yatmo-map/), [Odoo module](https://apps.odoo.com/apps/modules/20.0/yatmo_map),
  [npm packages](https://www.npmjs.com/org/yatmo) (JavaScript, React, Vue, web components), [Next.js starter](https://github.com/Yatmo/yatmo-nextjs-starter)
- [More examples](https://github.com/Yatmo/yatmo-examples)

MIT licence. Yatmo is a paid service for real estate portals, agency networks and developers; a licence key is required.
