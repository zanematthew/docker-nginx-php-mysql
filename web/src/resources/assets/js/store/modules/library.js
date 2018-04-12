'use strict';

import Library from '~/api/Library';
import * as types from '~/store/mutation-types';

/**
 * The initial state of our scheduling module.
 */
const state = {
  // These are array of IDs only.
  event: [],
  venue: [],
  schedule: [],
};

/**
 * Data that needs to be computed based on the current state of the application.
 */
const getters = {
  /**
   * Determine if a given item is in the users Library.
   */
  isItemInLibrary: (state) => (item_id, item_type) => {
    return ! _.isUndefined(_.find(state[item_type], {id: item_id}));
  },
  // @todo add a new getter
  // Get library items by Type, an item is stored elsewhere, i.e., this
  // is not the single source of truth. If an item name changes, i.e.,
  // a schedule is already in the library, and then renamed, this
  // currently does not reflect that change.
  //
  // if state[item_type] === 'schedules'
  //    rebuild state[item_type] from matching IDs found in this.state.schedule.schedules
  //
};

/**
 * Actions commit mutations, these are to be used for asynchronous request.
 */
const actions = {
  /**
   * Retrieve all items from the endpoint, and commit/add them
   * to the state.
   */
  fetchAllLibraryItems({commit}) {
    return new Promise((resolve) => {
      Library.getIndex(response => {
        commit(types.SET_LIBRARY_ITEMS, response);
        resolve(response);
      });
    });
  },

  /**
   * Send the request to add/remove an item from the
   * users library.
   */
  toggleLibraryItem({commit, state}, payload) {
    Library.toggleItem(response => {
      commit(types.TOGGLE_LIBRARY_ITEM, {
        response: response,
        item_type: payload.item_type
      });
    }, {...payload});
  }
};

/**
 * The ONLY way to update the state of our store, is by committing a mutation.
 */
const mutations = {
  /**
   * Set the state of our application.
   */
  [types.SET_LIBRARY_ITEMS] (state, payload) {
    var items = _.mapKeys(payload, function(value, key) {
      return _.toLower(_.replace(key, 'App\\', ''));
    });
    state.event    = items.event || [];
    state.venue    = items.venue || [];
    state.schedule = items.schedule || [];
  },

  /**
   * Add/remove an item from the state of our application.
   */
  [types.TOGGLE_LIBRARY_ITEM] (state, payload) {
    if (payload.response.attached === false){
      let key = _.findKey(state[payload.item_type], {id: payload.response.detached.id});
      Vue.delete(state[payload.item_type], key);
    } else if (payload.response.detached === false) {
      state[payload.item_type].push(payload.response.attached);
    } else {
      return 'Broken';
    }
  }
};

export default {
  state,
  getters,
  actions,
  mutations
};