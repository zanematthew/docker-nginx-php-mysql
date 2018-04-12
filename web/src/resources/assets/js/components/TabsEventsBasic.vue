<template>
<div>
  <div class="row nav is-underlined is-tertiary">
    <router-link v-for="item in items" :key="item.id" :to="{
      name: route_name,
      params: item.params,
      query: query
    }" class="nav-item">{{ item.title }}</router-link>
  </div>

  <div v-if="totalEvents === 0" class="row is-item">
    <div class="grid is-100">
      <p>There are <strong>No events</strong> at this time. Please refer to the Venue website.</p>
    </div>
  </div>
  <search-result-event :item="event" :key="event.id" v-for="event in events"></search-result-event>
</div>
</template>
<script>
import Search from '~/api/Search';
import searchResultEvent from '~/components/SearchResultEvent';

export default {
  computed: {
    when() {
      return this.$route.params.when;
    },
    query() {
      return this.$route.query;
    },
    totalEvents() {
      return this.events.length;
    }
  },
  components: {
    searchResultEvent
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
          title: 'Upcoming',
          params: { when: 'upcoming' }
        },
      ],
      events: {},
      route_name: this.$route.name
    }
  },
  mounted() {
    this.getEvents();
  },
  methods: {
    getEvents() {
      let coords = JSON.parse(sessionStorage.getItem('location'));
      Search.date(response => this.events = response, this.when, {
        latlon: `${coords.lat},${coords.lon}`
      });
    }
  },
  watch: {
    '$route' (to, from) {
      this.getEvents();
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