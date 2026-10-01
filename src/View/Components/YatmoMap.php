<?php

declare(strict_types=1);

namespace Yatmo\Laravel\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Yatmo\Laravel\Yatmo;

/**
 * `<x-yatmo-map :latitude="$listing->lat" :longitude="$listing->lng" marker="circle" isochrone="right" />`
 *
 * The Yatmo neighbourhood map (points of interest, travel times, summary, isochrones, routes) as the
 * iframe plugin, built with the frontend key of the configuration. Every parameter of
 * https://documentation.yatmo.com/plugins/iframe is an attribute; `config('yatmo.map')` holds the defaults.
 */
final class YatmoMap extends Component
{
    public const IFRAME_URL = 'https://map.yatmo.com/plugin.html';

    /** @param array<string, string> $extra Any other iframe parameter */
    public function __construct(
        private readonly Yatmo $yatmo,
        public float $latitude,
        public float $longitude,
        public ?string $mode = null,
        public ?int $zoom = null,
        public ?int $mapStyle = null,
        public ?string $accentColor = null,
        public ?string $marker = null,
        public ?int $circleRadius = null,
        public ?int $rounded = null,
        public ?string $isochrone = null,
        public ?string $routeFrom = null,
        public ?string $userId = null,
        public ?string $language = null,
        public ?string $country = null,
        public ?string $key = null,
        public string|int|null $height = null,
        public string $title = 'Map and neighbourhood of the property',
        public array $extra = [],
    ) {
    }

    /** The iframe URL for the attributes and the configuration. */
    public function src(): string
    {
        $defaults = (array) config('yatmo.map', []);
        $params = [
            'licenseKey' => $this->key ?? $this->yatmo->frontendKey(),
            'country' => strtoupper($this->country ?? $this->yatmo->country()),
            'language' => strtoupper($this->language ?? $this->yatmo->client()->language()),
            'latitude' => number_format($this->latitude, 7, '.', ''),
            'longitude' => number_format($this->longitude, 7, '.', ''),
            'mode' => $this->mode ?? $defaults['mode'] ?? 'overlay',
            'zoom' => (string) ($this->zoom ?? $defaults['zoom'] ?? 15),
        ];
        $optional = [
            'mapStyle' => $this->mapStyle ?? $defaults['map_style'] ?? null,
            'accentColor' => $this->accentColor ?? $defaults['accent_color'] ?? null,
            'marker' => $this->marker ?? $defaults['marker'] ?? null,
            'circleRadiusInMeters' => $this->circleRadius ?? $defaults['circle_radius'] ?? null,
            'rounded' => ($this->rounded ?? $defaults['rounded'] ?? null) ? ($this->rounded ?? $defaults['rounded']) . 'px' : null,
            'isochrone' => $this->isochrone ?? $defaults['isochrone'] ?? null,
            'routeFrom' => $this->routeFrom ?? $defaults['route_from'] ?? null,
            'userId' => $this->userId,
        ];
        foreach ($optional as $name => $value) {
            if ($value !== null && $value !== '') {
                $params[$name] = (string) $value;
            }
        }
        foreach ($this->extra as $name => $value) {
            $params[$name] = (string) $value;
        }

        return self::IFRAME_URL . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /** CSS height: a bare number is pixels. */
    public function cssHeight(): string
    {
        $raw = trim((string) ($this->height ?? config('yatmo.map.height', '560px')));

        return preg_match('/^\d+(\.\d+)?$/', $raw) ? $raw . 'px' : $raw;
    }

    public function render(): View
    {
        return view('yatmo::components.map');
    }
}
