<template>
<div>
  <schedule-button-add></schedule-button-add>
  <action-bar :type="'schedule'" :item="schedule" :key="schedule.id" v-for="schedule in schedules" class="row"></action-bar>
</div>
</template>
<script>
import scheduleButtonAdd from '~/components/ScheduleButtonAdd';
import actionBar from '~/components/ActionBar';
import { mapState } from 'vuex';

export default {
  components: {
    scheduleButtonAdd,
    actionBar
  },
  computed: {
    ...mapState({
      schedules: state => state.schedule.schedules
    })
  },
  metaInfo() {
    return {
      titleTemplate: 'Schedules | My BMX Events'
    }
  },
}
</script>
