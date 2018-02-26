<?php

namespace App;

class ShovelEventIdByType extends AbstractShovelClient
{
    use ShovelTrait;

    public function __construct($type = null, $year = null, $page = null, $pastOnlyFix = null)
    {
        parent::__construct($this->url($type, $year, $page, $pastOnlyFix));
    }

    // $pastOnly, In order to see past events for the current year you need to
    // pass in "past_only=1" in the URL.
    public function url($type = null, $year = null, $page = null, $pastOnly = null): string
    {
        if ($this->isYearValid($year) === false) {
            return '';
        }

        $sectionId = $this->eventTypes[$type]['section_id'];

        switch ($sectionId) {
            case 228:
                $additionalParams = ['category' => strtoupper($type)];
                break;
            case 95:
                $additionalParams = ['series_race_type' => $type];
                break;
            case 24:
                $additionalParams = ['goldcup' => 1];
                break;
            case 19:
                $additionalParams = ['series_race_type' => $type];
                break;
            case 23:
                $additionalParams = ['filter_state' => 1];
                break;
            default:
                $additionalParams = null;
                break;
        }

        $yearFix = $year;

        // UPCOMING and past only 0 can't happen at the same time.
        if ($year == 'Upcoming' || $year == date('Y') && empty($pastOnly)) {
            $yearFix  = 'UPCOMING';
            $pastOnly = 0;
        } else {
            $pastOnly = 1;
        }

        return 'https://www.usabmx.com/site/bmx_races?' . http_build_query(array_merge([
            'section_id' => $sectionId,
            'year'       => $yearFix,
            'past_only'  => $pastOnly,
            'page'       => empty($page) ? 1 : $page,
        ], $additionalParams));
    }

    public function eventIds(): array
    {
        $links = $this->filter('.event_title')->each(function ($node) {
            return $node->filter('a')->eq(0)->attr('href');
        });

        return array_map(function ($link) {
            return (int) explode('/', explode('?', $link)[0])[3];
        }, $links);
    }

    public function maxPage(): int
    {
        return max($this->filter('.pagination li')->each(function ($node) {
            return (int) $node->filter('li')->text();
        }));
    }
}
