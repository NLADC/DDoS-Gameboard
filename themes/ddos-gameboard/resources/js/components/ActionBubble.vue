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
