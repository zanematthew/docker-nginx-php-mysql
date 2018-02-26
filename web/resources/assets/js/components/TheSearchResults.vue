<template>
<div class="search-results-container">
  <close></close>
  <!--
  This base form style should be used for;
    - search field
    - action/ add new schedule field
    - schedules/all add new schedule
  -->
  <form class="grid row is-item form icon-inside-field">
    <icon name="search"></icon>
    <input
      type="text"
      placeholder="Search..."
      v-model="currentText"
      />
  </form>

  <!-- Tabs -->
  <div class="row nav is-underlined is-tertiary">
    <router-link v-for="item in items" :key="item.id" :to="{
      name: 'search-results',
      params: item.params
    }" class="nav-item">{{ item.title }}</router-link>
  </div>

  <!-- Search Results -->
  <strong
    v-if="currentText === ''"
    class="row grid is-item is-100 not-title">
    Suggestions
  </strong>

  <div
    v-if="activeSearchResults[type].length > 0"
    v-for="searchResult in activeSearchResults[type]"
    :key="searchResult.id"
    >
    <search-result-venue
      v-if="type === 'venue'"
      :item="searchResult"></search-result-venue>
    <search-result-event
      v-if="type === 'event'"
      :item="searchResult"></search-result-event>
  </div>

  <div v-else class="align-center row is-item is-100">
    <icon name="refresh" spin></icon>
  </div>

</div>
</template>
<script>
import { mapState } from 'vuex';
import searchResultEvent from '~/components/SearchResultEvent';
import searchResultVenue from '~/components/SearchResultVenue';
import close from '~/components/Close';

export default {
  components: {
    searchResultEvent,
    searchResultVenue,
    close
  },
  computed: {
    ...mapState({
      searchText:       state => state.search.text.current,
      type:             state => state.route.params.type,
      results:          state => state.search.results,
      suggestedResults: state => state.search.suggestedResults
    }),
    currentText: {
      get() {
        return this.searchText;
      },
      set(value) {
        if (_.trim(value).length === 0) {
          this.resetSearch();
          return;
        }
        this.$store.commit('UPDATE_KEYWORD', value);
      }
    },
    coords: function () {
      return JSON.parse(sessionStorage.getItem('location'));
    }
  },
  data() {
    return {
      // @todo places will be for state, city, zip
      // @todo move count to be part of state.
      // Restructure results state as;
      // search.results[type] = [ count => 0, items = [] ];
      items: [
        // {
        //   title: 'Places',
        //   params: { type: 'place' }
        // },
        {
          title: 'Events',
          params: { type: 'event' }
        },
        {
          title: 'Venues',
          params: { type: 'venue' }
        },
      ],
      activeSearchResults: { event: {}, venue: {} }
    }
  },
  mounted() {
    this.updateSearchType();
    this.$store.commit('SET_SEARCH_COORDS', this.coords);
    this.setSuggestedResults();
  },
  watch: {
    '$route' (to, from) {
      this.updateSearchType();
    },
    currentText: function () {
      this.search();
    }
  },
  methods: {
    search: _.debounce(function() {
      if (_.isEmpty(this.currentText)) {
        return;
      }
      this.$store.commit('CLEAR_SEARCH_RESULTS');
      this.$store.dispatch('getSearchResults').then(response => {
        this.activeSearchResults[this.type] = this.results[this.type];
      });
    }, 250),

    /**
     * Simply put, our search type (sort of) maps to a model in the API.
     *
     * Note; "places", is not a model, its just a flag to narrow search
     * results based on a place, i.e., city, state.
     */
    updateSearchType() {
      this.$store.commit('UPDATE_SEARCH_TYPE', {
        type: this.$store.state.route.params.type
      });
      if (_.isEmpty(this.activeSearchResults[this.type])) {
        this.setSuggestedResults();
      }
      this.$store.commit('CLEAR_KEYWORD');
    },

    resetSearch() {
      this.$store.commit('CLEAR_KEYWORD');
      this.$store.commit('CLEAR_SEARCH_RESULTS');
      this.setSuggestedResults();
    },

    setSuggestedResults() {
      this.$store.dispatch('getSearchSuggestedResults').then(response => {
        this.activeSearchResults[this.type] = this.suggestedResults[this.type];
      });
    }
  }
}
</script>
<style lang="scss" scoped>
  @import "../../sass/variables";
  .icon-inside-field {
    position: relative;
    padding: 10px;
    input[type="text"] {
      padding-left: 30px;
    }
    .fa-icon {
      position: absolute;
      top: 22px;
      left: 20px;
    }
  }
  .is-tertiary {
    .nav-item {
      float: left;
      width: 50%;
      text-align: center;
    }
  }
  .search-results-container {
    input[type="text"] {
      background-color: rgba(0, 0, 0, 0.04);
      border: 0;
    }
  }
</style>