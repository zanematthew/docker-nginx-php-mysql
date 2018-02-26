'use strict';
import moment from 'moment';

class Search {
  /**
   * Retrieve results based on a phrase and lat/long from Elasticsearch.
   *
   * @param  {promise} then    The promise
   * @param  {object}  payload Object containing text and latlon.
   * @return {array}           Event or Venue document from Elasticsearch.
   */
  static phrase(then, payload) {
    return axios.get(`/api/search/${payload.type}/phrase`, {
      params: {
        text: payload.text,
        latlon: payload.latlon
      }
    }).then(({data}) => then(data));
  }

  /**
   * Retrieve suggested results based on and lat/long from Elasticsearch.
   *
   * @param  {promise} then    The promise
   * @param  {object}  payload Object containing text and latlon.
   * @return {array}           Event or Venue document from Elasticsearch.
   */
  static suggestion(then, payload) {
    return axios.get(`/api/search/${payload.type}/suggestion`, {
      params: {
        latlon: payload.latlon
      }
    }).then(({data}) => then(data));
  }

  /**
   * Retrieve results based on a date range, and lat/long from Elasticsearch.
   *
   * @param  {promise} then    The promise
   * @param  {object}  payload Object containing date range (to, from), text and latlon.
   * @return {array}           Event or Venue document from Elasticsearch.
   */
  static date(then, when, payload) {

    let range  = {};

    // API date format 2017-12-10
    switch (when) {
      case 'this-month':
        range = {
          from: moment().format(),
          to: moment().endOf('month').format()
        }
        break;
      case 'next-month':
        range = {
          from: moment().add(1, 'months').startOf('month').format(),
          to: moment().add(1, 'months').endOf('month').format()
        };
        break;
      case 'upcoming':
        range = {
          from: moment().format(),
          to: moment().add(6, 'months').endOf('month').format()
        };
        break;
      default:
        range = {
          from: moment().format(),
          to: moment().endOf('month').format()
        };
        break;
    }

    return axios.get('/api/search/event/date', {
      params: {
        ...range,
        latlon: payload.latlon
      }
    }).then(({data}) => then(data));
  }
}
export default Search;