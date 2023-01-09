<template>
<div class="action-bubble ">
	<div class="timestamp" style="display: inline-block; width: 40%">{{ this.moment(action.start).format('YYYY-MM-DD HH:mm:ss') }} - {{ actionEnd() }}</div>
    <div class="name" style="display: inline-block; width: 60%">{{ nameDescription() }}</div>
</div>
</template>

<script>
export default {
	props: {
        action: Object
	},

    methods: {
	    actionEnd() {
            var delayStart = new Date(this.action.start);

            var executionStart = new Date(delayStart);
            executionStart.setSeconds(delayStart.getSeconds() + this.action.delay);

            var extensionStart = new Date(executionStart);
            extensionStart.setSeconds(executionStart.getSeconds() + this.action.length);

            var extensionEnd = new Date(extensionStart);
            extensionEnd.setSeconds(extensionStart.getSeconds() + this.action.extension);

            return this.moment(extensionEnd).format('HH:mm:ss');
        },

        nameDescription() {
	        var name = this.action.name;
	        name += (this.action.description) ? ' - ' + this.action.description: '';
	        return name;
        }

    }

};
</script>0
