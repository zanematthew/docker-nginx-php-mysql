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

  <div class="content row">
    <gmap-map
      :options="defaultOptions"
      :draggable="false"
      :center="center"
      :zoom="7"
      style="width: 100%; height: 300px"
    >
      <gmap-marker
        :key="index"
        v-for="(m, index) in markers"
        :position="m.position"
        @click="center=m.position"
      ></gmap-marker>
    </gmap-map>
  </div>
  <tabs-events></tabs-events>
</div>
</template>
<script>

// @TODO fix this.
// :city="venue.city.name"
// :state_abbr="venue.city.states[0].abbr"
import * as VueGoogleMaps from 'vue2-google-maps';
import Vue from 'vue';

Vue.use(VueGoogleMaps, {
  load: {
    key: 'AIzaSyAVD89EibFYXW5pAA7DeVDMv3qfB4gtigg',
    v: '3.27',
  }
});

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
      center: { lat: 39.2904, lng: 76.6122 },
      markers: [],
      defaultOptions: {
        mapTypeControl: false,
        scrollwheel: false
      },
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
        this.center.lat = parseInt(response.data.lat);
        this.center.lng = parseInt(response.data.long);
        this.markers = [{
          position: {lat: parseInt(response.data.lat), lng: parseInt(response.data.long)}
        }];
        this.pageTitle = this.venue.name;
      });
    },
    eventCount(events) {
      return events.length;
    }
  }
}
</script>