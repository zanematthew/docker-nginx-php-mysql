<template>
  <div v-if="schedule">
    <div v-if="count === 0">
      <router-link :to="{ name: 'event-list-page', params: { when: 'this-month' } }" class="grid row is-item title align-center">Add Events to this Schedule.</router-link>
    </div>
    <div v-else>
      <action-bar :type="'event'" :item="item" :key="item.id" v-for="item in schedule.events"></action-bar>
    </div>
  </div>
  <div v-else class="align-center row is-item grid is-100">
    <icon name="refresh" spin></icon>
  </div>
</template>
<script>
import actionBar from '~/components/ActionBar';

export default {
  components: {
    actionBar
  },
  props: {
    id: {
      type: [Number, String],
      required: true
    }
  },
  computed: {
    schedule() {
      return this.$store.getters.getEventsInScheduleByScheduleId(this.id);
    },
    // @todo getter schedule event count
    count() {
      return _.isUndefined(this.schedule.events) ? 0 : this.schedule.events.length;
    },
  },
  // @todo this is throwing an error, it is running before the computed property schedule()
  metaInfo() {
    let name = '';
    if (_.isUndefined(this.schedule)){
      name = 'no name';
    } else {
      name = this.schedule.name;
    }
    console.log(name);
    return {
      title: name,
      titleTemplate: 'Schedules >> %s | My BMX Events'
    }
  },
}
</script>