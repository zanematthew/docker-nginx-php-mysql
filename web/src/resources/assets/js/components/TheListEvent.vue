<template>
<div>
  <tabs-events-basic></tabs-events-basic>
</div>
</template>
<script>
import moment from 'moment';
import tabsEventsBasic from '~/components/TabsEventsBasic';

export default {
  components: {
    tabsEventsBasic
  },
  props: {
    when: {
      type: String,
      required: true
    },
    type: String
  },
  metaInfo() {
    return {
      title: `${this.getMonthName()}`,
      titleTemplate: '%s | My BMX Events'
    }
  },
  methods: {
    getMonthName() {
      var monthName;
      if (this.when == 'this-month') {
        monthName = moment().format('MMMM');
      } else if (this.when == 'next-month') {
        monthName = moment().add(1, 'month').format('MMMM');
      } else if (this.when == 'upcoming') {
        monthName = 'Upcoming';
      }
      return monthName;
    }
  }
}
</script>