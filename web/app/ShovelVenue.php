<?php

namespace App;

class ShovelVenue extends AbstractShovelClient
{
    use ShovelTrait;

    private $venueId;

    public function __construct($venueId = null)
    {
        $this->venueId = $venueId;
        parent::__construct($this->url());
    }

    public function url()
    {
        return 'https://www.usabmx.com/site/bmx_tracks/'.$this->venueId;
    }

    /**
     * Parse the Venue contact information.
     *
     * @return array An empty array on failure. On success;
     *                    'email'             => 'some value',
     *                    'track_phone'       => 'some value',
     *                    'primaryContact'   => 'some value',
     *                    'secondaryContact' => 'some value',
     */
    public function contact(): array
    {
        if (empty($this->filter('#track_contact ul li')->count())) {
            return [];
        }

        $contact = array_filter($this->filter('#track_contact ul li')->each(function ($node) {
            $text = $node->filter('li')->text();
            if (str_contains($text, ':')) {
                list($title, $content) = explode(':', $text);
                $key = lcfirst(trim(camel_case($title))); // clean title
                $key = $key == 'trackPhone' ? 'phone' : $key;
                return [$key => $this->aggressiveTrim($content)];
            }
        }));

        $tmp = [];

        foreach ($contact as $c) {
            foreach ($c as $k => $v) {
                $tmp[$k] = $v;
            }
        }

        return $tmp;
    }

    /**
     * Parse the venue Location.
     *
     * An address may look like the following, note the second has
     * no street, and two br tags.
     * <p>26600 Budds Creek Rd<br>Mechanicsville,  Md 20659<br>Usa</p>
     * <p>Egg Harbor Township,  Nj 08234<br>Usa</p>
     *
     * @return array An empty array on failure. On success;
     *                  'street'    => 'some value',
     *                  'city'      => 'some value',
     *                  'stateAbbr' => 'some value',
     *                  'zipCode'   => 'some value',
     *                  'country'   => 'some value',
     */
    public function parseLocationFromP(): array
    {
        $location = $this->filter('#track_location p')->eq(0)->html();

        $street  = null;
        $city    = null;
        $state   = null;
        $zip     = null;
        $country = null;

        if (substr_count($location, '<br>') == 2) {
            list($street, $cityStateZip, $country) = explode('<br>', $location);
            list($city, $stateZip) = array_map('trim', explode(',', $cityStateZip));
            list($state, $zip) = explode(' ', $stateZip);
        }

        if (substr_count($location, '<br>') == 1) {
            list($city, $stateZipCountry) = explode(',  ', $location);
            list($state, $zipCountry) = explode(' ', $stateZipCountry);
            list($zip, $country) = explode('<br>', $zipCountry);
        }

        return array_map('strip_tags', [
            'street'    => $street,
            'city'      => $city,
            'stateAbbr' => strtoupper($state),
            'zipCode'   => $zip,
            'country'   => strtoupper($country),
        ]);
    }

    public function getStreet(): string
    {
        return $this->parseLocationFromP()['street'];
    }

    public function getCity(): string
    {
        return $this->parseLocationFromP()['city'];
    }

    public function getStateAbbr(): string
    {
        return $this->parseLocationFromP()['stateAbbr'];
    }

    public function getZipCode(): string
    {
        return $this->parseLocationFromP()['zipCode'];
    }

    public function getCountry(): string
    {
        return $this->parseLocationFromP()['country'];
    }

    public function parseLinksUri($linkText = null): string
    {
        $link = array_values(array_filter($this->filter('#track_location p')->each(function ($node) use ($linkText) {
            if (empty($node->filter('a')->count())) {
                return '';
            }
            return str_contains($node->text(), $linkText) ? $node->filter('a')->attr('href') : '';
        })));

        return empty($link) ? '' : $link[0];
    }

    public function parseScheduleUri(): string
    {
        return $this->parseLinksUri('Schedule');
    }

    public function parseMapUri(): string
    {
        return $this->parseLinksUri('Map');
    }

    public function parseWebsiteUri(): string
    {
        return $this->parseLinksUri('Website');
    }

    public function parseLogo(): string
    {
        return $this->filter('#track_location img')->count() ? $this->filter('#track_location img')->attr('src') : '';
    }

    public function parseDescription(): string
    {
        return implode(' ', $this->filter('#track_contact p')->each(function ($node) {
            return $node->text();
        }));
    }

    public function parseDistrict(): string
    {
        if (empty($this->filter('#track_district')->count())) {
            return '';
        }

        return trim(last(explode(':', $this->filter('#track_district')->text())));
    }

    public function parseLatLongMapsUri(): array
    {
        preg_match("~\?q=(.*?)\&~", $this->parseMapUri(), $matches);
        if (empty($matches)) {
            return [];
        }

        list($lat, $long) = explode(',', $matches[0]);
        return [
            'lat'  => floatval(str_replace('?q=', '', $lat)),
            'long' => floatval($long),
        ];
    }

    public function parseName(): string
    {
        return $this->aggressiveTrim(current(explode('<', $this->filter('#main_content h1')->html())));
    }
}
