<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $paginate = 10;
    protected $params = [
        'next_month',
        'this_month',
        'upcoming',
        'page',
        'state',
        'states',
        'venue_id',
    ];

    public function handleRequest($q = null)
    {
        if (request('state')) {
            $state = request('state');
            $q = $q->whereHas('venue.city.states', function ($query) use ($state) {
                $query->where('abbr', strtoupper($state));
            });
        }

        if (request('states')) {
            $states = request('states');
            $q = $q->whereHas('venue.city.states', function ($query) use ($states) {
                $states = explode(',', strtoupper($states));
                $query->whereIn('abbr', $states);
            });
        }

        if (request('next_month') == true) {
            $q = $q->whereBetween('start_date', [
                date('Y-m-d', strtotime('first day of next month')),
                date('Y-m-d', strtotime('last day of next month')),
            ]);
        } elseif (request('this_month') == true) {
            $q = $q->whereBetween('start_date', [
                date('Y-m-d', strtotime('today')),
                date('Y-m-d', strtotime('last day of this month')),
            ]);
        } elseif (request('upcoming') == true) {
            $q = $q->where('start_date', '>=', date('Y-m-d', strtotime('today')));
        }

        if (request('venue_id')) {
            $q = $q->where('venue_id', request('venue_id'));
        }

        return $q;
    }

    /**
     * All events
     *
     * @return object Paginated Event model with; venue, city, and state. Ordered by start date ascending.
     */
    public function index(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $q = \App\Event::with('venue.city.states');
        $q = $this->handleRequest($q);

        return $q->orderBy('start_date', 'asc')
                 ->paginate($this->paginate)->appends(request($this->params));
    }

    /**
     * Events based on state
     *
     * @param  string $state The state abbreviation.
     *
     * @return object        Paginated Event model.
     */
    public function state($state = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $q = \App\Event::with('venue.city.states')
            ->whereHas('venue.city.states', function ($query) use ($state) {
                $query->where('abbr', strtoupper($state));
            });
        $q = $this->handleRequest($q);

        return $q->orderBy('start_date', 'asc')
                 ->paginate($this->paginate)->appends(request($this->params));
    }

    /**
     * Events based on type.
     *
     * @param  string $type The type of event.
     *
     * @return object       Paginated Event model based on type.
     */
    public function type($type = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {

        $q = \App\Event::with('venue.city.states');
        $q = $this->handleRequest($q);

        return $q->where('type', $type)
                 ->orderBy('start_date', 'asc')
                 ->paginate($this->paginate);
    }

    /**
     * Events based on year.
     *
     * @param  string $year The year.
     *
     * @return object       Paginated events model based on year.
     */
    public function year($year = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $q = \App\Event::with('venue.city.states');
        $q = $this->handleRequest($q);
        return $q->whereYear('start_date', $year)
                 ->orderBy('start_date', 'asc')
                 ->paginate($this->paginate);
    }

    /**
     * Events based on year and state.
     *
     * @param  string $year  A four digit year, YYYY.
     * @param  string $month A two digit month, MM.
     *
     * @return object        Paginated events.
     */
    public function yearMonth($year = null, $month = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $q = \App\Event::with('venue.city.states');
        $q = $this->handleRequest($q);
        return $q->whereYear('start_date', $year)
                 ->whereMonth('start_date', $month)
                 ->orderBy('start_date', 'asc')
                 ->paginate($this->paginate);
    }

    /**
     * Events based on year and type.
     *
     * @param  string $year Four digit year, YYYY.
     * @param  string $type The type.
     *
     * @return object       Paginated events based on year and type.
     */
    public function yearType($year = null, $type = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Event::with('venue.city.states')
            ->whereYear('start_date', $year)
            ->where('type', $type)
            ->orderBy('start_date', 'asc')
            ->paginate($this->paginate);
    }

    /**
     * Events based on year and state.
     *
     * @param  string $year  Four digit year, YYYY.
     * @param  string $state The state abbreviation.
     *
     * @return object        Paginated events based on year and state.
     */
    public function yearState($year = null, $state = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Event::with('venue.city.states')
            ->whereHas('venue.city.states', function ($query) use ($state) {
                $query->where('abbr', strtoupper($state));
            })
            ->whereYear('start_date', $year)
            ->orderBy('start_date', 'asc')
            ->paginate($this->paginate);
    }

    /**
     * Events based on type and year.
     *
     * @param  string $year  Four digit year, YYYY.
     * @param  string $type  Event type.
     * @param  string $state The state abbreviation.
     *
     * @return object        Paginated events based on year, type and state.
     */
    public function yearTypeState($year = null, $type = null, $state = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Event::with('venue.city.states')
            ->whereHas('venue.city.states', function ($query) use ($state) {
                $query->where('abbr', strtoupper($state));
            })
            ->whereYear('start_date', $year)
            ->where('type', $type)
            ->orderBy('start_date', 'asc')
            ->paginate($this->paginate);
    }

    /**
     * Events based on year, month, and state.
     *
     * @param  string $year  Four digit year, YYYY.
     * @param  string $month Two digit month, MM.
     * @param  string $state The state abbreviation.
     *
     * @return object        Paginated events based on year, month, state.
     */
    public function yearMonthState($year = null, $month = null, $state = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Event::with('venue.city.states')
            ->whereHas('venue.city.states', function ($query) use ($state) {
                $query->where('abbr', strtoupper($state));
            })
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->orderBy('start_date', 'asc')
            ->paginate($this->paginate);
    }

    /**
     * Events based on year, month, type and state.
     *
     * @param  string $year  Four digit year, YYYY.
     * @param  string $month Two digit month, MM.
     * @param  string $type  The event type.
     * @param  string $state The state abbreviation.
     *
     * @return object        Paginated events based on year, month, state.
     */
    public function yearMonthTypeState($year = null, $month = null, $type = null, $state = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Event::with('venue.city.states')
                ->whereHas('venue.city.states', function ($query) use ($state) {
                    $query->where('abbr', strtoupper($state));
                })
                ->whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->where('type', $type)
                ->orderBy('start_date', 'asc')
                ->paginate($this->paginate);
    }

    public function single($id = null, $slug = null)
    {
        return \App\Event::with('venue.city.states')->where('id', $id)->first();
    }
}
