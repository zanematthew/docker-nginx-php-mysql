'use strict';
// https://stackoverflow.com/questions/42295340/how-to-clear-state-in-vuex-store
import * as types from '~/store/mutation-types';

const state = {
  default: {},
  // @todo maybe move to module
  social_account: { facebook: { avatar: '' } }
};

const getters = {
  // @todo maybe move to root vuex
  isLoggedIn: state => {
    return ! _.isEmpty(state.default);
  },
  avatar: state => {
    return _.isUndefined(state.social_account) ? '' : state.social_account.facebook.avatar;
  }
};

const actions = {};

const mutations = {
  [types.SET_AUTHUSER] (state, payload) {
    state.default = payload.default;
    state.social_account = payload.social_account;
  }
};

export default {
  state,
  getters,
  actions,
  mutations
};