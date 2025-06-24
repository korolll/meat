<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\GeocodeRequest;
use App\Http\Requests\Clients\API\ReverseRequest;
use Geocoder\Laravel\Facades\Geocoder;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class GeocodeController extends Controller
{
    /**
     *
     */
    public function __construct()
    {
        $this->middleware('throttle:30');
    }

    /**
     * @param \App\Http\Requests\Clients\API\GeocodeRequest $request
     *
     * @return array[]
     */
    public function geocode(GeocodeRequest $request)
    {
        $address = $request->get('address');
        $query = GeocodeQuery::create($address)
            ->withLimit(5)
            ->withLocale('ru');

        $data = config('geocoder.data', []);
        foreach ($data as $field => $value) {
            if ($value !== null) {
                $query = $query->withData($field, $value);
            }
        }

        /** @var \Geocoder\Laravel\ProviderAndDumperAggregator $aggregator */
        $aggregator = Geocoder::geocodeQuery($query);
        $results = [];

        /** @var \Geocoder\Location $geocoded */
        foreach ($aggregator->get() as $geocoded) {
            $results[] = $geocoded->toArray();
        }

        return ['data' => $results];
    }

    /**
     * @param \App\Http\Requests\Clients\API\ReverseRequest $request
     *
     * @return array[]
     */
    public function reverse(ReverseRequest $request)
    {
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $query = ReverseQuery::fromCoordinates($latitude, $longitude)
            ->withLimit(1)
            ->withLocale('ru');

        $data = config('geocoder.data', []);
        foreach ($data as $field => $value) {
            if ($value !== null) {
                $query = $query->withData($field, $value);
            }
        }

        /** @var \Geocoder\Laravel\ProviderAndDumperAggregator $aggregator */
        $aggregator = Geocoder::reverseQuery($query);
        $results = [];

        /** @var \Geocoder\Location $geocoded */
        foreach ($aggregator->get() as $geocoded) {
            $results[] = $geocoded->toArray();
        }

        return ['data' => $results];
    }

}
