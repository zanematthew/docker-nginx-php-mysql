<template>
  <div class="grid is-100 row is-item" v-if="schedule">
    <form class="form" v-on:submit.prevent="updateName">
      <input type="text" :value="schedule.name" ref="new_name" />
      <div class="not-title">Updated {{ fromNow(schedule.updated_at) }}</div>
      <input type="submit" value="Save" class="inline-button" />
    </form>
  </div>
</template>
<script>
import MyMixin from '~/mixin.js';
export default {
  mixins: [MyMixin],
  props: {
    // Router props when accessed via direct URL, i.e.,
    // site.com/123 are shown as Strings, when clicked
    // through from parent --> child, props are shown
    // as Number.
    id: [Number, String],
    type: String
  },
  data() {
    return {}
  },
  computed: {
    schedule() {
      return this.$store.getters.getScheduleById(this.id);
    }
  },
  methods: {
    // @todo Is this appropriate to use Ref's?
    updateName(e) {
      this.$store.dispatch('rename', {
        id: this.schedule.id,
        name: this.$refs.new_name.value
      }).then(response => {
        // Its better to re-fetch all library items
        // for the user/provider performing the rename,
        // than to add the JS support to re-evaluate
        // which items from their schedule are in their
        // library, and re-set the library items.
        this.$store.dispatch('fetchAllLibraryItems');
      });
    }
  },
  mounted() {}
}
</script>
<style lang="scss" scoped>
@import "../../sass/variables";
.inline-button {
  margin-top: 20px;
}
</style>