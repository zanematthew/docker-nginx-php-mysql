<template>
  <div class="row is-item is-100">

    <!-- Library Area -->
    <library-item-toggle :item_id="item.id" :item_type="'event'"></library-item-toggle>

    <!-- Image Area -->
    <div class="avatar-container" v-if="item.image_uri">
      <div class="avatar">
        <img :src="item.image_uri" itemprop="image">
      </div>
    </div>

    <!-- Text Area -->
    <router-link
      :to="{
      name: 'event-single-page',
      params: { id: item.id, slug: item.slug, when: 'this-month' },
      query: { venue_id: item.venue_id }
      }"
      class="text-area">
      <div class="title">{{ item.title }}</div>
      <div class="not-title">{{ date }} &bull; {{ item.distance_from }} miles &bull; {{ item.state_abbr }} &bull; {{ fromNow(item.registration) }}</div>
    </router-link>
  </div>
</template>
<script>
import MyMixin from '~/mixin.js';
import libraryItemToggle from '~/components/LibraryItemToggle';
import moment from 'moment';

export default {
  components: {
    libraryItemToggle
  },
  mixins: [MyMixin],
  props: {
    item: Object
  },
  computed: {
    date() {
      return moment(this.item.registration).format('MMM D');
    }
  }
}
</script>
<style lang="scss" scoped>
.avatar-container {
  float: left;
  width: 15%;
  text-align: center;
}
.text-area {
  width: 75%;
  float: left;
  padding-right: 20px;
}
</style>