<?php
// @TODO needs test
namespace App;

class ShovelVenueByState extends AbstractShovelClient
{
    use ShovelTrait;

    private $stateAbbr;

    public function __construct($stateAbbr = null)
    {
        $this->stateAbbr = $stateAbbr;
        parent::__construct($this->url());
    }

    public function url(): string
    {
        return 'http://usabmx.com/site/bmx_tracks/by_state?section_id=12&state='.strtoupper($this->stateAbbr);
    }

    /**
     * Parses for the Venue ID, and Venue Title.
     *
     * @return array    An array containing;
     *                     'id'    => 123,
     *                     'title' => 'venue title'
     */
    public function parseVenueId(): array
    {
        return $this->filter('#track_results li')->each(function ($node) {
            return (int)explode('?', explode('/', $node->filter('.track_title a')->attr('href'))[3])[0];
        });
    }
}
