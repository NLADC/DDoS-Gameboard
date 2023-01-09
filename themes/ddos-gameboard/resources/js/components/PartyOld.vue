<template>
	<div class="party w-32 lg:w-64" :class="{'global': global, 'hidden': !show}">
        <h2 class="h-16 w-32 top-20 lg:w-64 leading-4 lg:leading-16 pt-4 text-xs lg:text-2xl " v-if="!global">
            {{ party.name }}
        </h2>
        <div v-if="show">
		  <slot></slot>
        </div>
	</div>
</template>

<script>
    export default {
    	props: {
            party: Object,
            global: Boolean,
            show: Boolean
    	}
    }
</script>

<style scoped>

</style>
