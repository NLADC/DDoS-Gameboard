<template>
    <div v-if="show" :class="{'shake animated': isError}" class="quicklogging-box">

        <div id="quicklogginbox-header"
             class="flex flex-row w-full border-b-2 border-prim bg-prim text-black font-semibold">
            <h3 class="w-1/2 text-black" v-html="l('theme.quicklog')"></h3>
            <h3 class="w-1/2 text-right">
                <button type="button" @click="close()" class="material-icons text-black focus:bg-prim">
                    cancel
                </button>
            </h3>
        </div>

        <div id="idQuickLogBox" class="quicklog-box">
            <quick-bubble v-for="log in activeLogs" :key="log.id" :log="log"></quick-bubble>

        </div>
        <Form id="quicklogging-form" :disabled="!editLog" @submit="submitForm">
            <div class="inputwrapper">
                    <textarea type="textarea" :disabled="!editLog"
                              v-on:keydown.enter="handleEnter"
                              class="focus:outline-none focus:shadow-outline bg-white text-black w-75 "
                              v-model="proxy.log" name="log" rows="2" cols="40"
                              placeholder="Write your log here; enter key is submit; shift-enter is newline"
                              @paste="pasteFromClipboard($event)"
                    ></textarea>
                <div class="w-full mb-3 text-center text-red-500" v-if="isError">{{ errorMsg }}</div>
                <ErrorMessage name="log" class="input-error"/>
                <div class="upload-log-attachments">
                    <div class="draganddropfiles" @dragover="dragover" @dragleave="dragleave" @drop="drop">
                        <input type="file" multiple name="fields[assetsFieldHandle][]" id="assetsFieldHandle"
                               class="w-px h-px opacity-0 overflow-hidden absolute" @change="onAttachmentsChange"
                               ref="file" :disabled="!editLog">
                        <label for="assetsFieldHandle" class="block cursor-pointer">
                            <div>
                                <span v-html="l('theme.dropfilesor')"></span> <span class="underline"><span
                                v-html="l('theme.clickhere')"></span></span>
                            </div>
                        </label>
                        <ul id="attachmentoverview" ref="AttachmentOverview" v-if="showAttachmentoverview"
                            class="mt-4" v-cloak>
                            <li class="text-sm p-1" v-for="filename in filenames">
                                {{ filename }}
                            </li>
                        </ul>
                        <a v-if="showAttachmentoverview" class="ml-2" type="button" @click="removeattachments()"
                           title="Remove file"><span class="underline" v-html="l('theme.removeall')"></span></a>
                    </div>
                </div>
                <ErrorMessage class="input-error"/>
            </div>

            <div class="actionswrapper">
                <input id="logTimespamp" class="focus:outline-none focus:shadow-outline w-auto "
                       v-model="proxy.timestamp"
                       readonly
                       name="timestamp"/>
                <i id="stopwatch" v-on:click="showStopwatch = !showStopwatch; initStopwatch()" v-if="this.isRedParty()"
                   title="Start an attack"
                   class="material-icons">timer</i>
                <button id="submitlogBtn" type="submit"
                        class="btn btn-primary"
                        :disabled="(proxy.log === '' && Object.keys(proxy.attachments).length === 0)"
                        v-html="l('theme.log')">
                </button>
            </div>
        </Form>
        <li id="currentattacks" v-if="this.isRedParty()" :class="{ show: showCurrentAttacks && showStopwatch }">
            <a class="currentattack" :class="{disabled : attack.status === 'stopped'}"
               v-for="attack in attacks" :id="'currentattack' + attack.id" :key="attack.id" @click="loadAttack(attack)">
                <span class="name">{{ attack.name }}</span>
                <span class="statusWrapper">
                    <span class="status started" v-if="attack.status === 'started'" v-html="l('theme.started')"></span>
                    <span class="status paused" v-if="attack.status === 'paused'" v-html="l('theme.paused')"></span>
                    <span class="status resumed" v-if="attack.status === 'resumed'" v-html="l('theme.resumed')"></span>
                    <span class="status stopped" v-if="attack.status === 'stopped'" v-html="l('theme.stopped')"></span>
                    <span class="updated_at">{{ attack.lastUpdated }}</span>
                </span>
            </a>
        </li>
        <div class="stopwatch" v-if="this.isRedParty()" :class="{ show: showStopwatch }">
            <i v-on:click="showCurrentAttacks = true;" v-if="!showCurrentAttacks"
               id="loadCurrentattacks" title="Load current attacks"
               class="material-icons">unfold_more</i>
            <i v-on:click="showCurrentAttacks = false;" v-if="showCurrentAttacks" id="loadCurrentattacks"
               title="Load current attacks"
               class="material-icons">unfold_less</i>
            <input id="AttackName" v-model="attackproxy.name" :disabled="stopwatchisRecording"
                   :class="{loaded: stopwatchisRecording}" type="search">
            <div class="buttons">
                <i class="recordicon"
                   :class="{ recording: stopwatchisRecording }"
                   @click="performStopwatchAction('start')">
                </i>
                <i class="material-icons pause"
                   :class="{ disabled: !stopwatchisRecording }"
                   v-if="!stopwatchPaused"
                   @click="!!stopwatchisRecording && performStopwatchAction('pause')">
                    pause
                </i>
                <i class="material-icons play"
                   :class="{ disabled: !stopwatchisRecording }"
                   v-if="stopwatchPaused"
                   @click="!!stopwatchisRecording && performStopwatchAction('resume')">
                    play_arrow
                </i>
                <i class="material-icons stop"
                   :class="{ disabled: !stopwatchisRecording }"
                   @click="!!stopwatchisRecording && performStopwatchAction('stop')">
                    stop
                </i>
                <i class="material-icons refresh" @click="initStopwatch()">refresh</i>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        show: Boolean,
        canEditLog: String,
        logs: Object,
        attacks: Object,
        user: Object,
        parties: Object,
        csrf: String,
        rerender: Boolean,
        rerenderattacks: Boolean,
    },

    data: () => ({
        editLog: false,
        filelist: [],
        isError: false,
        errorMsg: '',
        showAttachmentoverview: false,
        filenames: [],
        proxy: {
            id: 0,
            log: '',
            attachments: {},
            timestamp: ''
        },
        attackproxy: {
            id: '',
            name: '',
            status: 'undefined',
            starttime: '',
            endtime: '',
        },
        activeattacks: {},
        now: '',
        spawnTime: null,
        activeLogs: {},
        showStopwatch: false,
        stopwatchPaused: false,
        stopwatchisRecording: false,
        showCurrentAttacks: false,
        maxfilesize: 12288 * 1024, // 12mb fallback when if global setting check below fails
        maxfiles: 10,
        acceptedExtensions: ["png", "jpg", "gif", "svg", "txt", "mp4", "csv", "gif", "json", "pdf"], //falback values
    }),

    mounted() {
        // Getting global settings from the backend, otherwise remain as det in the data in this Vue component.
        if (this.logmaxfiles) this.maxfiles = this.logmaxfiles;
        if (this.logmaxfilesize) this.maxfilesize = this.logmaxfilesize
        if (this.acceptedFileTypes && Array.isArray(this.acceptedFileTypes)) this.acceptedExtensions = this.acceptedFileTypes;
        setInterval(this.timer.bind(this), 1000);


    },



    watch: {
        filenames: {
            handler(newVal) {
            },
            deep: true, // Watch changes within the array
        },
        rerender: {
            handler() {
                console.debug('Watch quicklogs rerender called');
                this.activeLogs = this.getLogs();
                this.$emit('rerendered');
            }
        },
        rerenderattacks: {
            handler() {
                console.debug('Watch attacks rerender called');
                this.activeattacks = this.getAttacks();
                this.$emit('rerenderattacks');
            }
        },
        show: {
            handler() {
                if (this.show) {
                    this.activeLogs = this.getLogs();
                    this.activeattacks = this.getAttacks();
                    this.editLog = this.getEditLogBool();
                }
            },
        },
        activeLogs: {
            handler() {
                this.scrollBottomTimeout();
            }
        },
        activeattacks: {
            handler() {
                this.attacksScrollBottomTimeout();
            }
        },

    },

    methods: {

        getEditLogBool(){
            let bool = (window.gameboard_edit == 'true');
          return bool;
        },

        /**
         * This function helps toggle the html when the drag and drop field is empty or filled with files, it also signals the vue to update.
         */
        hasProxyAttachments() {
            this.showAttachmentoverview = Object.keys(this.proxy.attachments).length > 0;
        },

        pasteFromClipboard(event) {
            // Get the clipboard data
            const clipboardData = event.clipboardData || window.clipboardData;

            // if we are not pasting text proceed
            if (!(clipboardData.types.includes('text/plain'))) {
                // Prevent filenames from pasting into chat
                event.preventDefault();


                if (event.clipboardData == false) {
                    if (typeof (event.callback) == "function") {
                        console.error('Paste event: No clipboard data')
                        event.callback(undefined);
                    }
                }
                let clipboarditems = event.clipboardData.files;

                if (clipboarditems == undefined) {
                    if (typeof (event.callback) == "function") {
                        event.callback(undefined);
                        console.error('pastevent: clipboarditems are undifined')
                    }
                }

                this.onAttachmentsChange(event, clipboarditems);
                // To speed up updating ul#attachmentoverview
                this.$nextTick(() => {
                    this.hasProxyAttachments();
                    const AttachmentOverview = this.$refs.AttachmentOverview;
                    AttachmentOverview.click();
                });
            }
        },

        onAttachmentsChange(event, files) {
            // Update Vue html
            this.hasProxyAttachments();

            if (!files) files = event.target.files;
            // If there are already proxy attachments don't overwrite their index.
            let highestindex = Object.keys(this.proxy.attachments).length || 0;
            if (highestindex + files.length > this.maxfiles) {
                files = [];
                alert("Quicklog: Max " + this.maxfiles + " attachments allowed");
                return;
            } else {
                for (let i = 0; i < Object.keys(files).length; i++) {
                    let filetype = files[i].name.split('.').pop().toLowerCase();
                    if (this.acceptedExtensions.indexOf(filetype) !== -1) {
                        if (files[i].size > this.maxfilesize) {
                            alert('File: ' + files[i].name + ' too big (> ' + Math.round(this.maxfilesize / 1024 / 1024) + 'mb )');
                            return;
                        } else {
                            // If all is set right then we can finally create an attachment in the attachment
                            this.createattachments(files[i], i + highestindex);
                        }
                    } else {
                        alert('File: ' + files[i].name + ' type is not allowed. Accepted filetypes are: ' + this.acceptedExtensions);
                        return;
                    }
                }
            }
            // For the v-if on the #showattachments <ul>
            this.hasProxyAttachments();
        },

        /**
         * There are only 2 thing we want to save, the name and its data, the rest is not of interest or kept in the log.
         * @param file = file type from a filelist
         * @param i = the index where it must be added to the proxy.attachments very important if there are already attachments in there
         */
        createattachments(file, i) {
            //var attachments = new attachments();
            var reader = new FileReader();

            this.proxy.attachments[i] = new Object();
            reader.onload = (event) => {
                if (this.filelist) {
                    this.proxy.attachments[i]['rawdata'] = event.target.result;
                }
            };
            reader.readAsDataURL(file);
            this.proxy.attachments[i]['filename'] = file.name;
            this.filenames.push(file.name);
            this.hasProxyAttachments();
        },


        removeattachments: function () {
            this.filelist = [];
            this.filenames = [];
            this.proxy.attachments = {};
            // For the v-if on the #showattachments <ul>
            this.hasProxyAttachments();
        },

        remove(i) {
            this.filelist.splice(i, 1);
            // For the v-if on the #showattachments <ul>
            this.hasProxyAttachments();
        },

        dragover(event) {
            event.preventDefault();
            // Add some visual fluff to show the user can drop its files
            if (!event.currentTarget.classList.contains('dragover')) {
                event.currentTarget.classList.remove('dragleave');
                event.currentTarget.classList.add('dragover');
            }
        },
        dragleave(event) {
            // Clean up
            event.currentTarget.classList.add('dragleave');
            event.currentTarget.classList.remove('dragover');
        },
        drop(event) {
            event.preventDefault();
            let files = event.dataTransfer.files;
            this.onAttachmentsChange(event, files); // Trigger the onAttachmentsChange event manually
            // Clean up
            event.currentTarget.classList.add('dragleave');
            event.currentTarget.classList.remove('dragover');
        },

        toggleAttachmentlinks(remove = false) {
            var attachmentlinks = document.querySelectorAll('a.attachmentlink');
            for (let i = 0; i < Object.keys(attachmentlinks).length; i++) {
                attachmentlinks[i].classList.toggle('disabled', !remove);
            }
        },

        timer() {
            this.now = this.proxy.timestamp = this.moment().format('HH:mm');
        },

        handleEnter(event) {
            if (!event.shiftKey) {
                this.submitForm();
                event.preventDefault();
            }
        },

        isRedParty() {
            if (this.user !== undefined) {
                return this.user.role === "red";
            } else {
                return false;
            }
        },

        getLogs() {
            var logs = [];
            Object.entries(this.logs).forEach(function (log, idx) {
                log = log[1];
                logs.push(log);
            }.bind(this));

            logs.sort(function (a, b) {
                return a.timestamp < b.timestamp ? -1 : a.timestamp > b.timestamp ? 1 : 0;
            });

            return logs;
        },

        getAttacks() {
            var attacks = [];
            Object.entries(this.attacks).forEach(function (attack, idx) {
                attack = attack[1];
                attacks.push(attack);
            }.bind(this));

            attacks.sort(function (a, b) {
                return a.timestamp < b.timestamp ? -1 : a.timestamp > b.timestamp ? 1 : 0;
            });
            return attacks;
        },

        scrollBottom() {
            var quicklog = document.getElementById('idQuickLogBox');
            if (quicklog) quicklog.scrollTop = quicklog.scrollHeight;
        },
        scrollBottomTimeout() {
            // note: after 1 sec, else html not ready
            setTimeout(this.scrollBottom, 1000);
        },

        triggerError(msg) {
            this.isError = true;
            this.errorMsg = msg;
            setTimeout(this.scrollBottom, 1000);
        },

        hideError() {
            this.isError = false;
            this.errorMsg = '';
        },

        close() {
            this.$emit('close');
        },

        resetFields() {
            this.proxy = {
                log: '',
                timestamp: '',
                attachments: ''
            };
        },

        /**
         * this function will fill the stopwatch name field with the action that is currenlty assigned to users party
         */
        initStopwatch() {
            this.performStopwatchAction('reset');
            if (this.showStopwatch) {
                const party = Object.values(this.parties || {}).find(p => p.id === this.user.party_id);
                if (party) {
                    const actions = Object.values(party.actions || {});
                    const now = this.moment();
                    const previousAction = actions.slice().reverse().find(a => a.start.diff(now) < 0);
                    this.attackproxy.name = previousAction ? previousAction.name : '';
                }
            }
        }
        ,

        performStopwatchAction(action) {
            switch (action) {
                case 'start':
                    if (!this.stopwatchisRecording && !this.attackproxy.name) {
                        this.triggerError('Empty attack name - please write something');
                        return;
                    } else {
                        this.attackproxy.status = "started";
                        this.stopwatchisRecording = true;
                        this.handleAttackChanges();
                    }
                    break;
                case
                'pause':
                    if (this.stopwatchisRecording) {
                        this.stopwatchPaused = true;
                        this.attackproxy.status = "paused";
                        this.handleAttackChanges();
                    }
                    break;
                case
                'resume':
                    if (this.stopwatchisRecording) {
                        this.stopwatchPaused = false;
                        this.attackproxy.status = "resumed";
                        this.handleAttackChanges();
                    }
                    break;
                case
                'stop':
                    if (this.stopwatchisRecording) {
                        this.attackproxy.status = "stopped";
                        this.stopwatchPaused = false;
                        this.handleAttackChanges();
                        this.performStopwatchAction('reset');
                        this.initStopwatch();
                    }
                    break;
                case
                'reset':
                    this.attackproxy.id = '';
                    this.attackproxy.name = '';
                    this.attackproxy.status = '';
                    this.attackproxy.starttime = '';
                    this.attackproxy.endtime = '';
                    this.stopwatchPaused = false;
                    this.stopwatchisRecording = false;
                    break;
            }
        },

        /**
         * To load an attack that has already been created
         * @param attack
         */
        loadAttack(attack) {
            const {id, name, status, created_at} = attack;
            this.attackproxy = {id, name, status, starttime: created_at};

            this.stopwatchPaused = false;

            if (status.includes("started") || status.includes("resumed")) {
                this.stopwatchisRecording = true;
                this.stopwatchPaused = false;
            } else if (status.includes("paused")) {
                this.stopwatchisRecording = true;
                this.stopwatchPaused = true;
            } else if (status.includes("stopped")) {
                this.stopwatchisRecording = false;
                this.stopwatchPaused = false;
            }

            this.toggleAttackClasses(id);
        },

        /**
         * this will toggle a loaded class in the input field for a attack name
         * @param attackid
         */
        toggleAttackClasses(attackid) {
            try {
                let currentattacks = document.querySelectorAll('.currentattack');
                for (let i = 0; i < currentattacks.length; i++) {
                    if (currentattacks[i].classList.contains('loaded')) {
                        currentattacks[i].classList.remove('loaded');
                    }
                }
            } catch (err) {
                this.triggerError('Can\'t DOM elements when removing .loading class: ' + err.message);
            }

            try {
                let currentTarget = document.querySelector('.currentattack[id*="' + attackid + '"]');
                currentTarget.classList.add('loaded');
            } catch (err) {
                this.triggerError('Can\'t DOM element loading attack: ' + err.message);
            }

        },

        attacksScrollBottom() {
            let currentattacks = document.getElementById('currentattacks');
            if (currentattacks) currentattacks.scrollTop = currentattacks.scrollHeight;
        },

        attacksScrollBottomTimeout() {
            // note: after 1 sec, else html not ready
            setTimeout(this.attacksScrollBottom, 1000);
        },

        async handleAttackChanges() {
            this.hideError();
            if (this.attackproxy.name !== null) {
                var tmp = {
                    _token: this.csrf,
                    id: this.attackproxy.id,
                    name: this.attackproxy.name,
                    status: this.attackproxy.status,
                    party_id: this.user.party_id,
                    user_id: this.user.id,
                    timestamp: this.moment(this.proxy.timestamp.timestamp).format('HH:mm:ss')
                }

                var path = '/api/attack';
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
                        if ('result' in data && data.result === false && 'message' in data)
                            this.triggerError(data.message);
                        else {
                            if ('result' in data && data.result === true) {
                                let attack = data.attack;
                                //update directly local -> update by stream can be seconds..
                                this.attacks[attack.id] = attack;
                                if (attack.status.includes("started") ||
                                    attack.status.includes("paused") ||
                                    attack.status.includes("resumed")) {
                                    this.attackproxy.id = attack.id;
                                } else {
                                    this.attackproxy.id = '';
                                }
                                this.$emit('update-attack', attack);
                            }
                        }
                    })
                    .catch(err => {
                        this.triggerError('A fatal error has occured! ' + err);
                    });

            } else {
                this.triggerError('Empty attack name - please write something');
            }
        },
        async submitForm() {
            // Check if we are inside the time bounds of an exercise
            if (this.getEditLogBool()) {
                this.hideError();
                if (this.proxy.log || Object.keys(this.proxy.attachments).length > 0) {
                    var tmp = {
                        _token: this.csrf,
                        id: this.proxy.id,
                        log: this.proxy.log,
                        attachments: this.proxy.attachments,
                        timestamp: this.moment(this.proxy.timestamp.timestamp).format('HH:mm:ss')
                    }
                    this.resetFields();
                    var path = '/api/log';
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
                                if ('result' in data && data.result == true && 'log' in data) {
                                    let log = JSON.parse(data.log);
                                    // update directly local -> update by stream can be seconds..
                                    this.logs[log.id] = log;
                                    this.$emit('update-log', log);
                                    // Show message if there is one.
                                    if ('message' in data && data.message.length >= 1) {
                                        this.triggerError(data.message);
                                    }
                                }
                            }
                        })
                        .catch(err => {
                            this.triggerError('A fatal error has occured! ' + err);
                        });

                } else {
                    this.triggerError('Empty log (text or attachment) - please add something');
                }
                this.removeattachments();
            }
        },

    },
}
</script>

