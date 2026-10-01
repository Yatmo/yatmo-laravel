<?php

declare(strict_types=1);

namespace Yatmo\Laravel\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Yatmo\Exception\YatmoException;
use Yatmo\Laravel\Yatmo;
use Yatmo\Model\SummaryCategory;
use Yatmo\Model\SummaryPlace;
use Yatmo\Model\TravelData;
use Yatmo\TravelMode;

/**
 * `<x-yatmo-pois :latitude="$listing->lat" :longitude="$listing->lng" categories="education,shopping" mode="walking" :limit="2" />`
 *
 * The nearest places by category (nurseries, schools, supermarkets, bus stops...) with distance and
 * travel time, as headings and lists styled by your CSS, from the cached summary.
 */
final class YatmoPois extends Component
{
    private const TYPES = ['education' => SummaryCategory::EDUCATION, 'transport' => SummaryCategory::TRANSPORT, 'shopping' => SummaryCategory::SHOPPING, 'tourism' => SummaryCategory::TOURISM];

    /** @param string|string[]|null $categories education, transport, shopping, tourism */
    public function __construct(
        private readonly Yatmo $yatmo,
        public float $latitude,
        public float $longitude,
        public string|array|null $categories = null,
        public string $mode = 'walking',
        public int $limit = 1,
        public string $heading = 'h3',
    ) {
    }

    /**
     * The groups to render: label, places with their travel data.
     *
     * @return array<int, array{label: string, places: array<int, array{name: string, distance: ?string, time: ?string, mode: ?string}>}>
     */
    public function groups(): array
    {
        try {
            $summary = $this->yatmo->summary($this->latitude, $this->longitude);
        } catch (YatmoException $e) {
            report($e);

            return [];
        }
        $names = \is_string($this->categories) ? explode(',', $this->categories) : ($this->categories ?? []);
        $types = array_values(array_filter(array_map(fn ($n) => self::TYPES[strtolower(trim((string) $n))] ?? null, $names)));
        $mode = TravelMode::tryFrom(ucfirst(strtolower($this->mode))) ?? TravelMode::Walking;
        $groups = [];
        foreach ($summary->subCategories(...$types) as $sub) {
            $places = \array_slice($sub->places, 0, max(1, $this->limit));
            if ($places === []) {
                continue;
            }
            $groups[] = [
                'label' => \count($places) === 1 && $sub->singularLabel ? $sub->singularLabel : $sub->label,
                'places' => array_map(function ($place) use ($mode) {
                    // The chosen mode, else the first mode the API computed (an airport has no walking time).
                    $travel = $place->travel($mode) ?? $this->firstTravel($place);

                    return ['name' => $place->name, 'distance' => $travel?->distanceShortLabel, 'time' => $travel?->travelTimeShortLabel, 'mode' => $travel?->travelMode === $mode ? null : $travel?->translatedTravelMode];
                }, $places),
            ];
        }

        return $groups;
    }

    private function firstTravel(SummaryPlace $place): ?TravelData
    {
        foreach ($place->travelData as $travel) {
            if ($travel->hasTravelInformation) {
                return $travel;
            }
        }

        return null;
    }

    public function render(): View
    {
        return view('yatmo::components.pois');
    }
}
