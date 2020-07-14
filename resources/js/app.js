require('./bootstrap');

window.Vue = require('vue');

import Vue from 'vue'
import moment from 'moment';
import RequestForm from "./components/RequestForm";
import HotelPrices from "./components/HotelPrices";

Vue.component('request-form', RequestForm);
Vue.component('hotels-prices', HotelPrices);
Vue.prototype.$moment = moment;


const app = new Vue({
    el: '#app',
});
