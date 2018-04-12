<template>
  <div v-if="hasHistory" class="grid row is-item close-container" v-on:click.prevent="back">
    <icon name="chevron-left"></icon>
  </div>
</template>
<script>
import router from '~/router';
export default {
  computed: {
    hasHistory() {
      return window.history.length > 2 ? 1 : 0;
    }
  },
  methods: {
    back() {
      this.$emit('beforeBack');
      router.go(-1);
      // @todo not sure yet how to handle this,
      // maybe have a; < and an x icon
      // if (this.$route.name == 'venue-single-events') {
      //   router.push({ name: 'venues' });
      // } else if (this.$route.name == 'event-single-page') {
      //   router.push({ name: 'events' });
      // } else if (window.history.length > 2) {
      //   router.go(-1);
      // } else {
      //   console.log('no history');
      // }
    }
  }
}
</script>
<style>
.close-container {
  cursor: pointer;
  max-height: 48px;
  min-height: 48px;
}
</style>