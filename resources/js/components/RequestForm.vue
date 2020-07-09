<template>
    <div id="mainapp">

        <div class="loading" v-if="loading">
            <p>
                Загружается список отелей...
            </p>
            <div>
                <svg width="100" height="100" viewBox="0 0 100 100" fill="none">
                    <path d="M25 50C25 56.6304 27.6339 62.9893 32.3223 67.6777C37.0107 72.3661 43.3696 75 50 75C56.6304 75 62.9893 72.3661 67.6777 67.6777C72.3661 62.9893 75 56.6304 75 50C75 43.3696 72.3661 37.0107 67.6777 32.3223C62.9893 27.6339 56.6304 25 50 25C43.3696 25 37.0107 27.6339 32.3223 32.3223C27.6339 37.0107 25 43.3696 25 50"/>
                </svg>
            </div>
        </div>
        <div class="error" v-if="error">
            <p>{{ error }}</p>
        </div>

        <div v-if="!responded && !loading">
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
                               class="whatparse__input input-number" min="1" required>
                    </div>
                    <div class="whatparse__row--small">
                        <label for="kids" class="whatparse__label">Кол-во детей</label>
                        <input type="number" name="name" v-model="kids" id="kids"
                               class="whatparse__input input-number" min="0">
                    </div>
                </div>
                <button type="submit" class="cta">Запросить цены</button>
            </form>
        </div>

        <div v-if="responded">
            <hotels-prices :tours="tours" />
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    export default {
        data() {
            return {
                responded: false,
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
                this.error = null;
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
                    if (!response.data.length) {
                        this.error = "Не найдено ни одной подходящей записи с такими параметрами";
                    }
                    else this.responded = true;
                })
                .catch(error => {
                    this.loading = false;
                    this.error = "Сервер не отвечает, попробуйте повторить позже или обратитесь к разработчикам!"
                });
            }
        },
    }
</script>
