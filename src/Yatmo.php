<?php

declare(strict_types=1);

namespace Yatmo\Laravel;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository;
use Yatmo\Client;
use Yatmo\Model\Scores;
use Yatmo\Model\Summary;
use Yatmo\Model\SummaryText;
use Yatmo\TextRenderer;

/**
 * The Yatmo service of the application (`Yatmo` facade, or inject this class): the client plus the
 * cached calls a property page needs. A neighbourhood rarely changes, so the text and the places
 * are kept for `yatmo.cache_days` days, as the plain arrays the API sent (every cache store takes them).
 */
final class Yatmo
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly Client $client,
        private readonly array $config,
        private readonly CacheFactory $cache,
    ) {
    }

    /** The configured `Yatmo\Client`, for any endpoint (scores, isochrones, routes, geocoding, static maps...). */
    public function client(): Client
    {
        return $this->client;
    }

    /** The country code of the configuration. */
    public function country(): string
    {
        return strtoupper((string) ($this->config['country'] ?? 'BE'));
    }

    /** The frontend key, for the map iframe. */
    public function frontendKey(): string
    {
        return (string) ($this->config['frontend_key'] ?? '');
    }

    /** The neighbourhood text of a property in the given language (default: the client's), cached. */
    public function text(float $latitude, float $longitude, ?string $language = null): SummaryText
    {
        $language = strtoupper($language ?? $this->client->language());
        $raw = $this->remember('text', $latitude, $longitude, fn () => $this->client->summaryTextRaw($latitude, $longitude));

        return SummaryText::fromWire($raw, $language);
    }

    /**
     * The neighbourhood text as HTML, ready for `{!! !!}` in a Blade view.
     *
     * @param array{heading?: ?string, titles?: string, paragraphs?: string[], strong?: bool, class?: string} $options See Yatmo\TextRenderer
     */
    public function textHtml(float $latitude, float $longitude, array $options = [], ?string $language = null): string
    {
        return TextRenderer::html($this->text($latitude, $longitude, $language), $options);
    }

    /** The nearest places by category with travel times, cached. */
    public function summary(float $latitude, float $longitude): Summary
    {
        return Summary::fromWire($this->remember('summary', $latitude, $longitude, fn () => $this->client->summaryRaw($latitude, $longitude)));
    }

    /** One 0 to 10 score per category, cached. */
    public function scores(float $latitude, float $longitude): Scores
    {
        return Scores::fromWire($this->remember('scores', $latitude, $longitude, fn () => $this->client->scoresRaw($latitude, $longitude)));
    }

    /** Forgets the cached data of a position (text, places, scores). */
    public function forget(float $latitude, float $longitude): void
    {
        foreach (['text', 'summary', 'scores'] as $kind) {
            $this->store()->forget($this->key($kind, $latitude, $longitude));
        }
    }

    /**
     * @param callable(): array<string, mixed> $load
     * @return array<string, mixed>
     */
    private function remember(string $kind, float $latitude, float $longitude, callable $load): array
    {
        $days = (int) ($this->config['cache_days'] ?? 30);
        if ($days <= 0) {
            return $load();
        }

        return $this->store()->remember($this->key($kind, $latitude, $longitude), $days * 86400, $load);
    }

    private function store(): Repository
    {
        return $this->cache->store($this->config['cache_store'] ?? null);
    }

    private function key(string $kind, float $latitude, float $longitude): string
    {
        // The language is not part of the key: the text is cached in every language, the places in the client's.
        $language = $kind === 'text' ? '' : '|' . $this->client->language();

        return sprintf('yatmo|%s|%s|%s|%s%s', $kind, $this->country(), number_format($latitude, 6, '.', ''), number_format($longitude, 6, '.', ''), $language);
    }
}
