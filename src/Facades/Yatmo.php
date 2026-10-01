<?php

declare(strict_types=1);

namespace Yatmo\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Yatmo\Client client()
 * @method static string country()
 * @method static string frontendKey()
 * @method static \Yatmo\Model\SummaryText text(float $latitude, float $longitude, ?string $language = null)
 * @method static string textHtml(float $latitude, float $longitude, array $options = [], ?string $language = null)
 * @method static \Yatmo\Model\Summary summary(float $latitude, float $longitude)
 * @method static \Yatmo\Model\Scores scores(float $latitude, float $longitude)
 * @method static void forget(float $latitude, float $longitude)
 *
 * @see \Yatmo\Laravel\Yatmo
 */
final class Yatmo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Yatmo\Laravel\Yatmo::class;
    }
}
