<template>
  <div class="row is-item is-100">
    <!-- Library Area -->
    <library-item-toggle :item_id="item.id" :item_type="'venue'"></library-item-toggle>

    <!-- Image Area -->
    <div class="grid is-15" v-if="item.image_uri">
      <div class="avatar">
        <img :src="item.image_uri" itemprop="image">
      </div>
    </div>

    <!-- Title -->
    <!-- Meta -->
    <router-link
      :to="{
      name: 'venue-single-events',
      params: { venue_id: item.id, slug: item.slug, when: 'this-month' }
      }"
      exact
      class="text-area">
      <div class="title">{{ item.name }}</div>
      <div class="not-title">{{ item.distance_from }} miles &bull; {{ item.city }}, {{ item.state_abbr }}</div>
    </router-link>
  </div>
</template>
<script>
import libraryItemToggle from '~/components/LibraryItemToggle';

export default {
  components: {
    libraryItemToggle
  },
  props: {
    item: Object
  }
}
</script>
<style lang="scss" scoped>
.text-area {
  width: 75%;
  padding: 0 15px;
  float: left;
}
</style>