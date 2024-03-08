/*
 * Copyright (C) 2024 Anti-DDoS Coalitie Netherlands (ADC-NL)
 *
 * This file is part of the DDoS gameboard.
 *
 * DDoS gameboard is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * DDoS gameboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; If not, see @link https://www.gnu.org/licenses/.
 *
 */

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./lang');

import Vue from 'vue';
import {ValidationObserver, ValidationProvider, extend} from 'vee-validate';
import * as rules from 'vee-validate/dist/rules';

window.Event = new Vue();

require('material-icons');
require('@lottiefiles/lottie-player');
Vue.prototype.moment = require('moment');
Vue.prototype.l = window.l;
Vue.prototype.graphmaxresponsetime = window.targetsdashboard_graphmaxresponsetime;

// Initialise VeeValidate, install rules and scheduler
Object.keys(rules).forEach(rule => {
    extend(rule, rules[rule]);
});

Vue.component('validation-provider', ValidationProvider);
Vue.component('validation-observer', ValidationObserver);

/**
 * The following block of code may be used to automatically register your
 * Vue scheduler. It will recursively scan this directory for the Vue
 * scheduler and automatically register them with their "basename".
 *
 * Eg. ./scheduler/ExampleComponent.vue -> <example-component></example-component>
 */

Vue.component('measurements', require('./components/Measurements.vue').default);
Vue.component('measurements-graph', require('./components/MeasurementsGraph.vue').default);
Vue.component('measurements-data', require('./components/MeasurementsData.vue').default);
Vue.component('groups-select', require('./components/GroupsSelect.vue').default);
Vue.component('group-select', require('./components/GroupSelect.vue').default);


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding scheduler to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const targetsdashboard = new Vue({
    el: '#targetsdashboard',
    emits: ['scaled'],

    data() {
        return {
            showMeasurements: false,
            measurements: JSON.parse(window.targetsdashboard_measurements),
            latesttimestamp: window.targetsdashboard_latesttimestamp,
            notifications: [],
            firsttime: window.targetsdashboard_firsttime,
            endtime: window.targetsdashboard_endtime,
            access: window.targetsdashboard_access,

            csrfToken: window.targetsdashboard_csrfToken,
            groups: JSON.parse(window.targetsdashboard_groups),
            showGroupsSelect: false,

            reRender: {
                measurements: false,
            },

            titleColor: 'text-blue-500',
        }

    },

    mounted() {
        this.showMeasurements = true;

        if (this.access) {
            // Preprocess the parties and actions
            Object.entries(this.groups).forEach(function (group, idx) {
                group = group[1];
                console.debug('group name=' + group.name);
            }.bind(this));

            this.logConsole("Set feeds reading interval on 10 secs");
            setInterval(this.readFeeds.bind(this), 10000);
        }
    },

    methods: {
        reRendered() {
            this.reRender.measurements = false;
        },

        rerenderAll() {
            this.reRender.measurements = true;
        },

        setupStream() {
            if (typeof (EventSource) === "undefined") {
                this.logConsole("Server-Sent Events are not supported.");
                return;
            } else
                this.logConsole("Initializing update stream.");

            try {
                // force close of running thread if loaded
                if (this.es != null) {
                    this.logConsole("Stream; reset last EventSource call");
                    this.es.close();
                }

                this.es = new EventSource("/api/targets/");

                this.es.addEventListener("message", event => {
                    this.logConsole("Stream; received message");
                    try {
                        var data = JSON.parse(event.data);
                        if ('login' in data && data.login == false) {
                            this.logConsole("Stream; no access (anymore)");
                            window.location.href = "/";
                        }
                    } catch (e) {
                        console.error('addEventListener error: ' + e);
                    }
                }, false);

                this.es.addEventListener("error", event => {
                    this.logConsole("Stream was closed - reconnect after a couple of seconds..");
                }, false);

            } catch (e) {
                console.error('setupStream error: ' + e);
            }
        },
        async readFeeds() {
            var tmp = {
                mode: 'readfeeds',
            }
            var path = '/api/targets/';
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
                    if ('result' in data && data.result == false && 'message' in data)
                        this.triggerError(data.message);
                    else {
                        if ('result' in data && data.result == true && 'measurements' in data) {
                            this.measurements = data.measurements;
                            this.latesttimestamp = data.latesttimestamp;
                            this.rerenderAll();
                        }
                    }
                })
                .catch(err => {
                    console.debug('A error has occured:  ' + err);
                });
        },
        // formatted logging
        logConsole(text) {
            var now = new Date();
            var h = (now.getHours() < 10) ? '0' + now.getHours() : now.getHours();
            var m = (now.getMinutes() < 10) ? '0' + now.getMinutes() : now.getMinutes();
            var s = (now.getSeconds() < 10) ? '0' + now.getSeconds() : now.getSeconds();
            console.log(h + ':' + m + ':' + s + '> ' + text);
        },

        triggerError(msg) {
            this.isError = true;
            this.errorMsg = msg;
            alert(this.errorMsg)
            setTimeout(this.scrollBottom, 1000);
        },

        hideError() {
            this.isError = false;
            this.errorMsg = '';
        },

        async Zoom(amount){
            let fontSize =  Number(window.getComputedStyle(document.body).getPropertyValue('font-size').match(/\d+/)[0]);
            if (fontSize < 200 ) {
                fontSize = fontSize + amount;
                if (fontSize >= 4) {
                    document.documentElement.style.fontSize = fontSize + "px";
                }
                else {
                    document.documentElement.style.fontSize = "4px";
                }
                Event.$emit('scaled');

            }
            else {
                document.documentElement.style.fontSize = "16px";
                Event.$emit('scaled');
            }

        },
    }
});

window.setZoom = function () {
    document.body.style.zoom = '30%';
}

window.resetZoom = function () {
    document.body.style.zoom = '100%';
}
