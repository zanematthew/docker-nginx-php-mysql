<?php

namespace App;

trait ShovelTrait
{
    protected $eventTypes = [
        'National' => [
            'section_id' => 228,
        ],
        'Earned Double' => [
            'section_id' => 95,
        ],
        'Gold Cup' => [
            'section_id' => 24,
        ],
        'Race for Life' => [
            'section_id' => 19,
        ],
        'State' => [
            'section_id' => 23,
        ],
    ];

    public function validYears()
    {
        $years     = [];
        $yearStart = 2004;
        $yearEnd   = date('Y');

        while ($yearStart <= $yearEnd) {
            $years[] = $yearStart++;
        }
        $years[] = 'Upcoming';

        return array_reverse($years);
    }

    public function isYearValid($year = null): bool
    {
        return (in_array($year, $this->validYears()));
    }

    public function isStateValid($state = null): bool
    {
        return (in_array(strtoupper($state), array_keys($this->states()), true));
    }

    public function isTypeValid($type = null): bool
    {
        return (in_array($type, array_keys($this->eventTypes)));
    }

    public function states()
    {
        return [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            // 'DC' => 'District Of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        ];
    }

    public function aggressiveTrim($content = null): string
    {
        return trim(preg_replace('/\s\s+/', ' ', $content));
    }

    public function parsedPageRange($pageRange = null): array
    {
        if (str_contains($pageRange, '-')) {
            return explode('-', $pageRange);
        }
        return [$pageRange, $pageRange];
    }

    public function convertToCents($price = null): int
    {
        return $price * 100;
    }

    public function timeFormat($time = null): string 
    {        
        return date('H:i', strtotime($time));
    }
}
