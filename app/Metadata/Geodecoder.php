<?php

declare(strict_types=1);

namespace App\Metadata;

use App\Models\Configs;
use App\Models\Logs;
use Geocoder\Provider\Cache\ProviderCache;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Query\ReverseQuery;
use Geocoder\StatefulGeocoder;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Http\Adapter\Guzzle6\Client as ClientAdapter;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;

class Geodecoder
{
    /**
     * Get http provider with caching.
     */
    public static function getGeocoderProvider(): ProviderCache
    {
        $stack = HandlerStack::create();
        $stack->push(RateLimiterMiddleware::perSecond(1));

        $httpClient = new Client([
            'handler' => $stack,
            'timeout' => Configs::get_value('location_decoding_timeout'),
        ]);

        $httpAdapter = new ClientAdapter($httpClient);

        $provider = new Nominatim($httpAdapter, 'https://nominatim.openstreetmap.org', \config('app.name'));

        return new ProviderCache($provider, \app('cache.store'));
    }

    /**
     * Decode GPS coordinates into location.
     *
     * @return string location
     */
    public static function decodeLocation(?float $latitude, ?float $longitude): ?string
    {
        // User does not want to decode location data
        if (Configs::get_value('location_decoding') === false) {
            return null;
        }
        if ($latitude === null || $longitude === null) {
            return null;
        }

        $cachedProvider = self::getGeocoderProvider();

        return self::decodeLocation_core($latitude, $longitude, $cachedProvider);
    }

    /**
     * Wrapper to decode GPS coordinates into location.
     */
    public static function decodeLocation_core(
        float $latitude,
        float $longitude,
        ProviderCache $cachedProvider
    ): ?string {
        $geocoder = new StatefulGeocoder($cachedProvider, Configs::get_value('lang'));
        $result_list = $geocoder->reverseQuery(ReverseQuery::fromCoordinates($latitude, $longitude));

        // If no result has been returned -> return null
        if ($result_list->isEmpty()) {
            Logs::warning(
                __METHOD__,
                __LINE__,
                'Location (' . $latitude . ', ' . $longitude . ') could not be decoded.'
            );

            return null;
        }

        return $result_list->first()->getDisplayName();
    }
}
