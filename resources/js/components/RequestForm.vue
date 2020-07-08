<template>
    <div id="mainapp">

        <div class="loading" v-if="loading">
            Загружается список отелей.....
        </div>

        <div v-if="error" class="error">
            <p>{{ error }}</p>

            <p>
                <button @click.prevent="fetchData">
                    Try Again
                </button>
            </p>
        </div>

        <div v-if="!responsed && !loading">
            <form action="" class="whatparse" @submit="fetchData">
                <div class="whatparse__row">
                    <label for="checkin" class="whatparse__label">Заезд</label>
                    <input type="date" name="date1" v-model="date1" id="checkin"
                           class="whatparse__input input-date" required>
                </div>
                <div class="whatparse__row">
                    <label for="checkout" class="whatparse__label">Выезд</label>
                    <input type="date" name="date2" v-model="date2" id="checkout"
                           class="whatparse__input input-date" required>
                </div>
                <div class="whatparse__row--line">
                    <div class="whatparse__row--small">
                        <label for="grownups" class="whatparse__label">Кол-во взрослых</label>
                        <input type="number" name="adults" v-model="adults" id="grownups"
                               class="whatparse__input input-number" required>
                    </div>
                    <div class="whatparse__row--small">
                        <label for="kids" class="whatparse__label">Кол-во детей</label>
                        <input type="number" name="name" v-model="kids" id="kids"
                               class="whatparse__input input-number">
                    </div>
                </div>
                <button type="submit" class="cta">Запросить цены</button>
            </form>
        </div>

        <div v-if="responsed">
            <hotels-prices :tours="tours" />
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    export default {
        data() {
            return {
                responsed: false,
                loading: false,
                tours: null,
                error: null,
                percent: 10,
                date1 : '2020-07-17',
                date2 : '2020-07-27',
                adults: '2',
                kids: '2',
            };
        },
        methods: {
            fetchData() {
                this.loading = true;
                axios
                    .post('/api/request', {
                        date1: this.date1,
                        date2: this.date2,
                        adults: this.adults,
                        kids: this.kids,
                    })
                    .then(response => {
                        this.loading = false;
                        this.tours = response.data;
                        this.responsed = true;
                        console.log(this.tours);
                    }).catch(error => {
                    this.loading = false;
                    this.error = "Что-то сломалось, обратитесь к разработчикам!"
                });
            }
        },
    }
</script>
