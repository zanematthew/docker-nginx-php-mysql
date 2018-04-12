<template>
  <div class="row is-item">
    <span v-if="data.last_page > 1">
      <router-link :to="{
        name: router_view,
        query: Object.assign({}, this.$route.query, {
          page: this.nextPrevPage( data.prev_page_url )
        })
      }" class="nav-item align-left grid is-50" exact>
        <icon name="angle-left" scale="2"></icon>
      </router-link>
      <router-link :to="{
        name: router_view,
          query: Object.assign({}, this.$route.query, {
            page: this.nextPrevPage( data.next_page_url )
          })
        }" class="nav-item align-right grid is-50" exact>
        <icon name="angle-right" scale="2"></icon>
      </router-link>
    </span>
  </div>
</template>
<script>
var URL = require('url-parse');

export default {
  props: {
    name: String,
    data: Object,
  },
  data() {
    return {
      router_view: this.$route.name
    }
  },
  methods: {
    nextPrevPage(url) {
      var parsed = new URL(url, true),
          pageNumber = parsed.query.page;
      return pageNumber || 1;
    }
  }
};
</script>