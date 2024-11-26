<template>
    <button class="pure-button btn btn-secondary btn-small" @click="this.$emit('logout');">{{ logoutbuttontext }}</button>
</template>

<script>
    export default {
    	props: {
        logoutbuttontext: String,
    	},


    }
</script>

<style scoped>
</style>
