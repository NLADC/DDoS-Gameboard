<template>
	<div class="timeline" >
        <div v-for="block in timeblocks" :keys="block.idx" :id="block.ref" :class="{'has-logs': block.logs.length, 'hournode': setBig(block)}">
            <span :class="{'time-big': setBig(block), 'time-small' : !setBig(block), 'animated jackInTheBox': block.logs.length}" @click="openModal(block)">
                {{ setBig(block) ? block.hrs : block.mins }}
            </span>
        </div>
    </div>
</template>

<script>

    export default {
    	props: {
            min: String,
            max: String,
            granularity: String,
            logs: Object,
            rerender: Boolean,
            loggedin: Boolean,
    	},

        // ,'z-50 sticky top-20 ': block.mins == '00'

    	data: () => ({
            timeblocks: []
    	}),

        watch: {
            rerender: {
                handler() {
                    console.debug('Watch timeline rerender called');
                    for(var i = 0; i < this.timeblocks.length; i++) {
                        var block = this.timeblocks[i];
                        block.logs = this.getLogs(block);
                    }
                    this.$emit('rerendered');
                }
            }
        },

    	mounted() {
            var minTime = this.moment(this.min);
            var maxTime = this.moment(this.max);
            var granularity = parseInt(this.granularity);
            var diff = this.moment.duration(maxTime.diff(minTime)).asMinutes();
            this.timeblocks = [];
            for(var m = this.moment(this.min), i = 0; m.isBefore(maxTime); m.add(granularity, 'minutes'), i++) {
                this.timeblocks.push({
                    idx:   i,
                    ref: 'timeline-block-' + i,
                    timestamp: m.format('YYYY-MM-DD HH:mm:ss'),
                    mins:  m.format('mm'),
                    hrs:   m.format('HH'),
                    logs:  this.getLogs({timestamp: m.format('YYYY-MM-DD HH:mm:ss')})
                });
            }
    	},
        methods: {

            setBig(block) {
                return (block.mins == '00' || block.idx==0);
            },

            doForceUpdate() {
                this.$forceUpdate();
            },

            getLogs(block) {
                var granularity = parseInt(this.granularity);
                //var start = this.moment(block.timestamp).subtract(granularity / 2, 'minutes');
                //var end = this.moment(block.timestamp).add(granularity / 2, 'minutes');
                var start = this.moment(block.timestamp);
                var end = this.moment(block.timestamp).add(granularity, 'minutes');

                var logs = [];
                Object.entries(this.logs).forEach(function(log, idx) {
                    log = log[1];
                    log.timestamp = this.moment(log.timestamp);
                    if(log.timestamp >= start && log.timestamp < end)
                        logs.push(log);
                }.bind(this));

                return logs;
            },

            getLogSettings(timeblock) {
                return {
                    title: timeblock.hrs + ':' + timeblock.mins,
                    timestamp: timeblock.timestamp,
                    hours: timeblock.hrs,
                    mins: timeblock.minutes,
                    granularity: parseInt(this.granularity)
                }
            },

            openModal(block) {
                if(this.loggedin)
                    this.$emit('edit', this.getLogSettings(block));
                else
                    this.$emit('login');
            }
        }
    }
</script>

<style scoped>

</style>
