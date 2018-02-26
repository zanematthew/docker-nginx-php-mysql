'use strict';

import VueRouter from 'vue-router';
import TheRouterView from './components/TheRouterView';
import TheSingleEvent from './components/TheSingleEvent';
import TheListEvent from './components/TheListEvent';
import TheSingleVenue from './components/TheSingleVenue';
import TheListVenue from './components/TheListVenue';
import TheListSchedule from './components/TheListSchedule';
import TheSingleSchedule from './components/TheSingleSchedule';
import TheAction from './components/TheAction';
import TheScheduleFormRename from './components/TheScheduleFormRename';
import TheShare from './components/TheShare';
import TheScheduleEventToggle from '~/components/TheScheduleEventToggle';
import TheListCollection from '~/components/TheListCollection';
import TheSingleCollection from '~/components/TheSingleCollection';
import TheSearchResults from '~/components/TheSearchResults';

const routes = [
  {
    path: '/browse/events',
    redirect: { name: 'event-list-page', params: { when: 'this-month' } },
    component: TheRouterView,
    name: 'events',
    props: true,
    children: [
      {
        path: ':id(\\d+)/:slug/:when',
        component: TheSingleEvent,
        name: 'event-single-page',
        props: true
      },
      {
        path: ':when',
        component: TheListEvent,
        name: 'event-list-page',
        props: true
      }
    ]
  },
  {
    path: '/browse/venues/',
    redirect: { name: 'venue-list-page' },
    component: TheRouterView,
    name: 'venues',
    props: true,
    children: [
      {
        path: ':venue_id(\\d+)/:slug/events/:when/',
        component: TheSingleVenue,
        name: 'venue-single-events',
        props: true,
      },
      {
        path: ':state?',
        name: 'venue-list-page',
        component: TheListVenue,
        props: true
      }
    ]
  },
  {
    path: '/schedules',
    name: 'schedules',
    component: TheRouterView,
    redirect: { name: 'schedule-list-page' },
    props: true,
    children: [
      {
        path: 'all',
        name: 'schedule-list-page',
        component: TheListSchedule,
        props: true
      },
      {
        path: ':id(\\d+)/:slug?',
        name: 'schedule-single-page',
        component: TheSingleSchedule,
        props: true
      }
    ]
  },
  {
    path: '/collections/',
    component: TheRouterView,
    name: 'collection',
    redirect: { name: 'collection-list-page' },
    props: true,
    children: [
      {
        path: 'all',
        name: 'collection-list-page',
        component: TheListCollection,
        props: true,
      },
      {
        path: ':item_type',
        component: TheSingleCollection,
        name: 'collection-type',
        props: true,
      }
    ]
  },
  {
    //
    // /action/295/schedule/
    // /action/295/schedule/share/
    // /action/295/schedule/toggle/952/
    //
    // /action/295/event/
    // /action/295/event/share/
    // /action/295/event/toggle/952/
    //
    // /action/295/venue/
    // /action/295/venue/share/
    // /action/295/venue/toggle/952/
    //
    // /action/295/schedule/edit/
    // @todo review "nested routes", we can probably remove the TheRouterView in lieu of
    // empty sub-route paths.
    path: '/action/',
    component: TheRouterView,
    name: 'action-routerview',
    redirect: { name: 'action-main' },
    props: true,
    children: [
      {
        // This works for upper case as well; https://github.com/vuejs/vue-router/pull/1215
        path: ':id(\\d+)/:type([a-z]+)',
        name: 'action-main',
        component: TheAction,
        props: true,
      },
      // Can't use nested children because this has no TheRouterView to bind(?) to.
      {
        path: ':id(\\d+)/:type([a-z]+)/edit',
        name: 'action-edit',
        component: TheScheduleFormRename,
        props: true
      },
      {
        path: 'share',
        name: 'share',
        component: TheShare,
        props: true
      },
      //
      // @todo This should be universal, same concept for
      // toggling an item to a collection!
      // add/:type/to/
      //
      {
        path: 'add/:type([a-z]+)/:id(\\d+)/to',
        name: 'add-to',
        component: TheScheduleEventToggle,
        props: true,
      }
    ]
  },
  {
    path: '/',
    redirect: { name: 'events' }
  },
  {
    path: '*',
    component: {
      template: `<div class="grid row"><p><br />404 Page Not Found</p></div>`
    }
  },
  // /search/places/?state=md&venue=123&date=jan
  // /search/events/
  // /search/venues/
  // /search/people/
  // /search/tags/
  // /search/:type/:query
  {
    path: '/search/:type([a-z]+)',
    component: TheSearchResults,
    name: 'search-results',
    props: true,
  },
];

export default new VueRouter({
  mode: 'history',
  routes,
  linkActiveClass: 'is-active'
});