<?php

namespace App;

class ShovelEvent extends AbstractShovelClient
{
    use ShovelTrait;

    public function __construct($eventId = null)
    {
        $this->eventId = $eventId;
        parent::__construct($this->url());
    }

    public function url()
    {
        return 'http://usabmx.com/site/bmx_races/'.$this->eventId;
    }

    public function idFromShareLinks(): int
    {
        // https://twitter.com/share?url=http://usabmx.com/site/bmx_races/334979?section_id=23&text=State%20Race%20Double&hashtags=bmx
        // https://twitter.com/share?url=http://usabmx.com/site/bmx_races/1&text=Redline%20Cup%20Finals%20East&hashtags=bmx
        // https://twitter.com/share?url=http://usabmx.com/site/bmx_races/324629?section_id=228&text=East%20Coast%20Nationals&hashtags=bmx
        if ($this->filter('.share_race a')->count() == 0) {
            return 0;
        }

        $uri = $this->filter('.share_race a')->eq(0)->attr('href');

        // @TODO
        $matched = preg_match('#bmx_races/(.*?)\?#s', $uri, $matches);
        if (empty($matched)) {
            $matched = preg_match('#bmx_races/(.*?)\&#s', $uri, $matches);
        }

        return $matched ? $matches[1] : 0;
    }

    public function title()
    {
        return $this->filter('#event_title')->text();
    }

    // Most events just say "There is no description for this race."
    public function description(){}

    public function venueId(): int
    {
        $title = 0;
        if ($this->filter('#venue_title a')->count()) {
            $title = last(explode('/', $this->filter('#venue_title a')->eq(0)->attr('href')));
        }

        return (int) $title;
    }

    // Only for Nationals/USABMX hosted events
    // These have a START DATE, and END DATE.
    public function date(): array
    {
        $date = $this->filter('#event_date')->eq(0)->text();

        if (strpos($date, '-') !== false) {
            $date = array_map('trim', explode('-', $date));
        } else {
            $date = (array) trim($date);
        }

        return $date;
    }

    public function startDate(): string
    {
        return current($this->date());
    }

    public function endDate(): string
    {
        return last($this->date());
    }

    public function parseResource($resource = null): string
    {
        $resources = $this->filter('.race_resources li')->each(function ($node) use ($resource) {
            // Note, can't use `snake_case()`, because that makes BMX into b_m_x
            return [str_replace(' ', '_', strtolower($node->text())) => $node->filter('a')->attr('href')];
        });

        $tmp = [];
        // TODO figure out why the array returned is nested(?)
        foreach ($resources as $r){
            foreach ($r as $k => $v) {
                $tmp[$k] = $v;
            }
        }
        return $tmp[$resource] ?? '';
    }

    public function flyerUri(): string
    {
        return $this->parseResource('event_flyer');
    }

    public function eventScheduleUri(): string
    {
        return $this->parseResource('event_schedule');
    }

    public function hotelUri(): string
    {
        return $this->parseResource('bmx_hotels');
    }

    // public function preSignUri(){}
    // Only for earned double, rfl, gcq, state race)(){}
    public function parseDescription($text = null): string
    {
        if (empty($text)) {
            return '';
        }

        if ($this->filter('#event_description li')->count() == 0) {
            return '';
        }

        $textArray = $this->filter('#event_description li')->each(function ($node) use ($text) {
            return $node->filter('li')->text();
        });

        $found = null;
        foreach ($textArray as $item) {
            if (str_contains($item, $text)) {
                $found = $item;
            }
        }
        return trim(last(explode(': ', $found)));
    }

    public function fee()
    {
        return $this->parseDescription('Entry Fee');
    }

    public function registrationStartTime(): string
    {
        return $this->parseDescription('Registration Begins');
    }

    public function registrationEndTime()
    {
        return $this->parseDescription('Registration Ends');
    }

    public function getTypeFromTitle(): string
    {
        if (empty($this->filter('#event_title')->count())) {
            return '';
        }

        $title = $this->filter('#event_title')->eq(0)->text();
        $types = array_keys($this->eventTypes);

        foreach ($types as $type) {
            if (str_contains($title, $type)) {
                return $type;
            }
        }
        return $type ?? 'N/A';
    }
}
