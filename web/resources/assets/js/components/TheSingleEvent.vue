<template>
<div>
  <action-bar :type="'event'" :item="event"></action-bar>

  <!-- Event Detail -->
  <div class="row is-item grid is-100" v-if="event.fee">
    <strong>Fee</strong> {{ formatCurrency(event.fee) }}<br />
    <strong>Registration Start</strong> {{ formatTime(event.start_date + ' ' + event.registration_start_time) }}<br />
    <strong>Registration End</strong> {{ formatTime(event.start_date + ' ' + event.registration_end_time) }}<br />
  </div>

  <!-- Event Schedule -->
  <div class="row is-item grid is-100" v-if="event.event_schedule_uri">
    <a :href="event.event_schedule_uri" target="_blank">Schedule (PDF)</a>,
    <a :href="event.flyer_uri" target="_blank">Flier (PDF)</a>
  </div>

  <!-- Map -->
  <div class="row">
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

  <!-- Venue Detail -->
  <venue-contact :venue="event.venue" class="row is-item grid is-100"></venue-contact>

  <!-- Tabs -->
  <tabs-events></tabs-events>
</div>
</template>

<script>
import MyMixin from '~/mixin.js';
import moment from 'moment';

import venueContact from '~/components/VenueContact';
import actionBar from '~/components/ActionBar';
import tabsEvents from '~/components/TabsEvents';

import * as VueGoogleMaps from 'vue2-google-maps';
import Vue from 'vue';

Vue.use(VueGoogleMaps, {
  load: {
    key: 'AIzaSyAVD89EibFYXW5pAA7DeVDMv3qfB4gtigg',
    v: '3.27',
  }
});

var numeral = require('numeral');

export default {
  mixins: [MyMixin],
  components: {
    venueContact,
    actionBar,
    tabsEvents
  },
  props: {
    id: {
      type: [Number, String],
      required: true
    },
    slug: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      event: { venue: { city: { states: '' } } },
      center: { lat: 39.2904, lng: 76.6122 },
      markers: [],
      defaultOptions: {
        scrollwheel: false,
        mapTypeControl: false,
      },
      relatedEvents: [],
      pageTitle: '...'
    }
  },
  metaInfo() {
    return {
      title: this.pageTitle
    }
  },
  mounted() {
    this.request();
  },
  methods: {
    formatTime(time) {
      return moment(time).format('h:mm a');
    },
    formatCurrency(number) {
      return numeral(number).format('$0,0[.]00');
    },
    request() {
      // @todo move to api/Event.js
      axios.get('/api/event/'+this.id+'/').then(response => {
        this.event = response.data;
        this.center.lat = parseInt(response.data.venue.lat);
        this.center.lng = parseInt(response.data.venue.long);
        this.markers = [{
          position: {lat: parseInt(response.data.venue.lat), lng: parseInt(response.data.venue.long)}
        }];
        this.pageTitle = `${this.event.venue.name} // ${this.event.title}`;
        return response.data;
      }).then(response => {
        // @todo add to api/Event.js
        axios.get('/api/event/', {
          params: {
            this_month: true,
            venue_id: response.venue_id,
          }
        }).then(response => {
          this.relatedEvents = response.data;
        });
      });
    }
  },
  watch: {
    '$route' (to, from) {
      this.request();
    }
  }
}
</script>
<style lang="scss">
@import "../../sass/variables";
.vue-map-container {
  border: 1px solid $light-gray;
}
</style>
