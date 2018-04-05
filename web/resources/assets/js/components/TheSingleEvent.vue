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

import Vue from 'vue';

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
