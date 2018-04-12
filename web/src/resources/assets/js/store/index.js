'use strict';
import Vue from 'vue';
import Vuex from 'vuex';
import * as actions from './actions';
import * as getters from './getters';
import user from './modules/user';
import schedule from './modules/schedule';
import library from './modules/library';
import search from './modules/search';

Vue.use(Vuex);

export default new Vuex.Store({
    actions,
    getters,
    modules: {
        user,
        schedule,
        library,
        search
    },
    strict: true
});