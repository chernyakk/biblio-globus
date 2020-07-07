<template>
    <div id="hotel-table">
        <div class="loading" v-if="loading">
            Loading...
        </div>

        <div v-if="error" class="error">
            <p>{{ error }}</p>

            <p>
                <button @click.prevent="fetchData">
                    Try Again
                </button>
            </p>
        </div>


        <div class="parsed" id="table">
            <div class="parsed__percent">
                <label for="percent" class="parsed__label">% наценки</label>
                <input v-model="percent" type="number" id="percent" class="parsed__input input-number">
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
                            <strong>Отель:</strong> {{ id_hotel }},<br>
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
    import axios from 'axios';
    export default {
        data() {
            return {
                loading: false,
                tours: null,
                error: null,
                percent: 10,
            };
        },
        created() {
            this.fetchData();
        },
        methods: {
            fetchData() {
                this.error = this.users = null;
                this.loading = true;
                axios
                    .post('/api/request', {
                        date1: '13.07.2020',
                        date2: '17.07.2020',
                        adults: '2',
                        kids: '2'
                    })
                    .then(response => {
                        this.loading = false;
                        this.tours = response.data;
                        console.log(response);
                    }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                });
            }
        },
    }
</script>
