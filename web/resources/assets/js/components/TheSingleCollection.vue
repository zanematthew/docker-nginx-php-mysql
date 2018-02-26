<template>
  <!-- @todo handle when there are no schedules.
  There are no <strong>Schedules</strong> added to your Collection. -->
  <div v-if="items">
    <action-bar :type="item_type" :item="item" :key="item.id" v-for="item in items" class="row"></action-bar>
  </div>
  <div v-else class="align-center row is-item grid is-100">
    <icon name="refresh" spin></icon>
  </div>
</template>
<script>
import actionBar from '~/components/ActionBar';

export default {
  components: {
    actionBar,
  },
  props: {
    item_type: String
  },
  computed: {
    items() {
      return this.$store.state.library[this.item_type];
    }
  },
  metaInfo() {
    return {
      title: _.startCase(this.item_type),
      titleTemplate: 'Collections >> %s | My BMX Events'
    }
  },
}
</script>