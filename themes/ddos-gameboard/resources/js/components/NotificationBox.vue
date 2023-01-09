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

