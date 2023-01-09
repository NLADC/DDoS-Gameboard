<template>
<transition name="modal" v-if="show">
<div class="modal-mask">
<div :class="{'shake animated': isError}" class="modal system-modal">
	<h3>Gameboard control</h3>

	<button type="button" class="btn btn-primary mb-6" @click="submitForm('refreshAll')">Refresh ALL</button>
    <button type="submit" class="btn btn-primary mb-6" @click="submitForm('refreshGuest')">Refresh GUEST</button>
	<button type="submit" class="btn btn-primary mb-6" @click="submitForm('refreshBlue')">Refresh BLUE</button>
	<button type="submit" class="btn btn-primary mb-6" @click="submitForm('refreshRed')">Refresh RED</button>
	<button type="submit" class="btn btn-primary mb-6" @click="submitForm('refreshPurple')">Refresh PURPLE</button>

    <div class="w-full mb-3 text-center text-red-500" v-if="isError">{{ errorMsg }}</div>
		
	<div class="flex items-center justify-between">
        <button type="reset" @click="close()" class="w-full py-2 px-4 rounded-r focus:outline-none focus:shadow-outline" v-html="l('theme.close')"></button>
    </div>
	
</div>
</div>
</transition>
</template>

<script>
    export default {
    	props: {
    		show: Boolean,
    		csrf: String,
    		user: Object
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
    			this.$emit('close')
    		},

    		async submitForm(command) {
                this.hideError();

                var tmp = {
                    _token: this.csrf,
                    command: command
                }

                var path = '/api/gameboard/command';
                var method = 'POST';

                const response = await fetch(path, {
                    method: method,
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(tmp)
                 })
                .then(response => response.json())
                .then(data => {
                    if('result' in data && data.result == false && 'message' in data)
                        this.triggerError(data.message != '' ? data.message : 'A fatal error has occured!');
                    else
                        this.close();
                })
                .catch(err => {
                    this.triggerError('A fatal error has occured! ' + err);
                });
            }

    	}
    }
</script>

<style scoped>
</style>