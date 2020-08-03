<template>
    <div id="hotel-table">
        <div class="parsed" id="table">
            <div class="parsed__topfield">
                <div class="parsed__percent">
                    <label for="percent" class="parsed__label">% наценки</label>
                    <input v-model="percent" type="number" id="percent" class="parsed__input input-number"
                           min="0" :style="{ width: percentLength }">
                </div>
                <div class="parsed__buttons">
                    <button class="cta form grey" onclick="location.reload()"> Вернуться </button>
                    <button class="cta form" v-on:click="giveFile(tours, percent)"> Выгрузить .xls </button>
                </div>
            </div>
            <div class="parsed__table">
                <div class="parsed__row --main">
                    <div class="parsed__td">
                        <strong>Места/питание</strong>
                    </div>
                    <div class="parsed__td">
                        <strong>Цена стандарт</strong>
                    </div>
                    <div class="parsed__td">
                        <strong>Цена с наценкой</strong>
                    </div>
                </div>
                <div class="parsed__row --sub" v-for="({ hotel, room, prices, quota, duration }, id) in tours"
                     v-bind:class="{ parsed__onclick : days }">
                    <div class="parsed__td" v-on:click.prevent="showList(id)">
                        <strong>Отель:</strong> {{ hotel }}<br>
                        <strong>Тип:</strong> {{ room }}<br>
                        <strong>Свободных мест:</strong> {{quota}}<br>
                        <strong>Количество ночей:</strong> {{duration}}
                    </div>
                    <div class="parsed__td" v-on:click="showList(id)">{{prices.total}}</div>
                    <div class="parsed__td" v-on:click="showList(id)">{{overprice(prices.total)}}</div>

                    <transition name="slide">
                        <div v-if="showing[id]">
                            <ul class="parsed__td--full" v-if="days">
                                <li class="parsed__row--small --main">
                                    <div class="parsed__td--small">
                                        <strong>Число</strong>
                                    </div>
                                    <div class="parsed__td--small">
                                        <strong>Цена стандарт</strong>
                                    </div>
                                    <div class="parsed__td--small">
                                        <strong>Цена с наценкой</strong>
                                    </div>
                                </li>
                                <li class="parsed__row--small" v-for="(price, date) in prices.separate">
                                    <div>{{ date }}</div>
                                    <div>{{ price }}</div>
                                    <div>{{ overprice(price) }}</div>
                                </li>
                            </ul>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        data() {
            return {
                percent: 50,
                hotelsToAPI: null,
                isShow: false,
                showing: {}
            };
        },
        created: function () {
            for (let data_id in this.tours) {
                Vue.set(this.showing, data_id, false)
            }
        },
        props: ['tours', 'days'],
        computed: {
            percentLength: function() {
                const min = 52;
                let now = String(this.percent).length < 2 ? 2 : String(this.percent).length;
                return Number(min + ((now - 2) * 10)) + 'px';
            }
        },
        methods: {
            overprice: function(price) {
                return (Number(price) * (1 + (this.percent) / 100)).toFixed(0)
            },
            giveFile: function (hotels, percent) {
                this.hotelsToAPI = hotels;
                this.percent = percent;
                axios
                    .post('/api/excel', {
                        hotels: this.hotelsToAPI,
                        percent: this.percent,
                        api_token: document.cookie.match ( '(^|;) ?' + 'api_token' + '=([^;]*)(;|$)' )[2],
                    })
                    .then(response => {
                        window.open(response.data)
                    })
            },
            showList: function(id) {
                this.showing[id] = !this.showing[id];
            }
        }
    }
</script>
