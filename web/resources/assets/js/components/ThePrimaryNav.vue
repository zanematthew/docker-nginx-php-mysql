<template>
<div class="masthead row">
  <div class="nav is-underlined is-primary container">
    <div class="another-div row">
      <router-link v-for="item in items" :key="item.id" :to="{
        name: item.id,
        params: item.params,
      }"
      class="nav-item"
      ><icon :name="item.icon"></icon></router-link>
      <router-link :to="{ name: 'schedules' }" class="nav-item icon-avatar">
        <span class="avatar"><img :src="avatar" /></span>
      </router-link>
    </div>
  </div>
</div>
</template>
<script>
import MyMixin from '~/mixin.js';
import { mapGetters } from 'vuex';

export default {
  mixins: [MyMixin],
  data() {
    return {
        items: [
          {
            name: 'Search',
            id: 'search-results',
            icon: 'search',
            params: { type: 'event' }
          },
          {
            name: 'Events',
            id: 'events',
            icon: 'calendar',
            params: { 'when': 'this-month' }
          },
          {
            name: 'Venues',
            id: 'venues',
            icon: 'map-o',
          },
          {
            name: 'Collections',
            id: 'collection',
            icon: 'star-o'
          }
        ]
    }
  },
  computed: {
    ...mapGetters([
      'avatar'
    ])
  }
}
</script>
<style lang="scss" scoped>
@import "../../sass/variables";
.masthead {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
}

.is-primary {
  .nav-item {
    padding: 10px 0 10px;
    text-align: center;
    width: 20%;
    float: left;
    margin-bottom: -1px;
  }
}

.nav-item {
 &.icon-avatar {
    padding: 5px 0 0;
  }
}

.another-div {
  background: #f5f5f5;
  border: solid $medium-gray;
  border-width: 1px 0 0;
}
</style>