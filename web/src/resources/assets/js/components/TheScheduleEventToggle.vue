<template>
  <div class="content">
    <div v-for="schedule in schedules" :key="schedule.id" class="grid is-100 row is-item pseudo-link" v-on:click="addTo(schedule)">
      <!--
        Needs to be a component.
        ActionBar based, rename, delete
      -->
      <div class="title">
        {{ schedule.name }}
        <div v-if="schedule.events" class="not-title align-right">{{ schedule.events.length }}</div>
        <div v-else class="not-title align-right">0</div>
      </div>
    </div>
    <schedule-button-add></schedule-button-add>
  </div>
</template>
<script>
import router from '~/router';
import scheduleButtonAdd from '~/components/ScheduleButtonAdd';
import Noty from 'noty';

export default {
  components: {
    scheduleButtonAdd
  },
  data() {
    return {
      id: _.parseInt(this.$route.params.id)
    }
  },
  computed: {
    schedules() {
      return this.$store.state.schedule.schedules;
    }
  },
  methods: {
    addTo(schedule) {
      this.$store.dispatch('toggleEventToSchedule', {
        eventId: this.id,
        scheduleId: schedule.id
      }).then(response => {
        if (_.isUndefined(response)) {
          return;
        }
        new Noty({
          text: response.attached ? 'Added' : 'Removed',
          type: 'success',
          theme: 'relax',
          timeout: 300,
          layout: 'top',
          progressBar: true,
          animation: {
            open: 'animated fadeInDownBig', // Animate.css class names
            close: 'animated fadeOutUpBig' // Animate.css class names
          }
        }).show();
        router.go(-1);
      });
    }
  },
  metaInfo() {
    return {
      titleTemplate: 'Action >> Add To Schedule | My BMX Events'
    }
  }
}
</script>
<!-- Mabye we can just imoprt Noty this way? -->
<!-- See; https://vue-loader.vuejs.org/en/start/spec.html -->
<!-- <style src="todomvc-app-css/index.css"> -->
