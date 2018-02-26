<?php

namespace App;

class ShovelEventsSchedule extends AbstractShovelClient
{
    use ShovelTrait;

    public function __construct($venuePageId = null, $venueId = null, $year = null, $month = null)
    {
        $this->venuePageId = $venuePageId;
        $this->venueId = $venueId;
        $this->year    = $year ?? date('Y');
        $this->month   = $month;

        parent::__construct($this->buildUrl());
    }

    public function buildUrl(): string
    {
        return 'http://usabmx.com/tracks/'.$this->venuePageId.'/events/schedule?'.http_build_query([
            'mode'  => 'calendar',
            'month' => $this->month,
            'year'  => $this->year,
        ]);
    }

    public function usaBmxEventIds(): array
    {
        return $this->filter('.calendar-event')->each(function ($node) {
            return explode('_', $node->filter('a')->attr('href'))[1];
        });
    }

    public function events(): array
    {
        // We need to loop over the `calendar_day_inner`, because this contains the date.
        // We can't just loop over `calendar_day_content`.
        $events = $this->filter('.calendar_day_inner')->each(function ($node) {

            // Bail if there is no content, skip over days with no events.
            if ($node->filter('.calendar-event')->count() == 0) {
                return [];
            }

            $date = date('Y-m-d', strtotime(sprintf(
                '%d-%d-%d',
                $this->year,
                $this->month,
                $node->filter('.calendar_day_title')->text()
            )));

            $events = $node->filter('.calendar-event')->each(function ($node) use ($date) {

                // Not all have a title
                if ($node->filter('h6')->count() == 1) {
                    $event['title'] = $node->filter('h6')->text();
                }

                $usaBmxEventId = explode('_', $node->filter('a')->attr('href'))[1];

                if ($node->filter('p')->count() == 1) {
                    $contentsArray = array_filter(
                        explode('<br>', str_replace(
                            ['<p>','</p>'],
                            '',
                            $this->aggressiveTrim($node->filter('p')->html())
                        ))
                    );

                    foreach ($contentsArray as $contentArray) {
                        list($key, $value) = explode(': ', $contentArray); // Use ': ', to avoid time issues.
                        $event[ str_slug(strip_tags($key)) ] = $value;
                    }
                    $event['usabmx_id'] = $usaBmxEventId;
                    $event['start_date']      = $date;
                    return $event;
                }
            });
            return $events;
        });

        $events = array_values(array_filter($events));
        $cleanedEvents = [];
        foreach ($events as $event) {
            $event = array_map(function ($event) {
                unset($event['status']);
                unset($event['race-time']);

                if (empty($event['registration-start'])) {
                    $event['registration_start_time'] = '';
                } else {
                    $event['registration_start_time'] = $this->timeFormat(
                        $this->wtfTimeFix(
                            $event['registration-start'], $event['type']
                        )
                    );
                    unset($event['registration-start']);
                }

                if (empty($event['registration-end'])) {
                    $event['registration_end_time'] = '';
                } else {
                    $event['registration_end_time'] = $this->timeFormat(
                        $this->wtfTimeFix(
                            $event['registration-end'], $event['type']
                        )
                    );
                    unset($event['registration-end']);
                }

                $event['title']           = $event['title'] ?? $event['type'];
                $event['fee']             = empty($event['fee']) ? '' : $this->feeFix($event['fee']);
                $event['usabmx_venue_id'] = $this->venueId;

                return $event;
            }, $event);
            $cleanedEvents[] = $event;
        }

        return $cleanedEvents;
    }

    public function feeFix($fee = null): string
    {
        $fee = str_replace([
                '$',
                'USD',
                ' ',
            ], '', $fee);

        preg_match('/[a-z]/i', $fee, $matches);

        if ($matches) {
            return '';
        }
        return $fee;
    }

    // Most practices and clinics are held in the evening, so if its missing the PM
    // we add it.
    public function wtfTimeFix($time = null, $eventType = null): string
    {
        $time = strtoupper($time);

        preg_match('#\d+M#', $time, $matches);
        if ($matches) {
            $time = str_replace('M', 'PM', $time);
        } elseif (in_array(strtolower($eventType), ['practice', 'clinic'])
            && ! str_contains($time, ['M', 'PM']) ) {
            $time = $time .'PM';
        }
        return $time;
    }

    public function count(): int
    {
        $events = $this->events();
        $total  = 0;
        foreach ($events as $event){
            $total += count($event);
        }
        return $total;
    }
}