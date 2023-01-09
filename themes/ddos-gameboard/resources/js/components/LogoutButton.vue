<template>
    <button class="pure-button btn btn-secondary btn-small" v-on:click="logout">{{ logoutbuttontext }}</button>
</template>

<script>
    export default {
    	props: {
        logoutbuttontext: String,
    	},

    	data: () => ({
    	}),

    	methods: {

    		async logout() {
    			const response = await fetch('/api/user/logout', {
    				method: 'GET'
    			})
				.then(response => response.json())
				.then(data => {
					if("result" in data && data.result == true) {
                        window.location.href="/";
					} else {
						console.log('Could not log out!');
					}
				})
				.catch(err => {
					console.log('A fatal error has occured. Reload this page.');
				});
    		}

    	}
    }
</script>

<style scoped>
</style>