<template>
  <div v-if="venues">
    <search-result-venue :item="venue" :key="venue.id" v-for="venue in venues"></search-result-venue>
  </div>
  <div v-else class="align-center row is-item grid is-100">
    <icon name="refresh" spin></icon>
  </div>
</template>
<script>
import searchResultVenue from '~/components/SearchResultVenue';
import Search from '~/api/Search';

export default {
  components: {
    searchResultVenue
  },
  props: {
    state: {
      type: String
    }
  },
  data() {
    return {
      venues: {}
    }
  },
  mounted() {
    this.getVenues();
  },
  methods: {
    getVenues() {
      let coords = JSON.parse(sessionStorage.getItem('location'));

      Search.suggestion(venues => this.venues = venues, {
        type: 'venue',
        latlon: `${coords.lat},${coords.lon}`
      });
    }
  },
  watch: {
    '$route' (to, from) {
      this.getVenues();
    }
  },
  metaInfo() {
    return {
      title: `Venues`,
      titleTemplate: '%s | My BMX Events'
    }
  }
}
</script>
