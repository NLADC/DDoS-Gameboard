import {createApp} from 'vue';

import UserDisplay from './components/UserDisplay.vue';
import LoginModal from './components/LoginModal.vue';
import LogoutButton from './components/LogoutButton.vue';
import GameCountdown from './components/GameCountdown.vue';
import Timeline from './components/Timeline.vue';
import Party from './components/Party.vue';
import Action from './components/Action.vue';
import NotificationBox from './components/NotificationBox.vue';
import Notification from './components/Notification.vue';
import LogModal from './components/LogModal.vue';
import LogBubble from './components/LogBubble.vue';
import SystemModal from './components/SystemModal.vue';
import TimelineScroller from './components/TimelineScroller.vue';
import LiveLog from './components/LiveLog.vue';
import PartiesSelect from './components/PartiesSelect.vue';
import PartySelect from './components/PartySelect.vue';
import ActionBubble from './components/ActionBubble.vue';
import ActionsList from './components/ActionsList.vue';
import QuickLogging from './components/QuickLogging.vue';
import QuickBubble from './components/QuickBubble.vue';
import HelpFile from './components/HelpFile.vue';
import AttachmentModal from './components/AttachmentModal.vue';

import './security.js';

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('./responsive');
require('./lang');
require('material-icons');

import {TransactionNotificationController} from './transactions.js';
import veeValidatePlugin from './vee-validate-plugin';
import eventBus from './eventBus'
import mitt from 'mitt';

