<template>
	<div>
		<span v-html="l('theme.name')"></span>: &nbsp;{{ user.name }}<br />
    <span v-html="l('theme.email')"></span>Email: &nbsp;&nbsp;{{ user.email }}<br /><br />
	</div>
</template>

<script>
    export default {
    	props: {
    		user: Object
    	},

    	data: () => ({

    	}),

    	methods: {

    	}
    }
</script>

<style scoped>
</style>