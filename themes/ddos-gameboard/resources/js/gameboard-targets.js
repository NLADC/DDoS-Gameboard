import {createApp} from 'vue'
import './security';

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./lang');
require('material-icons');
// require('@lottiefiles/lottie-player');

import veeValidatePlugin from './vee-validate-plugin';
import eventBus from './eventBus'
import mitt from 'mitt';


const targetsdashboard = {
    el: '#targetsdashboard',
    emits: ['scaled'],

    created(){
        this.emitter = mitt();
    },

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
                this.emitter.emit('scaled');

            }
            else {
                document.documentElement.style.fontSize = "16px";
                this.emitter.emit('scaled');
            }

        },
    }
};

const app = createApp(targetsdashboard);

app.config.globalProperties.moment = require('moment');
app.config.globalProperties.l = window.l;
app.config.globalProperties.graphmaxresponsetime = window.targetsdashboard_graphmaxresponsetime;

/**
 * The following block of code may be used to automatically register your
 * Vue scheduler. It will recursively scan this directory for the Vue
 * scheduler and automatically register them with their "basename".
 *
 * Eg. ./scheduler/ExampleComponent.vue -> <example-component></example-component>
 */

app.component('measurements', require('./components/Measurements.vue').default);
app.component('measurements-graph', require('./components/MeasurementsGraph.vue').default);
app.component('measurements-data', require('./components/MeasurementsData.vue').default);
app.component('groups-select', require('./components/GroupsSelect.vue').default);
app.component('group-select', require('./components/GroupSelect.vue').default);
app.use(veeValidatePlugin);

app.mount('#targetsdashboard');



window.setZoom = function () {
    document.body.style.zoom = '30%';
}

window.resetZoom = function () {
    document.body.style.zoom = '100%';
}
