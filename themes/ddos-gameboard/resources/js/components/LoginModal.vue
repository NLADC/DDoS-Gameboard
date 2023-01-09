<template>
<transition name="modal" id="login-modal" v-if="show">
<div class="modal-mask">
<div :class="{'shake animated': isError}" class="modal">
	<h3 v-html="l('theme.signin')"></h3>

	<validation-observer v-slot="{ handleSubmit, reset }">
	<form @submit.prevent="handleSubmit(submitForm)" @reset.prevent="reset">

		<div class="mb-3">
			<validation-provider name="Email address" rules="required|email" v-slot="{ errors }">
				<input v-model="email" name="email" type="text" :class="{'border-red-500' : errors[0]}" class="focus:outline-none focus:shadow-outline" placeholder="Email address">

				<span class="input-error">{{ errors[0] }}</span>
			</validation-provider>
		</div>

		<div class="mb-6">
			<validation-provider name="Password" rules="required" v-slot="{ errors }">
				<input v-model="password" name="password" type="password" :class="{'border-red-500' : errors[0]}" class="focus:outline-none focus:shadow-outline" placeholder="******************">

				<span class="input-error">{{ errors[0] }}</span>
			</validation-provider>
		</div>

		<div class="w-full mb-6 text-center text-red-500" v-if="isError">{{ errorMsg }}</div>

		<div id="loginform" class="flex items-center justify-between">
			<button type="submit" class="w-1/2 bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded-l focus:outline-none focus:shadow-outline" v-html="l('theme.signin')"></button>

			<button type="reset" @click="close()" class="w-1/2 py-2 px-4 rounded-r focus:outline-none focus:shadow-outline" v-html="l('theme.close')"></button>
		</div>

	</form>
    <hr class="divider">
    <div class="forgotpassword">
      <a href="/forgot-password" v-html="l('theme.forgotpass')"></a><br>
    </div>
	</validation-observer>
</div>
</div>
</transition>
</template>

<script>
    export default {
    	props: {
    		show: Boolean,
    		csrf: String
    	},

    	data: () => ({
			email: '',
			password: '',

			isError: false,
			errorMsg: ''
    	}),

    	methods: {

    		triggerError(msg) {
    			this.isError = true;
    			this.errorMsg = msg;
    		},

    		hideError() {
    			this.isError = false;
    			this.errorMsg = '';
    		},

    		close() {
    			this.hideError();
    			this.email = '';
    			this.password = '';
    			this.$emit('close')
    		},

    		async submitForm() {
    			this.hideError();
    			let credentials = {_token: this.csrf, email: this.email, password: this.password}

    			const response = await fetch('/api/user/login', {
    				method: 'POST',
                    credentials: 'same-origin',
    				headers: {
    					'Content-Type': 'application/json'
    				},
    				body: JSON.stringify(credentials)
			     })
				.then(response => response.json())
				.then(data => {
					if ('result' in data && data.result == false) {
					    if ('alreadyloggedin' in data && data.alreadyloggedin) {
                            this.triggerError('Already logged in (other browser?) ');
                        } else {
                            this.triggerError('Incorrect credentials or no access!');
                        }
                    } else
						window.location.href="/";
				})
				.catch(err => {
                    this.triggerError('A fatal error has occured. Whoops!');
				});
    		}

    	}
    }
</script>

<style scoped>
</style>
