<template>
    <div id="hotel-table">
        <div class="parsed" id="table">
            <div class="parsed__percent">
                <label for="percent" class="parsed__label">% наценки</label>
                <input v-model="percent" type="number" id="percent" class="parsed__input input-number">
                <button> Вернуться </button>
                <button> Выгрузить .xls </button>
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
                            Свободных мест: {{quota}},<br>
                            Количество ночей: {{duration}}
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
    export default {
        data() {
            return {
                percent: 10,
                hotelNames: {
                    102616630651 : 'ГОРКИ ГОРОД, апарт-отель',
                    102610026739 : 'SIGMA SIRIUS, пансионат (бывш. кв. Александровский сад)',
                    102610026611 : 'GAMMA SIRIUS (бывш. кв. Чистые Пруды)',
                    102610084348 : 'ОК СОЧИ ПАРК ОТЕЛЬ'
                },
            };
        },
        props: ['tours'],
    }
</script>
