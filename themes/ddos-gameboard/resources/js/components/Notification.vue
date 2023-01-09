<template>
<div :class="[type, animation]" class="notification" role="alert">
	<div class="flex flex-row">
		<div class="w-8 text-left"><span class="icon material-icons">{{ icon }}</span></div>
		<div class="title flex-grow font-bold">{{ title }}</div>
		<div class="w-8 text-right"><button class="material-icons" @click="close()">clear</button></div>
	</div>
	<div v-if="message" class="message pl-8" v-html="message"></div>
    <lottie-player v-if="title == 'Countdown'" class="" src="/json/countdown.json" background="transparent" speed="0.4" autoplay></lottie-player>
</div>
</template>

<script>
	export default {
		props: {
			type: String,
			title: String,
			message: String
		},

    	data: () => ({
    		icon: '',
    		spawntime: null,
    		animation: 'animated fadeInUp',
    		counter: null,
    		closeAnimation: null,
            show: Boolean
    	}),

		mounted() {
			this.spawntime = this.moment();

			switch(this.type) {
				case 'success': this.icon = 'done'; break;
				case 'warning': this.icon = 'priority_high'; break;
				case 'error': this.icon = 'sentiment_very_dissatisfied'; break;
				case 'info': this.icon = 'info'; break;
			}

			this.counter = setInterval(function() {
				if((this.moment() - this.spawntime) >= 17000)
					this.close();
			}.bind(this), 1000);
		},

		methods: {
			close() {
				clearInterval(this.counter);

				this.closeAnimation = setInterval(function() {
					this.show = false;
					clearInterval(this.closeAnimation);
					// trigger off screen
					this.$emit('notify-off');
				}.bind(this), 1000);

				this.animation = 'animated fadeOutUp';
			}
		}
	}
</script>

<style scoped>

</style>
