<?php

namespace Likewares\Firewall\Middleware;

use Likewares\Firewall\Abstracts\Middleware;
use Illuminate\Support\Str;

class Geo extends Middleware
{
    public function check($patterns)
    {
        $places = ['continents', 'regions', 'countries', 'cities'];

        if ($this->isEmpty($places)) {
            return false;
        }

        if (! $location = $this->getLocation()) {
            return false;
        }

        foreach ($places as $place) {
            if (! $this->isFiltered($location, $place)) {
                continue;
            }

            return true;
        }

        return false;
    }

    protected function isEmpty($places)
    {
        foreach ($places as $place) {
            if (! $list = config('firewall.middleware.' . $this->middleware . '.' . $place)) {
                continue;
            }

            if (empty($list['allow']) && empty($list['block'])) {
                continue;
            }

            return false;
        }

        return true;
    }

    protected function isFiltered($location, $place)
    {
        if (! $list = config('firewall.middleware.' . $this->middleware . '.' . $place)) {
            return false;
        }

        $s_place = Str::singular($place);

        if (! empty($list['allow']) && ! in_array((string) $location->$s_place, (array) $list['allow'])) {
            return true;
        }

        if (in_array((string) $location->$s_place, (array) $list['block'])) {
            return true;
        }

        return false;
    }

    protected function getLocation()
    {
        $location = new \stdClass();
        $location->continent = $location->country = $location->region = $location->city = null;

        $service = config('firewall.middleware.' . $this->middleware . '.service');

        return $this->$service($location);
    }

    protected function ipapi($location)
    {
        $response = $this->getResponse('http://ip-api.com/json/' . $this->ip() . '?fields=continent,country,regionName,city');

        if (!is_object($response) || empty($response->country) || empty($response->city)) {
            return false;
        }

        $location->continent = $response->continent;
        $location->country = $response->country;
        $location->region = $response->regionName;
        $location->city = $response->city;

        return $location;
    }

    protected function extremeiplookup($location)
    {
        $response = $this->getResponse('https://extreme-ip-lookup.com/json/' . $this->ip());

        if (!is_object($response) || empty($response->country) || empty($response->city)) {
            return false;
        }

        $location->continent = $response->continent;
        $location->country = $response->country;
        $location->region = $response->region;
        $location->city = $response->city;

        return $location;
    }

    protected function ipstack($location)
    {
        $response = $this->getResponse('https://api.ipstack.com/' . $this->ip() . '?access_key=' . env('IPSTACK_KEY'));

        if (!is_object($response) || empty($response->country_name) || empty($response->region_name)) {
            return false;
        }

        $location->continent = $response->continent_name;
        $location->country = $response->country_name;
        $location->region = $response->region_name;
        $location->city = $response->city;

        return $location;
    }

    protected function ipdata($location)
    {
        $response = $this->getResponse('https://api.ipdata.co/' . $this->ip() . '?api-key=' . env('IPSTACK_KEY'));

        if (! is_object($response) || empty($response->country_name) || empty($response->region_name)) {
            return false;
        }

        $location->continent = $response->continent_name;
        $location->country = $response->country_name;
        $location->region = $response->region_name;
        $location->city = $response->city;

        return $location;
    }

    protected function ipinfo($location)
    {
        $response = $this->getResponse('https://ipinfo.io/' . $this->ip() . '/geo?token=' . env('IPINFO_KEY'));

        if (! is_object($response) || empty($response->country) || empty($response->city)) {
            return false;
        }

        $location->country = $response->country;
        $location->region = $response->region;
        $location->city = $response->city;

        return $location;
    }

    public function ipregistry($location)
    {
        $url = 'https://api.ipregistry.co/' . $this->ip() . '?key=' . env('IPREGISTRY_KEY');

        $response = $this->getResponse($url);

        if (! is_object($response) || empty($response->location)) {
            return false;
        }

        $location->continent = $response->location->continent->name;
        $location->country = $response->location->country->name;
        $location->country_code = $response->location->country->code;
        $location->region = $response->location->region->name;
        $location->city = $response->location->city;
        $location->timezone = $response->time_zone->id;
        $location->currency_code = $response->currency->code;

        $location->is_eu = $response->location->in_eu;

        if (! empty($response->location->language->code)) {
            $location->language_code = $response->location->language->code . '-' . $response->location->country->code;
        }

        return $location;
    }

    public function ip2locationio($location)
    {
        $url = 'https://api.ip2location.io/?ip=' . $this->ip() . '&key=' . env('IP2LOCATIONIO_KEY');

        $response = $this->getResponse($url);

        if (! is_object($response) || empty($response->location)) {
            return false;
        }

        $location->country = $response->country_name;
        $location->country_code = $response->country_code;
        $location->region = $response->region_name;
        $location->city = $response->city_name;
        $location->latitude = $response->latitude;
        $location->longitude = $response->longitude;
        $location->zipcode = $response->zip_code;
        $location->timezone = $response->time_zone;
        $location->asn = $response->asn;
        $location->as = $response->as;

        return $location;
    }

    protected function getResponse($url)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            $content = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($content);
        } catch (\ErrorException $e) {
            $response = null;
        }

        return $response;
    }
}
