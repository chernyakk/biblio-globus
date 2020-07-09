<template>
    <div id="hotel-table">
        <div class="parsed" id="table">
            <div class="parsed__topfield">
                <div class="parsed__percent">
                    <label for="percent" class="parsed__label">% наценки</label>
                    <input v-model="percent" type="number" id="percent" class="parsed__input input-number">
                </div>
                <div class="parsed__buttons">
                    <button class="cta form grey" onclick="location.reload()"> Вернуться </button>
                    <button class="cta form" v-on:click="giveFile(tours, percent)"> Выгрузить .xls </button>
                </div>
            </div>
            <table class="parsed__table">
                <thead>
                    <tr class="parsed__row --main">
                        <th class="col-6">
                            Места/питание
                        </th>
                        <th class="col-3">
                            Цена стандарт
                        </th>
                        <th class="col-3">
                            Цена с наценкой
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="parsed__row --sub" v-for="{ id_hotel, room, prices, quota, duration } in tours">
                        <td class="col-6">
                            <strong>Отель:</strong> {{ hotelNames[id_hotel] }},<br>
                            <strong>Тип:</strong> {{ room }},<br>
                            <strong>Свободных мест:</strong> {{quota}},<br>
                            <strong>Количество ночей:</strong> {{duration}}
                        </td>
                        <td class="col-3">{{prices[0].amount}}</td>
                        <td class="col-3">{{(Number(prices[0].amount) * (1 + '.' + percent)).toFixed(0)}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        data() {
            return {
                percent: 10,
                hotelsToAPI: null,
                targetFile: false,
                hotelNames: {
                    102616630651 : 'ГОРКИ ГОРОД, апарт-отель',
                    102610026739 : 'SIGMA SIRIUS, пансионат (бывш. кв. Александровский сад)',
                    102610026611 : 'GAMMA SIRIUS (бывш. кв. Чистые Пруды)',
                    102610084348 : 'ОК СОЧИ ПАРК ОТЕЛЬ',
                    102610145618 : 'ОЛИМПИЙСКИЙ ПАРК'
                },
            };
        },
        props: ['tours'],
        methods: {
            giveFile: function (hotels, percent) {
                this.hotelsToAPI = hotels;
                this.percent = percent;
                axios
                    .post('/api/excel', {
                        hotels: this.hotelsToAPI,
                        percent: this.percent,
                    })
                    .then(response => {
                        window.open(response.data)
                        this.targetFile = response.data;
                    })
            }
        }
    }
</script>