const emitter = mitt();
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding scheduler to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const gameboard = {
    el: '#gameboard',
    emits: ['updateLog', 'notify'],

    data() {
        return {
            es: null,

            showLoginModal: false,
            showLogModal: false,
            showLiveLogModal: false,
            showPartiesSelect: false,
            showActionsList: false,
            showQuickLogging: false,
            showHelpFile: false,
            showAttachmentModal: false,
            showNotification: false,

            scroll: window.gameboard_scroll,
            showscroll: false,
            firsttime: window.gameboard_firsttime,
            endtime: window.gameboard_endtime,
            access: window.gameboard_access,

            targetdasboardurl: window.gameboard_targetdasboardurl ? window.gameboard_targetdasboardurl : false,
            allowedInactivityInSeconds: window.gameboard_allowedInactivityInSeconds,
            allowedNonVisibleInSeconds: window.gameboard_allowedNonVisibleInSeconds,

            editingAction: null,
            editingLogs: {},

            reRender: {
                logs: false,
                quicklogs: false,
                livelogs: false,
                attacks: false,
            },

            attachmentmodal: {
                filename: '',
                base64string: '',
                message: '',
                isimage: false,
                istext: false,
                ispdf: false,
                isvideo: false,
                isaudio: false,
                created_at: '',
                src: '#',
            },

            acceptedImageExtensions: ["png", "jpg", "jpeg", "gif", "svg", "webp", "apng"],
            acceptedTextExtensions: ["txt", "rtf", "json"],
            acceptedVideoExtensions: ["mp4", "webM", "Ogg"],
            acceptedAudioExtensions: ["mp3", "wav", "Ogg"],

            lastTransaction: window.gameboard_lastTransaction,
            parties: JSON.parse(window.gameboard_parties),
            user: JSON.parse(window.gameboard_user),
            logs: JSON.parse(window.gameboard_logs),
            attacks: JSON.parse(window.gameboard_attacks),
            csrfToken: window.gameboard_csrfToken,
            acceptedFileTypes: window.gameboard_acceptedfiletypes,
            notifications: [],

            titleColor: 'text-blue-500',
        }

    },

    mounted() {
        this.setupAutoLogout();

        this.emitter.on('initAttachmentlog', (data) => {
                if ('filename' in data && 'exportablebase64' in data) {
                    this.attachmentmodal = {
                        'filename': data.filename,
                        'created_at': data.created_at,
                        'base64string': data.exportablebase64,
                        'message': '',
                    }

                    const extensionMap = [
                        {extension: 'pdf', isType: 'ispdf'},
                        {extension: 'jpg', isType: 'isimage'},
                        {extension: 'jpeg', isType: 'isimage'},
                        {extension: 'png', isType: 'isimage'},
                        {extension: 'gif', isType: 'isimage'},
                        {extension: 'txt', isType: 'istext'},
                        {extension: 'mp4', isType: 'isvideo'},
                        {extension: 'mp3', isType: 'isaudio'}
                    ];

                    // Match the "extension" from the data.extension with one that is in the map
                    const match = extensionMap.find(({extension: ext}) => ext === data.extension);
                    if (match) {
                        this.attachmentmodal[match.isType] = true;
                    } else {
                        this.attachmentmodal.message = l('theme.cantpreviewfile');
                    }
                    this.showAttachmentModal = true;
                }
            }
        );
        this.emitter.on('emptyAttachmentsmodal', () => {
                for (var key in this.attachmentmodal) {
                    this.attachmentmodal[key] = null;
                }
            }
        );
        if (this.access) {
            // Preprocess the parties and actions
            Object.entries(this.parties).forEach(function (party, idx) {
                party = party[1];
                Object.entries(party.actions).forEach(function (action, idx) {
                    action = action[1];
                    action = this.processAction(action, true, true);
                }.bind(this));
            }.bind(this));

            Object.entries(this.logs).forEach(function (log, idx) {
                log = log[1];
                log.timestamp = this.moment(log.timestamp);
            }.bind(this));

            // Setup all attacks
            Object.entries(this.attacks).forEach(function (attacks, idx) {
                attacks = attacks[1];
                attacks.timestamp = this.moment(attacks.timestamp);
            }.bind(this));

            // color title -> indication of role of current user
            var color = '';
            switch (this.user.role) {
                case 'purple':
                    color = 'purplecolor';
                    break;
                case 'red':
                    color = 'redcolor';
                    break;
                default:
                    color = 'bluecolor';
                    break;
            }
            this.titleColor = color;

            // Setup other stuff
            this.setupStream();
            this.setupOffsets();

            // checker if scroll can be shown
            this.checkScroll();
            setInterval(this.checkScroll.bind(this), 3000);
        }
    },

    methods: {
        globalIsRequired(value) {
            if (value && value.trim()) {
                return true;
            }
            return 'This is required';
        },

        checkScroll() {
            var firsttime = new Date(this.firsttime).getTime();
            var now = new Date().getTime();
            var distance = firsttime - now;
            if (distance < 0) {
                var endtime = new Date(this.endtime).getTime();
                distance = now - endtime;
            }
            // show if first time on screen
            this.showscroll = (distance < 0) ? true : false;
            if (!this.showscroll && this.scroll) {
                this.scroll = false;
            }
        },

        sortedParties(parties) {
            // sort on sortkey
            return _.orderBy(parties, 'sortkey');
        },

        rerenderAll() {
            this.reRender.logs = true;
            this.reRender.quicklogs = true;
            this.reRender.livelogs = true;
            this.reRender.attacks = true;
        },

        processTransaction(transaction) {
            var notification = null;
            var type = transaction.type;
            var transaction = transaction.data;
            var context = {
                user: this.user
            };

            if (type == 'action' || type == 'actionsilent') {

                // Note: actionsilent purpose is for multiply changes of actions in one time; do not update screen

                // get exisiting action
                var action = this.parties[transaction.partyId]['actions'][transaction.id];

                this.logConsole('Update existing action; transaction.partyId=' + transaction.partyId + ', transaction.id=' + transaction.id);

                if ('start' in transaction)
                    transaction.start = this.moment(transaction.start);
                if ('description' in transaction && (this.user.role == 'blue' || this.user.role == 'observer')) {
                    action.name = transaction.description;
                    transaction.description = '';
                }

                if (type == 'action') {
                    // Start the TransactionNotificationController
                    context.action = action;
                    context.name = (this.user.role == 'blue' || this.user.role == 'observer') ? action.tag : action.name;
                    notification = TransactionNotificationController.process('action', transaction, context);
                }

                // Actually update the action
                Object.keys(transaction).forEach(function (key, idx) {
                    if (key != 'id' && key != 'partyId')
                        action[key] = transaction[key];
                });
                action = this.processAction(action, ('description' in Object.keys(transaction)), true);

            } else if (type == 'log') {

                if (transaction.id in this.logs) {

                    this.logConsole('Update existing log');

                    // Update existing
                    var log = this.logs[transaction.id];
                    Object.keys(transaction).forEach(function (key, idx) {
                        if (key != 'id' && key != 'user_id')
                            log[key] = transaction[key];
                    });

                } else {

                    this.logConsole('Create new log');

                    // Create new
                    var log = transaction;
                    log.timestamp = this.moment(transaction.timestamp);

                    this.logs[log.id] = log;
                }

                this.rerenderAll();

            } else if (type == 'attack') {

                if (transaction.id in this.attacks) {

                    this.logConsole('Update existing attacks');

                    // Update existing
                    var attack = this.attacks[transaction.id];
                    Object.keys(transaction).forEach(function (key, idx) {
                        if (key != 'id' && key != 'user_id')
                            attack[key] = transaction[key];
                    });

                } else {
                    this.logConsole('Create new attack');

                    // Create new
                    var attack = transaction;
                    log.timestamp = this.moment(transaction.timestamp);

                    this.attacks[attack.id] = attack;
                }

                this.rerenderAll();

            } else if (type == 'system') {
                notification = TransactionNotificationController.process('system', transaction, context);
            }

            if (notification) {
                this.notifications.push(notification);
                this.showNotification = true;
            }
        },

        processAction(action, processRole, calcOffsets) {
            action.start = this.moment(action.start);
            action.weight = action.delay + action.length + action.extension;
            action.end = this.moment(action.start).add(action.delay + action.length + action.extension, 'seconds');
            action.channel = 0;

            if (processRole && (this.user.role == 'blue' || this.user.role == 'observer')) {
                action.name = action.description;
                action.description = '';
            }

            if (calcOffsets)
                this.setupOffsets();

            return action;
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

                this.es = new EventSource("/api/feed/" + this.lastTransaction);

                this.es.addEventListener("message", event => {
                    this.logConsole("Stream; received message");
                    try {
                        var data = JSON.parse(event.data);
                        if ('login' in data && data.login == false) {
                            this.logConsole("Stream; no access (anymore)");
                            window.location.href = "/";
                        } else {
                            this.processTransaction(data);
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

        setupOffsets() {
            // For every action, calculate overlap
            Object.entries(this.parties).forEach(function (party, idx) {
                party = party[1];

                var actions = Object.entries(party.actions).slice().sort((a, b) => b.start < a.start);
                for (var i = 0; i < actions.length; i++)
                    actions[i][1].channel = 0;

                var foundOverlap = true;
                var exitLoop;
                while (foundOverlap) {
                    foundOverlap = false;

                    actions.forEach(function (a1, idx) {
                        a1 = a1[1];

                        if (exitLoop) {
                            exitLoop = false;
                            return;
                        }
                        exitLoop = false;

                        actions.forEach(function (a2, idx) {
                            a2 = a2[1];

                            if (a1.id == a2.id)
                                return;

                            // Check if same channel
                            if (a1.channel != a2.channel)
                                return;

                            // Check if overlap
                            if (!((a1.start < a2.end && a1.end > a2.start) || (a1.start == a2.start && a1.end == a2.end)))
                                return;

                            // Push lesser weight to the right
                            if (a1.weight < a2.weight)
                                a1.channel += 1;
                            else
                                a2.channel += 1;

                            // Status vars
                            foundOverlap = true;
                            exitLoop = true;
                        });
                    });
                }

            });
        },

        setupAutoLogout() {
            let idleTimer;
            const IdleTimeOut = this.allowedInactivityInSeconds ?? 3600; // 3600; // Allow a full hour of inactivity

            let visibleTimer;
            const visibleTimeOut = this.allowedNonVisibleInSeconds ?? 3600; // This is the allowed seconds for switching a tab or window away from the gameboard

            // Function to set a timer
            const setTimer = (func, seconds) => {
                const ms = seconds * 1000;
                return setTimeout(func, ms); // Return the timer reference
            };

            // Function to reset the timer
            const resetTimer = (timer) => {
                clearTimeout(timer);
            };

            // Function to set the idle timer
            const setIdleTimer = () => {
                resetTimer(idleTimer); // Reset the current idleTimer
                idleTimer = setTimer(handleIdle, IdleTimeOut); // Assign the new idleTimer
            };

            // Function that handles the user being idle
            const handleIdle = () => {
                console.info("DDOS-GAMEBOARD: No Activity, logging out...");
                this.logout(); // Call the logout method
            };

            // Set up event listeners for user activity
            document.addEventListener("DOMContentLoaded", () => {
                setIdleTimer(); // Set the idle timer when the page loads
            });

            document.addEventListener("mousemove", setIdleTimer);
            document.addEventListener("mousedown", setIdleTimer);
            document.addEventListener("keypress", setIdleTimer);
            document.addEventListener("touchstart", setIdleTimer);

            // Handle visibility change (user switching tabs)
            document.addEventListener("visibilitychange", () => {
                if (document.hidden) {
                    // Start the visible timer when the tab is hidden
                    visibleTimer = setTimer(handleIdle, visibleTimeOut);
                } else {
                    console.debug("Not logging out due user switching back to Gameboard in time");
                    resetTimer(visibleTimer); // Clear the visible timer when the user returns
                }
            });
        },


        canLog() {
            var dolog = false;
            if (this.user.role != 'observer') {
                var firsttime = new Date(this.firsttime).getTime();
                var now = new Date().getTime();
                var distance = firsttime - now;
                // only log after firsttime
                if (distance < 0) {
                    var endtime = new Date(this.endtime).getTime();
                    distance = now - endtime;
                    if (distance > 0) {
                        this.notifyUser('warning', l('theme.exerciseover'), l('theme.noacceslogerr') + this.endtime + ' ');
                    } else {
                        dolog = true;
                    }
                } else {
                    this.notifyUser('warning', l('theme.exercisenotstarted'), l('theme.nologginpassiblepartone') + this.firsttime + l('theme.nologginpassibleparttwo'));
                }
            } else {
                this.notifyUser('warning', l('theme.norights'), l('You are not authorized to log'));
            }
            return dolog;
        },

        editLog(data) {
            if (this.canLog()) {
                this.editingLogs = data;
                this.showLogModal = true;
            }
        },

        editDirectLog() {
            if (this.showQuickLogging) {
                this.showQuickLogging = false
            } else {
                this.showQuickLogging = this.canLog();
            }
        },

        async submitScroll() {
            var tmp = {
                _token: this.csrfToken,
                mode: 'setScroll',
                scroll: !this.scroll
            }
            var path = '/api/setting';
            var method = 'POST';

            // simple sent to server setting - no show to user of errors
            this.logConsole('submitScroll scroll');
            const response = await fetch(path, {
                method: method,
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(tmp)
            })
                .then(response => response.json())
                .catch(err => {
                    console.debug('A fatal error has occured! ' + err);
                });
        },


        async logout() {
            await fetch('/api/user/logout', {
                method: 'GET'
            })
                .then(response => response.json())
                .then(data => {
                    if ("result" in data && data.result == true) {
                        window.location.href = "/";
                    } else {
                        console.error('Could not log out!');
                    }
                })
                .catch(err => {
                    console.error('A fatal error has occured. Reload this page.');
                });
        },

        updateLog: function (log) {
            console.debug('gameboard.updateLog.received: ', log);
            this.logConsole('Create new log');
            // note: log.id is unique
            this.logs[log.id] = log;
            this.rerenderAll();
        },

        updateAttack: function (attack) {
            console.debug('gameboard.updateAttack.received: ', attack);
            this.logConsole('Update attack');
            // note: attack.id is unique
            this.attacks[attack.id] = attack;
            this.rerenderAll();
        },

        notifyUser: function (type, title, text) {
            var context = {
                user: this.user,
                type: type,
                title: title,
                message: [text]
            };
            var notification = TransactionNotificationController.process('showNotify', '', context);
            if (notification) {
                this.notifications.push(notification);
                this.showNotification = true;
            }

        },

        async downloadLoggingPurple() {
            const tmp = {
                _token: this.csrfToken,
                user: this.user,
            };
            const path = '/api/download';
            const method = 'POST';

            // Log the action
            this.logConsole('Downloading log for party: ' + this.party);

            try {
                const response = await fetch(path, {
                    method: method,
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(tmp),
                });

                const contentType = response.headers.get('Content-Type');

                if (contentType && contentType.includes('application/json')) {
                    // Error response
                    const data = await response.json();
                    if ('message' in data) {
                        this.triggerError(data.message);
                    } else {
                        this.triggerError('An error occurred while downloading the file.');
                    }
                    return;
                }

                // Success response
                // Get the filename from the response headers, if provided
                const contentDisposition = response.headers.get('Content-Disposition');
                let filename = 'downloaded_file';

                if (contentDisposition && contentDisposition.indexOf('attachment') !== -1) {
                    const filenameMatch = contentDisposition.match(/filename="?([^"]+)"?/);
                    if (filenameMatch && filenameMatch[1]) {
                        filename = filenameMatch[1];
                    }
                }

                // Read the response as a Blob
                const blob = await response.blob();

                // Create a URL for the Blob and trigger a download
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename; // Set the filename
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            } catch (err) {
                console.error('An error has occurred:', err);
                this.triggerError('An unexpected error occurred while downloading the file.');
            }
        },

        notifyOff: function () {
            // function for removing off screen -> else quick log editor is not working anymore
            this.showNotification = false;
            this.notifications = [];
            this.logConsole('Notify off')
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
    }
};

const app = createApp(gameboard);

app.config.globalProperties.moment = require('moment');
app.config.globalProperties.delayedUpdateAllResponsiveFunctions = window.delayedUpdateAllResponsiveFunctions;
app.config.globalProperties.l = window.l;

//import variables from global settings
app.config.globalProperties.logmaxfilesize = window.gameboard_logmaxfilesize;
app.config.globalProperties.logmaxfiles = window.gameboard_logmaxfiles;
app.config.globalProperties.acceptedFileTypes = window.gameboard_acceptedfiletypes.split(',');
app.config.globalProperties.emitter = emitter;

app.use(veeValidatePlugin);

// Initialise VeeValidate, install rules and scheduler
// Object.keys(rules).forEach(rule => {
//     extend(rule, rules[rule]);
// });

/**
 * The following block of code may be used to automatically register your
 * Vue scheduler. It will recursively scan this directory for the Vue
 * scheduler and automatically register them with their "basename".
 *
 * Eg. ./scheduler/ExampleComponent.vue -> <example-component></example-component>
 */

app.component('user-display', UserDisplay);
app.component('login-modal', LoginModal);
app.component('logout-button', LogoutButton);
app.component('game-countdown', GameCountdown);
app.component('timeline', Timeline);
app.component('party', Party);
app.component('action', Action);
app.component('notification-box', NotificationBox);
app.component('notification', Notification);
app.component('log-modal', LogModal);
app.component('log-bubble', LogBubble);
app.component('system-modal', SystemModal);
app.component('timeline-scroller', TimelineScroller);
app.component('live-log', LiveLog);
app.component('parties-select', PartiesSelect);
app.component('party-select', PartySelect);
app.component('action-bubble', ActionBubble);
app.component('actions-list', ActionsList);
app.component('quick-logging', QuickLogging);
app.component('quick-bubble', QuickBubble);
app.component('help-file', HelpFile);
app.component('attachment-modal', AttachmentModal);

app.mount('#gameboard');

