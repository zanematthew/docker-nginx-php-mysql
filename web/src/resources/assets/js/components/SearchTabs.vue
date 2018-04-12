<template>
<div>
  <div class="row nav is-underlined is-tertiary">
    <router-link v-for="item in items" :key="item.id" :to="{
      name: 'search-results',
      params: item.params,
      query: query
    }" class="nav-item">{{ item.title }}</router-link>
  </div>

  <action-bar
    :type="type"
    :item="result"
    :key="result.id"
    v-for="result in results[type]"
    class="row"></action-bar>

</div>
</template>
<script>
import actionBar from '~/components/ActionBar';

export default {
  components: {
    actionBar
  },
  computed: {
    // This denotes the App\Model type
    // Although there is no "places".
    type() {
      return this.$route.params.type;
    },
    query() {
      return this.$route.query;
    },
  },
  data() {
    return {
      items: [
        {
          title: 'Places',
          params: { type: 'place' }
        },
        {
          title: 'Events',
          params: { type: 'event' }
        },
        {
          title: 'Venues',
          params: { type: 'venue' }
        },
      ],
      results: []
    }
  },
  // @todo Search to/from query params?
  mounted() {
    this.updateSearchType();
  },
  watch: {
    '$route' (to, from) {
      this.updateSearchType();
    }
  },
  methods: {
    // Need to result to prior ES(5?) style, due to the
    // way de-bounce works.
    updateSearchType: function () {
      this.$store.commit('UPDATE_SEARCH_TYPE', {
        type: this.$store.state.route.params.type
      });
    }
  }
}
</script>
<style lang="scss">
.is-tertiary {
  .nav-item {
    float: left;
    width: 33.33%;
    text-align: center;
  }
}
</style>