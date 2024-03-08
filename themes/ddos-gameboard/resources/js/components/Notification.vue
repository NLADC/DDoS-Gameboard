<!--
  - Copyright (C) 2024 Anti-DDoS Coalitie Netherlands (ADC-NL)
  -
  - This file is part of the DDoS gameboard.
  -
  - DDoS gameboard is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 3 of the License, or
  - (at your option) any later version.
  -
  - DDoS gameboard is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with this program; If not, see @link https://www.gnu.org/licenses/.
  -
  -->

<template>
<div :class="[type, animation]" class="notification" role="alert">
	<div class="flex flex-row">
		<div class="w-8 text-left"><span class="icon material-icons">{{ icon }}</span></div>
		<div class="title flex-grow font-bold">{{ title }}</div>
		<div class="w-8 text-right"><button class="material-icons" @click="close()">X</button></div>
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
