<?php

declare(strict_types=1);

namespace Yatmo\Laravel\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Yatmo\Exception\YatmoException;
use Yatmo\Laravel\Yatmo;

/**
 * `<x-yatmo-text :latitude="$listing->lat" :longitude="$listing->lng" heading="h3" titles="city" paragraphs="education,shopping" />`
 *
 * The written description of the neighbourhood as headings and paragraphs, rendered on the server
 * (indexable) and cached. Renders nothing when Yatmo cannot be reached, so a page never breaks
 * because of it; the exception is logged.
 */
final class YatmoText extends Component
{
    /** @param string|string[]|null $paragraphs */
    public function __construct(
        private readonly Yatmo $yatmo,
        public float $latitude,
        public float $longitude,
        public ?string $heading = 'h3',
        public string $titles = 'street-city',
        public string|array|null $paragraphs = null,
        public bool $strong = true,
        public ?string $language = null,
    ) {
    }

    public function html(): string
    {
        try {
            return $this->yatmo->textHtml($this->latitude, $this->longitude, [
                'heading' => $this->heading === '' ? null : $this->heading,
                'titles' => $this->titles,
                'paragraphs' => \is_string($this->paragraphs) ? array_filter(array_map('trim', explode(',', $this->paragraphs))) : ($this->paragraphs ?? []),
                'strong' => $this->strong,
            ], $this->language);
        } catch (YatmoException $e) {
            report($e);

            return '';
        }
    }

    public function render(): View
    {
        return view('yatmo::components.text');
    }
}
