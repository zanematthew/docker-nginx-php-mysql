<template>
<div>
  <div class="row nav is-underlined is-tertiary">
    <router-link v-for="item in items" :key="item.id" :to="{
      name: route_name,
      params: item.params,
      query: query
    }" class="nav-item">{{ item.title }}</router-link>
  </div>

  <div v-if="events.total === 0" class="row is-item">
    <div class="grid is-100">
      <p>There are <strong>No events</strong> at this time. Please refer to the Venue website.</p>
    </div>
  </div>

  <action-bar :type="'event'" :item="event" :key="event.id" v-for="event in events.data" class="row"></action-bar>

  <the-pager :data="events"></the-pager>
</div>
</template>
<script>
import thePager from '~/components/ThePager';
import actionBar from '~/components/ActionBar';
import Event from '~/api/Event';

export default {
  computed: {
    venue_id() {
      return this.$route.params.venue_id;
    },
    when() {
      return this.$route.params.when;
    },
    query() {
      return this.$route.query;
    }
  },
  components: {
    thePager,
    actionBar
  },
  data() {
    return {
      items: [
        {
          title: 'This Month',
          params: { when: 'this-month' }
        },
        {
          title: 'Next Month',
          params: { when: 'next-month' }
        },
        {
          title: 'All Upcoming',
          params: { when: 'upcoming' }
        },
      ],
      events: {},
      route_name: this.$route.name
    }
  },
  mounted() {
    this.request();
  },
  methods: {
    request() {
      // @todo once we add the option to add events
      // this needs to be handled via the event store.
      Event.events(
        events => this.events = events,
        this.when,
        _.merge(this.$route.query, {venue_id: this.venue_id})
      );
    }
  },
  watch: {
    '$route' (to, from) {
      this.request();
    }
  }
}
</script>
<style lang="scss" scoped>
@import "../../sass/variables";
.is-tertiary {
  .nav-item {
    float: left;
    width: 33.33%;
    text-align: center;
  }
}
</style>