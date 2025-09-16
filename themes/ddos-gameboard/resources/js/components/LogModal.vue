<template>
    <transition name="modal" v-if="show">
        <div class="modal-mask">
            <div :class="{'shake animated': isError}" class="modal modal-large log-modal">

                <div class="modal-header">
                    <h3 class="text-xl lg:text-4xl sm:text-2xl xm:text-1xl">
                        <span v-html="l('theme.logbook')"></span> :
                        <span>{{ editing.title }}</span>
                    </h3>

                    <button type="reset" @click="close()" class="btn btn-secondary btn-small close-button">X</button>
                </div>

                <div class="log-box">
                    <log-bubble v-for="log in activeLogs" @editLog="editLog" :key="logs[log.id].id"
                                :log="log"></log-bubble>
                </div>

                <Form @submit="submitForm" :disabled="!editLog">
                    <div class="mb-3">
             <textarea type="textarea" :disabled="!editLog"
                       v-on:keydown.enter="handleEnter"
                       class="focus:outline-none focus:shadow-outline bg-white text-black w-75 "
                       v-model="proxy.log" name="log" rows="2" cols="40"
                       placeholder="Write your log here; enter key is submit; shift-enter is newline"
                       @paste="pasteFromClipboard($event)"
             ></textarea>
                        <ErrorMessage name="log" class="input-error"/>
                        <div class="upload-log-attachments">
                            <div class="draganddropfiles" @dragover="dragover" @dragleave="dragleave" @drop="drop">
                                <input type="file" :disabled="!editLog" multiple name="fields[assetsFieldHandle][]" id="assetsFieldHandle"
                                       class="w-px h-px opacity-0 overflow-hidden absolute"
                                       @change="onAttachmentsChange"
                                       ref="file">
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
                    </div>

                    <div id="logmodalattachments" class="mb-3">
                        <div class="attachments-wrapper">
                            <div class="message">{{ proxy.log.log }}</div>
                            <div class="attachments">
                                <li v-for="attachment in proxy.log.attachments" :key="proxy.log.attachments.id">
                                    <a class="attachmentlink"
                                       @click="openAttachmenModel((attachment.id), $event.target)">{{
                                            attachment.file_name
                                        }}
                                        <div class="loading_animation"></div>
                                    </a>

                                </li>
                            </div>
                        </div>
                    </div>
                    <div class="w-full mb-3 text-center text-red-500" v-if="isError">{{ errorMsg }}</div>

                    <div class="flex items-center justify-between">
                        <button type="reset" @click="close()"
                                class="w-1/2 py-2 px-4 rounded-r focus:outline-none focus:shadow-outline"
                                v-html="l('theme.cancel')">
                        </button>
                        <button type="submit" id="idLogSubmitButton"
                                :disabled="(proxy.log === '' && Object.keys(proxy.attachments).length === 0)"
                                class="w-1/2 bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded-l focus:outline-none focus:shadow-outline">
                            <span v-html="l('theme.log')"></span>
                        </button>
                    </div>
                </Form>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    props: {
        show: Boolean,
        csrf: String,
        logs: Object,
        user: Object,
        editing: Object
    },

    data: () => ({
        editLog: false,
        isError: false,
        errorMsg: '',
        showAttachmentoverview: false,
        filenames: [],
        proxy: {
            log: '',
            timestamp: '',
            id: 0,
            attachments: {},
            deletefilesbyid: [],
        },
        orgattachments: {},
        spawnTime: null,
        submitdisabled: false,
        maxfilesize: 12288 * 1024, // 12mb fallback when if global setting check below fails
        maxfiles: 10,
        acceptedExtensions: ["png", "jpg", "gif", "svg", "txt", "mp4", "csv", "gif", "json", "pdf"], // falback values
    }),

    mounted() {
        // Getting global settings from the backend, otherwise remain as det in the data in this Vue component.
        if (this.logmaxfiles) this.maxfiles = this.logmaxfiles;
        if (this.logmaxfilesize) this.maxfilesize = this.logmaxfilesize
        if (this.acceptedFileTypes && Array.isArray(this.acceptedFileTypes)) this.acceptedExtensions = this.acceptedFileTypes;

        setInterval(this.timer.bind(this), 1000);
    },

    watch: {
        show: {
            handler() {
                if (this.show) {
                    this.editLog = this.getEditLogBool();
                    this.setFields();
                    this.spawnTime = Date.now();
                }
            }
        }
    },

    computed: {
        activeLogs() {
            return this.getLogs();
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

            this.proxy.attachments[i] = {};
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
            var granularity = this.editing.granularity;
            var start = this.moment(this.editing.timestamp);
            var end = this.moment(this.editing.timestamp).add(granularity, 'minutes');
            var logs = [];
            Object.entries(this.logs).forEach(function (log, idx) {
                log = log[1];
                log.showedit = (log.user.name == this.user.name);
                if (log.timestamp >= start && log.timestamp < end)
                    logs.push(log);
            }.bind(this));

            logs.sort(function (a, b) {
                return a.timestamp > b.timestamp ? -1 : a.timestamp < b.timestamp ? 1 : 0;
            });

            return logs;
        },

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
                id: 0,
                log: '',
                timestamp: this.moment(this.editing.timestamp).format('HH:mm:ss'),
                attachments: this.proxy.attachments,
                deletefilesbyid: '',
            };
            this.orgattachments = {};
            this.setSubmitButton('Log');
        },

        resetFields() {
            this.proxy = {
                log: '',
                timestamp: '',
                attachments: {},
                deletefilesbyid: '',
            };
            this.orgattachments = '';
        },

        close() {
            if ((Date.now() - this.spawnTime) > 350) {
                this.hideError();
                this.resetFields();
                this.$emit('close');
            }
        },
        editLog(log) {
            console.debug('editLog: ', log);

            this.proxy.log = log.log;
            this.proxy.timestamp = this.moment(this.editing.timestamp).format('HH:mm:ss'),
            this.proxy.id = log.id;
            this.proxy.attachments = {};
            this.proxy.deletefilesbyid = [];
            this.orgattachments = log.attachments;

            this.setSubmitButton('Update');
        },

        hasorgattachments() {
            if (this.orgattachments) {
                return true;
            } else {
                return false;
            }
        },

        setSubmitButton(txt) {
            var elm = document.getElementById('idLogSubmitButton');
            if (elm) elm.innerText = txt;
        },


        async submitForm() {
            if (this.getEditLogBool()) {
                this.hideError();

                if (this.proxy.log || Object.keys(this.proxy.attachments).length > 0 ) {
                    var tmp = {
                        _token: this.csrf,
                        id: this.proxy.id,
                        log: this.proxy.log,
                        attachments: this.proxy.attachments,
                        timestamp: this.moment(this.editing.timestamp).format('HH:mm:ss'),
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
                            console.debug(data);
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


        async openAttachmenModel(id, target) {
            this.hideError();
            var path = '/api/attachments';
            var method = 'POST';
            var tmp = {
                id: id,
            }
            let parentfound = false;

            if (target) {
                target.classList.add('loading');
                parentfound = true;
                this.toggleAttachmentlinks();

            }
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
                            this.emitter.emit('initAttachmentlog', data.file);
                        }
                    }
                    if (parentfound) {
                        target.classList.remove('loading');
                    }
                    this.toggleAttachmentlinks(true);
                })
                .catch(err => {
                    this.triggerError('A fatal error has occured! ' + err);
                });

        },

        softDeleteAttachment(logid, event) {
            // loop trough log attachments until right id is found and mark for deleting
            for (let i = 0; i < Object.keys(this.orgattachments).length; i++) {
                if (this.orgattachments[i].id === logid) {
                    this.orgattachments[i].delete = true;
                }
            }
            // add class so end user gets feedback via styling by class
            let attachmentholder = event.path[3];
            if (attachmentholder) {
                attachmentholder.classList.add("delete");
            }
        },

        deleteAttachments() {
            // loop trough log attachments until right id is found and send to api to delete these files
            if (this.orgattachments !== undefined) {
                for (let i = 0; i < Object.keys(this.orgattachments).length; i++) {
                    if (this.orgattachments[i].delete === true) {
                        this.proxy.deletefilesbyid.push(this.orgattachments[i].id);
                        this.orgattachments.splice([i], 1);
                    }
                }
            }
        },
    }
}
</script>
