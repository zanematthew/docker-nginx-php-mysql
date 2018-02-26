'use strict';
class Schedule {
  static toggleEventToSchedule(then, args) {
    return axios.post(`/api/user/schedule/toggle/${args.eventId}/to/${args.scheduleId}/`, args).then(({data}) => then(data));
  }

  static getScheduels(then) {
    return axios.get('/api/user/schedule/').then(({data}) => then(data));
  }

  static addSchedule(then, name) {
    return axios.post('/api/user/schedule/new/', {
      name: name
    }).then(({data}) => then(data));
  }

  static delete(then, id) {
    return axios.delete(`/api/user/schedule/${id}/delete/`).then(({data}) => then(data));
  }

  static rename(then, id, name) {
    return axios.post(`/api/user/schedule/${id}/update/`, {
      id: id,
      name: name
    }).then(({data}) => then(data));
  }

  static events(then, id) {
    return axios.get(`/api/user/schedule/events/${id}/`).then(({data}) => then(data));
  }
}
export default Schedule;