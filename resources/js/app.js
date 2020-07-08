require('./bootstrap');

window.Vue = require('vue');

import Vue from 'vue'
import RequestForm from "./components/RequestForm";
import HotelPrices from "./components/HotelPrices";

Vue.component('request-form', RequestForm);
Vue.component('hotels-prices', HotelPrices);

const app = new Vue({
    el: '#app',
});
