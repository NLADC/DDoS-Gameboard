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
<div v-if="show" class="action-wrapper" :class="{'global': global, 'bg-red-500': dHeight || eHeight}" :style="{marginLeft: (action.channel * 16.5) + 'rem', marginTop: offsetY + 'rem', paddingTop: dHeight + 'rem', paddingBottom: eHeight + 'rem'}">

    <div class="action" :style="{height: aHeight + 'rem'}">

        <div class="action-header sticky-top" :class="{'has-flags': hasFlags}">
            <div class="name text-xs lg:text-xl">
                {{ action.name }}
                <div class="flag-wrapper bg-sec rounded-lg float-right flex flex-row items-center justify-end h-full px-4 el-2">
                    <span v-if="action.hasIssues" title="Action has issues" class="flag material-icons" :class="{'has-issues': action.hasIssues}"></span>
                    <span v-if="action.isCancelled" title="Action is cancelled" class="flag material-icons" :class="{'is-cancelled': action.isCancelled}"></span>
                </div>
            </div>
            <div class="description text-xs lg:text-sm">{{ action.description }}</div>
        </div>

        <div class="description">&nbsp;</div>

        <div class="footer text-center" >
            <div class="tag text-xs lg:text-sm">{{ action.tag }} </div>
        </div>
    </div>
</div>
</template>

<script>
    export default {
    	props: {
            action: Object,
            role: String,
            min: String,
            granularity: String,
            csrf: String,
            global: Boolean,
            show: Boolean
    	},

    	data: () => ({
            dHeight: 0,
            aHeight: 0,
            eHeight: 0,
            offsetY: 0,
            showMenu: false,
            showSubmenu: 1,

            proxy: null,

            hasFlags: false,
            header: '',
            message: '',
            startVerbose: ''
    	}),

    	mounted() {
            this.calcAll();
            this.updateProxy();
    	},

        watch: {
            action: {
                deep: true,
                handler() {
                    this.calcAll();
                    this.updateProxy();
                }

            }
        },

        methods: {
            calcAll() {
                var delayStart = new Date(this.action.start);

                var executionStart = new Date(delayStart);
                executionStart.setSeconds(delayStart.getSeconds() + this.action.delay);

                var extensionStart = new Date(executionStart);
                extensionStart.setSeconds(executionStart.getSeconds() + this.action.length);

                var extensionEnd = new Date(extensionStart);
                extensionEnd.setSeconds(extensionStart.getSeconds() + this.action.extension);

                this.offsetY = this.calcHeight(null, delayStart);

                var newDHeight = this.action.delay == 0 ? 0 : this.calcHeight(delayStart, executionStart);
                if(this.dHeight != newDHeight)
                    this.dHeight = newDHeight;

                var newAHeight = this.calcHeight(executionStart, extensionStart);
                if(this.aHeight != newAHeight)
                    this.aHeight = newAHeight;

                var newEHeight = this.action.extension == 0 ? 0 : this.calcHeight(extensionStart, extensionEnd);
                if(this.eHeight != newEHeight)
                    this.eHeight = newEHeight;
            },

            calcHeight(start, end) {
                var offsetY = 0;

                if(start == null) {
                    start = this.moment(this.min);
                    offsetY = 1.5;
                }

                var minutes = Math.floor((Math.abs(start - end) / 1000) / 60);

                return (minutes / this.granularity * 4 ) + offsetY;
            },

            updateProxy() {
                this.hasFlags = this.action.hasIssues || this.action.isCancelled;
                this.proxy = {
                    id: this.action.id,
                    length: this.action.length,
                    delay: this.action.delay,
                    extension: this.action.extension,
                    hasIssues: this.action.hasIssues,
                    isCancelled: this.action.isCancelled
                };

                this.start(0);
            },

            closeMenu() {
                this.showMenu = false;
                this.showSubmenu = 1;
                this.error = '';
                this.updateProxy();
            },

            delay(value) {
                var newValue = this.proxy.delay + value;
                this.proxy.delay = newValue <= 0 ? 0 : newValue;
            },

            extend(value) {
                var newValue = this.proxy.extension + value;
                this.proxy.extension = newValue <= 0 ? 0 : newValue;
            },

            start(value) {
                if(value != 0)
                    this.proxy.start = this.proxy.start.add(value, 's');
                else
                    this.proxy.start = this.moment(this.action.start);

                this.startVerbose = this.proxy.start.format('h:mm');
            },

            length(value) {
                if(value != 0) {
                    var newValue = this.proxy.length + value;
                    this.proxy.length = newValue <= 300 ? 300 : newValue;
                } else
                    this.proxy.length = this.action.length;
            },

            hideMessage() {
                this.message = '';
                this.header = '';
                this.showSubmenu = 1;
            },

            showMessage(header, message) {
                this.header = header;
                this.message = message;
                this.showSubmenu = 4;
            },

            async save() {
                var data = {_token: this.csrf};

                data.id = this.action.id;

                if(this.proxy.start.format('YYYY-MM-DD HH:mm:ss') != this.action.start)
                    data.start = this.proxy.start.format('YYYY-MM-DD HH:mm:ss');
                if(this.proxy.length != this.action.length)
                    data.length = this.proxy.length;
                if(this.proxy.delay != this.action.delay)
                    data.delay = this.proxy.delay;
                if(this.proxy.extension != this.action.extension)
                    data.extension = this.proxy.extension;
                if(this.proxy.hasIssues != this.action.hasIssues)
                    data.has_issues = this.proxy.hasIssues;
                if(this.proxy.isCancelled != this.action.isCancelled)
                    data.is_cancelled = this.proxy.isCancelled;

                const response = await fetch('/api/action/' + this.proxy.id, {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                 })
                .then(response => response.json())
                .then(data => {
                    if('result' in data && data.result == false) {
                        if('message' in data)
                            this.showMessage('error', data.message);
                        else
                            this.showMessage('error', 'Could not update the action.');
                    } else
                        this.closeMenu();
                })
                .catch(err => {
                    this.showMessage('error', 'A fatal error has occured. Reload this page');
                });
            }
        }
    }
</script>
