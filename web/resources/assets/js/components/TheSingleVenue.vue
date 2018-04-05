<template>
<div>
  <action-bar :type="'venue'" :item="venue" class="row"></action-bar>
  <venue-contact :venue="venue" class="grid is-100 row is-item"></venue-contact>
  <address itemprop="address" itemscope itemtype="http://schema.org/PostalAddress" class="row is-item grid is-100">
    <span v-if="venue.street_address" itemprop="streetAddress">{{ venue.street_address }}</span><br>
    <span itemprop="addressLocality">{{ venue.city.name }}</span>,
    <span v-if="venue.city.states[0]" itemprop="addressRegion">{{ venue.city.states[0].abbr }}</span> <span>{{ venue.zip_code }}</span>
  </address>
  <div v-if="venue.events" class="row is-item grid is-100"><strong>{{ eventCount(venue.events) }}</strong> Events</div>

  <tabs-events></tabs-events>
</div>
</template>
<script>

// @TODO fix this.
// :city="venue.city.name"
// :state_abbr="venue.city.states[0].abbr"
import Vue from 'vue';

import venueContact from '~/components/VenueContact';
import actionBar from '~/components/ActionBar';
import tabsEvents from '~/components/TabsEvents';

export default {
  components: {
    venueContact,
    actionBar,
    tabsEvents
  },
  data() {
    return {
      venue: { city: { states: [{abbr:''}] } },
      pageTitle: '...'
    }
  },
  metaInfo() {
    return {
      title: this.pageTitle
    }
  },
  computed: {
    venueId() {
      return this.$route.params.venue_id;
    }
  },
  mounted() {
    this.request();
  },
  methods: {
    request() {
      // @todo move to api/Venue.js
      axios.get('/api/venue/'+this.venueId).then(response => {
        this.venue = response.data;
        this.pageTitle = this.venue.name;
      });
    },
    eventCount(events) {
      return events.length;
    }
  }
}
</script>