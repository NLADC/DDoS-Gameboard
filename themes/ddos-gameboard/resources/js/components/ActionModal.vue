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
<transition name="modal" id="login-modal" v-if="show">
<div class="modal-mask">
<div :class="{'shake animated': isError}" class="modal">
	<h3 v-html="l('theme.editingaction')"></h3>

	<validation-observer v-slot="{ handleSubmit, reset }">
	<form @submit.prevent="handleSubmit(submitForm)" @reset.prevent="reset">

		<div class="mb-3">
			<validation-provider name="Name" rules="required" v-slot="{ errors }">
				<input v-model="proxy.name" name="name" type="text" :class="{'border-red-500' : errors[0]}" class=" focus:outline-none focus:shadow-outline" placeholder="Name">

				<span class="input-error">{{ errors[0] }}</span>
			</validation-provider>
		</div>

		<div class="mb-3">
			<validation-provider name="tag" rules="required" v-slot="{ errors }">
				<input v-model="proxy.tag" name="tag" type="text" :class="{'border-red-500' : errors[0]}" class=" focus:outline-none focus:shadow-outline" placeholder="Tag">

				<span class="input-error">{{ errors[0] }}</span>
			</validation-provider>
		</div>

		<div class="mb-6">
			<validation-provider name="description" rules="" v-slot="{ errors }">
				<input v-model="proxy.description" name="description" type="text" :class="{'border-red-500' : errors[0]}" class="focus:outline-none focus:shadow-outline" placeholder="Description">

				<span class="input-error">{{ errors[0] }}</span>
			</validation-provider>
		</div>

		<div class="w-full mb-6 text-center text-red-500" v-if="isError">{{ errorMsg }}</div>

		<div class="flex items-center justify-between">
			<button type="submit" class="w-1/2 bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded-l focus:outline-none focus:shadow-outline" v-html="l('theme.edit')"></button>

			<button type="reset" @click="close()" class="w-1/2 py-2 px-4 rounded-r focus:outline-none focus:shadow-outline" v-html="l('theme.close')"></button>
		</div>
	</form>
	</validation-observer>
</div>
</div>
</transition>
</template>

<script>
    export default {
    	props: {
    		show: Boolean,
    		csrf: String,
    		action: Object
    	},

    	data: () => ({
			isError: false,
			errorMsg: '',
			proxy: null
    	}),

    	watch: {
    		action: function() {
    			this.setFields();
    		}
    	},

    	methods: {

    		triggerError(msg) {
    			this.isError = true;
    			this.errorMsg = msg;
    		},

    		hideError() {
    			this.isError = false;
    			this.errorMsg = '';
    		},

    		setFields() {
    			this.proxy = {
    				id: this.action.id,
    				name: this.action.name,
    				tag: this.action.tag,
    				description: this.action.description
    			};
    		},

    		resetFields() {
    			this.proxy = null;
    		},

    		close() {
    			this.hideError();
    			this.$emit('close')
    		},

    		async submitForm() {
    			this.hideError();
    			this.proxy._token = this.csrf;

    			const response = await fetch('/api/action/' + this.proxy.id, {
    				method: 'PUT',
                    credentials: 'same-origin',
    				headers: {
    					'Content-Type': 'application/json'
    				},
    				body: JSON.stringify(this.proxy)
			     })
				.then(response => response.json())
				.then(data => {
                    if('result' in data && data.result == false) {
                        if('message' in data)
                            this.showMessage('error', data.message);
                        else
                            this.showMessage('error', 'Could not update the action.');
                    } else
                        this.close();
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
