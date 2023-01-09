<template>
  <transition name="modal" v-if="show">
    <div class="modal-mask">
      <div :class="{'shake animated': isError}" class="modal modal-large log-modal">

        <div class="modal-header">
          <h3 class="text-xl lg:text-4xl sm:text-2xl xm:text-1xl" >
            <span v-html="l('theme.logbook')"></span>
            <span >{{ editing.title }}</span>
          </h3>

          <button type="reset" @click="close()" class="btn btn-secondary btn-small close-button">X</button>
        </div>

        <div class="log-box">
          <log-bubble v-for="log in activeLogs" @edit-log="editLog" :key="logs[log.id].id" :log="log"></log-bubble>
        </div>

        <validation-observer v-slot="{ handleSubmit, reset }" id="LogModalForm">
          <form @submit.prevent="handleSubmit(submitForm)" @reset.prevent="reset">

            <div class="mb-3">
              <validation-provider name="log" rules="" v-slot="{ errors }">
                <textarea v-model="proxy.log" name="log" rows="10" :class="{'border-red-500' : errors[0]}"
                          class="focus:outline-none focus:shadow-outline" placeholder="Write your log here"></textarea>
                <div class="upload-log-attachments">
                  <input type="file" ref="uploadfiles" multiple @change="onAttachmentsChange" name="attachments">
                </div>
                <span class="input-error">{{ errors[0] }}</span>
              </validation-provider>
            </div>

            <div id="logmodalattachments" class="mb-3">
              <validation-provider name="attachments" rules="" v-slot="{ errors }">
                <h4><span v-html="l('theme.attachments')"></span>:</h4>
                <li class="attachmentholder" v-for="attachment in orgattachments" :key="attachment.id">
                  <a class="attachmentlink"
                     @click="openAttachmenModel((attachment.id), $event.target)">{{ attachment.file_name }}
                    <div class="loading_animation"></div>
                  </a>
                  <div class="attachmentmodifiers">
                    <a @click="softDeleteAttachment(attachment.id, $event)" class="removeattachment btn btn-tiny"><span
                        class="material-icons text-sm">delete</span></a>
                  </div>
                </li>
                <h5 v-if="proxy.attachments.length">Uncommitted Attachments: </h5>
                <li class="attachmentholder" v-for="attachment in proxy.attachments" :key="attachment.id">
                  <p class="attachmentlink">{{ attachment.name }}</p>
                </li>
                <span class="input-error">{{ errors[0] }}</span>
              </validation-provider>
            </div>

            <div :class="{'mb-4': !isError, 'mb-3': isError}">
              <validation-provider name="log" rules="" v-slot="{ errors }">
                <h4><span v-html="l('theme.timestamp')"></span>:</h4>
                <input v-model="proxy.timestamp" name="timestamp" :class="{'border-red-500' : errors[0]}"
                       class="focus:outline-none focus:shadow-outline" placeholder="Timestamp">
                <span class="input-error">{{ errors[0] }}</span>
              </validation-provider>
            </div>

            <div class="w-full mb-3 text-center text-red-500" v-if="isError">{{ errorMsg }}</div>

            <div class="flex items-center justify-between">
              <button type="reset" @click="close()"
                      class="w-1/2 py-2 px-4 rounded-r focus:outline-none focus:shadow-outline">Cancel
              </button>
              <button type="submit" id="idLogSubmitButton" :class="{disabled : proxy.log === ''}"
                      :disabled="proxy.log === ''"
                      class="w-1/2 bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded-l focus:outline-none focus:shadow-outline">
                <span v-html="l('theme.log')"></span>
              </button>
            </div>
          </form>
        </validation-observer>
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
    isError: false,
    errorMsg: '',
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
  }),

  watch: {
    show: {
      handler() {
        if (this.show) {
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
        attachments: '',
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

    onAttachmentsChange(e) {
      var maxfilesize = 12288 * 1024; // 12mb fallback when if global setting check below fails
      var maxfiles = 10;
      var acceptedExtensions = ["png", "jpg", "gif", "svg", "txt", "mp4", "csv", "gif", "json", "pdf", "zip"];
      if (this.logmaxfilesize) {
        maxfilesize = this.logmaxfilesize;
      }

      if (this.logmaxfiles) {
        maxfiles = this.logmaxfiles;
      }

      if (this.acceptedFileTypes && this.acceptedFileTypes.constructor === Array) {
        acceptedExtensions = this.acceptedFileTypes;
      }

      this.proxy.attachments = e.target.files || e.dataTransfer.files;
      if (this.proxy.attachments !== undefined) {
        if (!this.proxy.attachments.length)
          return;
        if (this.proxy.attachments.length > maxfiles) {
          this.removeattachments();
          alert("Quicklog: Max " + maxfiles + " attachments allowed");
        }
        for (let i = 0; i < Object.keys(this.proxy.attachments).length; i++) {
          let filetype = this.proxy.attachments[i].name.split('.').pop().toLowerCase();
          if (acceptedExtensions.indexOf(filetype) !== -1) {
            if (this.proxy.attachments[i].size > maxfilesize) {
              e.preventDefault();
              alert('File: ' + this.proxy.attachments[i].name + ' too big (> ' + Math.round(maxfilesize / 1024 / 1024) + 'mb )');
              this.removeattachments();
              return;
            } else {
              this.createattachments(this.proxy.attachments[i], i);
            }
          } else {
            e.preventDefault();
            alert('File: ' + this.proxy.attachments[i].name + ' type is not allowed. accepted filetypes are: ' + JSON.stringify(acceptedExtensions));
            this.removeattachments();
            return;
          }

        }
      }
    },

    createattachments(file, i) {
      //var attachments = new attachments();
      var reader = new FileReader();

      reader.onload = (e) => {
        if (this.proxy.attachments[i]) {
          this.proxy.attachments[i]['rawdata'] = e.target.result;
        }
      };
      reader.readAsDataURL(file);
      this.proxy.attachments[i]['filename'] = file.name;
    },

    removeattachments: function () {
      this.$refs.uploadfiles.value = null;
    },

    getLogs() {
      var granularity = this.editing.granularity;
      //var start = this.moment(this.editing.timestamp).subtract(granularity / 2, 'minutes');
      //var end = this.moment(this.editing.timestamp).add(granularity / 2, 'minutes');
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

    editLog(log) {
      console.debug('editLog: ', log);

      this.proxy.log = log.log;
      this.proxy.timestamp = this.moment(log.timestamp).format('HH:mm:ss');
      this.proxy.id = log.id;
      this.proxy.attachments = '';
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
      this.hideError();

      // Checking for max files again, or the user can add more files to an already large pile of files.
      var maxfiles = 10;
      if (this.logmaxfiles) {
        maxfiles = this.logmaxfiles;
      }
      if (this.proxy.log && this.orgattachments) {
        if (Object.keys(this.proxy.attachments).length + Object.keys(this.orgattachments).length > maxfiles) {
          this.removeattachments();
          alert("Quicklog: Max " + maxfiles + " attachments allowed");
          this.deleteAttachments();
          this.resetFields();
          return;
        }
      }

      var dt = this.moment(this.proxy.timestamp, 'HH:mm:ss');
      if (this.proxy.log && dt.isValid()) {
        var tmp = {
          _token: this.csrf,
          id: this.proxy.id,
          log: this.proxy.log,
          attachments: this.proxy.attachments,
          hasorgattachments: this.hasorgattachments(),
          deletefilesbyid: this.proxy.deletefilesbyid,
          timestamp: this.moment(this.proxy.timestamp, 'HH:mm:ss').format('HH:mm:ss')
        }

        this.deleteAttachments();
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
                  var log = JSON.parse(data.log);
                  // update directly local -> update by stream can be seconds..
                  this.$emit('update-log', log);
                }

                this.close();
              }
            })
            .catch(err => {
              this.triggerError('A fatal error has occured! ' + err);
            });

      } else {

        if (!dt.isValid()) {
          this.triggerError('Time value not valid');
        } else {
          this.triggerError('Empty log (text) - please write something');
        }
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
                Event.$emit('initAttachmentlog', data.file);
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
    toggleAttachmentlinks(remove = false) {
      var attachmentlinks = document.querySelectorAll('a.attachmentlink');
      for (let i = 0; i < Object.keys(attachmentlinks).length; i++) {
        if (remove) {
          attachmentlinks[i].classList.remove('disabled');
        } else {
          attachmentlinks[i].classList.add('disabled');
        }
      }
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

<style scoped>
</style>
