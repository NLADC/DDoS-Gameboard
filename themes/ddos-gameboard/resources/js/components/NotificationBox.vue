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
	<div class="notification-box">
		<notification v-for="notif in notifications" @notify-off="notifyOff" :key="notif.id" :type="notif.type" :title="notif.title" :message="notif.message"></notification>
	</div>
</template>

<script>
	export default {
        props: {
            notifications: Array,
            showNotification: Boolean
        },

        methods: {
            notifyOff: function () {
                // transport to parent
                this.$emit('notify-off');
            }
        }
    }
</script>

