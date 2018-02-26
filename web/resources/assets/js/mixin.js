'use strict';

import moment from 'moment';
export default {
  methods: {
    fromNow(start_date) {
      return moment(start_date).fromNow();
    },
    startDate(start_date) {
      if (_.isUndefined(start_date)) {
        return 'N/A';
      }
      var startDate = moment(start_date).format("ddd MMM D, YYYY");
      return startDate;
    },
    startEndDate(start_date, end_date) {
      if (typeof start_date == 'undefined' || typeof end_date == 'undefined') {
        return;
      }

      var startMonthDate = moment(start_date).format("MMM D");

      if (!end_date){
        return startMonthDate + ", " + moment(start_date).format("YYYY");
      } else if (start_date == end_date) {
        return startMonthDate + ", " + moment(end_date).format("YYYY");
      } else {
        var endDate = moment(end_date).format("D");
        return startMonthDate + " \u2013 " + endDate + ", " + moment(end_date).format("YYYY");
      }
    },
    formatTime(time) {
      return moment(time).format("ddd MMM D, YYYY");
    }
  }
};
