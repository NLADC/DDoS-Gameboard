<template>
    <div class="game-countdown" :class="'mode-' + mode" id="game-countdown">
        <div class="wrapper text-xl lg:text-5xl sm:text-2xl xm:text-1xl">
            <lottie-player v-if="mode == 2" class="player-1 -top-5 lg:-top-1 -left-10 lg:-left-20" src="/json/alarm.json" background="transparent" speed="0.4" loop autoplay></lottie-player>

            <span v-if="mode == 1">{{ days < 10 ? '0' + days : days }}</span>
            <span v-if="mode == 1">:</span>
            <span>{{ hours < 10 ? '0' + hours : hours }}</span>
            <span>:</span>
            <span>{{ minutes < 10 ? '0' + minutes : minutes }}</span>
            <span>:</span>
            <span>{{ seconds < 10 ? '0' + seconds : seconds }}</span>

            <lottie-player v-if="mode == 2" class="player-2 -top-5 lg:-top-1 -right-10 lg:-right-20" src="/json/alarm.json" background="transparent" speed="0.4" loop autoplay></lottie-player>
        </div>
    </div>
</template>

<script>
    export default {
    	props: {
            starttime: String,
            endtime: String
    	},

    	data: () => ({
			startDate: 0,
            endTime: 0,
            distance: 0,
            ref: null,
            mode: 1,

            days: 0,
            hours: 0,
            minutes: 0,
            seconds: 0
    	}),

    	mounted() {
    		this.startDate = new Date(this.starttime).getTime();
            this.endDate = new Date(this.endtime).getTime();
            var now = new Date().getTime();
            var distance = this.startDate - now;
            if (distance < 0) {
                distance = now - this.endDate;
            }
            distance < 0 ? this.initTimer() : this.initCountDown();
    	},

    	methods: {
            initCountDown() {
                this.startCountdown();
                this.ref = setInterval(this.startCountdown.bind(this), 1000);
            },

            startCountdown() {
                var now = new Date().getTime();
                var distance = this.startDate - now;
                if (distance < 0) {
                    distance = now - this.endDate;
                }
                if (distance < 0) {
                    this.initTimer();
                } else {
                    this.days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    this.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    this.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    this.seconds = Math.floor((distance % (1000 * 60)) / 1000);
                }
            },

            initTimer() {
                //console.debug('initTimer');
                this.mode = 2;
                this.startTimer();
                clearInterval(this.ref);
                this.ref = setInterval(this.startTimer.bind(this), 1000);
              try {
                this.delayedUpdateAllResponsiveFunctions();
              } catch (err) {
                console.log("function delayedUpdateAllResponsiveFunctions in resposive.js not found: " + err.message);
              }

            },

            startTimer() {
                //console.debug('startTimer');
                var now = new Date();
                this.hours = now.getHours();
                this.minutes = now.getMinutes();
                this.seconds = now.getSeconds();
            },
    	}
    }
</script>

<style scoped>

</style>
