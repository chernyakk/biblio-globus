<template>
    <div id="hotel-table">
        <div class="parsed" id="table">
            <div class="parsed__topfield">
                <div class="parsed__percent">
                    <label for="percent" class="parsed__label">% наценки</label>
                    <input v-model="percent" type="number" id="percent" class="parsed__input input-number" min="0">
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
                <div class="parsed__row --sub" v-for="({ id_hotel, room, prices, quota, duration }, id) in tours" :data-id="id" >
<!--                    <div @click="isShow = !isShow"></div>-->
                    <div class="parsed__td">
                        <strong>Отель:</strong> {{ hotelNames[id_hotel] }}<br>
                        <strong>Тип:</strong> {{ room }}<br>
                        <strong>Свободных мест:</strong> {{quota}}<br>
                        <strong>Количество ночей:</strong> {{duration}}
                    </div>
                    <div class="parsed__td">{{prices.total}}</div>
                    <div class="parsed__td">{{overprice(prices.total)}}</div>
<!--                    <transition name="slide">-->
<!--                        <div v-if="isShow" :data-toggle-id="id">-->
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
<!--                        </div>-->
<!--                    </transition>-->
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
                hotelNames: {
                    102616630651 : 'ГОРКИ ГОРОД, апарт-отель',
                    102610026739 : 'SIGMA SIRIUS, пансионат (бывш. кв. Александровский сад)',
                    102610026611 : 'GAMMA SIRIUS (бывш. кв. Чистые Пруды)',
                    102610084348 : 'ОК СОЧИ ПАРК ОТЕЛЬ',
                    102610172080 : 'DELTA SIRIUS, гостиница 3*',
                    102610171604 : 'DELTA SIRIUS, гостиница 3*',
                },
                isShow: false,
            };
        },
        props: ['tours', 'days'],
        methods: {
            overprice: function(price) {
                return (Number(price) * (1 + '.' + this.percent)).toFixed(0)
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
            }
        }
    }
</script>

<!--<style>-->

<!--.slide-enter-active {-->
<!--    -moz-transition-duration: 0.3s;-->
<!--    -webkit-transition-duration: 0.3s;-->
<!--    -o-transition-duration: 0.3s;-->
<!--    transition-duration: 0.3s;-->
<!--    -moz-transition-timing-function: ease-in;-->
<!--    -webkit-transition-timing-function: ease-in;-->
<!--    -o-transition-timing-function: ease-in;-->
<!--    transition-timing-function: ease-in;-->
<!--}-->

<!--.slide-leave-active {-->
<!--    -moz-transition-duration: 0.3s;-->
<!--    -webkit-transition-duration: 0.3s;-->
<!--    -o-transition-duration: 0.3s;-->
<!--    transition-duration: 0.3s;-->
<!--    -moz-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);-->
<!--    -webkit-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);-->
<!--    -o-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);-->
<!--    transition-timing-function: cubic-bezier(0, 1, 0.5, 1);-->
<!--}-->

<!--.slide-enter-to, .slide-leave {-->
<!--    max-height: 200px;-->
<!--    overflow: hidden;-->
<!--}-->

<!--.slide-enter, .slide-leave-to {-->
<!--    overflow: hidden;-->
<!--    max-height: 0;-->
<!--}-->
<!--</style>-->
