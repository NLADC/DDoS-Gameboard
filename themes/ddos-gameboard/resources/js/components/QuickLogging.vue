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
      <div class="w-full mb-3 text-center text-red-500" v-if="isError">{{ errorMsg }}</div>
    </div>

    <form id="quicklogging-form" @submit.prevent="submitForm">
      <div class="inputwrapper">
        <validation-provider class="" name="log" rules="" v-slot="{ errors }">
                    <textarea
                        v-on:keydown.enter="handleEnter"
                        class="focus:outline-none focus:shadow-outline bg-white text-black w-75 "
                        v-model="proxy.log" name="log" rows="2" cols="40"
                        :class="{'border-red-500' : errors[0]}"
                        placeholder="Write your log here; enter key is submit; shift-enter is newline"></textarea>
          <div class="upload-log-attachments">
            <input type="file" ref="uploadfiles" multiple @change="onAttachmentsChange" name="attachments">
          </div>
          <span class="input-error">{{ errors[0] }}</span>
        </validation-provider>
      </div>

      <div class="actionswrapper">
        <input id="logTimespamp" class="focus:outline-none focus:shadow-outline w-auto " v-model="proxy.timestamp"
               readonly
               name="timestamp"/>
        <i id="stopwatch" v-on:click="showStopwatch = !showStopwatch; initStopwatch()" v-if="this.isRedParty()"
           title="Start an attack"
           class="material-icons">timer</i>
        <button id="submitlogBtn" type="submit"
                class="btn btn-primary" :class="{disabled : proxy.log === ''}"
                :disabled="proxy.log === ''" v-html="l('theme.log')">
        </button>
      </div>
    </form>
    <li id="currentattacks" v-if="this.isRedParty()" :class="{ show: showCurrentAttacks && showStopwatch }">
      <a class="currentattack" :class="{disabled : attack.status === 'stopped'}"
         v-for="attack in attacks" :id="'currentattack' + attack.id" :key="attack.id" @click="loadAttack(attack)">
        <span class="name">{{ attack.name }}</span>
        <span class="status started" v-if="attack.status === 'started'" v-html="l('theme.started')">Started</span>
        <span class="status paused" v-if="attack.status === 'paused'" v-html="l('theme.paused')">Paused</span>
        <span class="status resumed" v-if="attack.status === 'resumed'" v-html="l('theme.resumed')">Resumed</span>
        <span class="status stopped" v-if="attack.status === 'stopped'" v-html="l('theme.stopped')">Stopped</span>
      </a>
    </li>
    <div class="stopwatch" v-if="this.isRedParty()" :class="{ show: showStopwatch }">
      <i v-on:click="showCurrentAttacks = true;" v-if="!showCurrentAttacks"
         id="loadCurrentattacks" title="Load current attacks"
         class="material-icons">unfold_more</i>
      <i v-on:click="showCurrentAttacks = false;" v-if="showCurrentAttacks" id="loadCurrentattacks"
         title="Load current attacks"
         class="material-icons">unfold_less</i>
      <input id="AttackName" v-model="attackproxy.name" :disabled="stopWatchisRecording"
             :class="{loaded: stopWatchisRecording}" type="search">
      <div class="buttons">
        <i class="recordicon"
           :class="{ recording: stopWatchisRecording }"
           @click="startStopwatch()">
        </i>
        <i class="material-icons pause"
           :class="{ disabled: !stopWatchisRecording }"
           v-if="!stopwatchPaused"
           @click="!!stopWatchisRecording && pauseStopwatch()">
          pause
        </i>
        <i class="material-icons play"
           :class="{ disabled: !stopWatchisRecording }"
           v-if="stopwatchPaused"
           @click="!!stopWatchisRecording && resumeStopwatch()">
          play_arrow
        </i>
        <i class="material-icons stop"
           :class="{ disabled: !stopWatchisRecording }"
           @click="!!stopWatchisRecording && stopStopwatch()">
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
    logs: Object,
    attacks: Object,
    user: Object,
    parties: Object,
    csrf: String,
    rerender: Boolean,
    rerenderattacks: Boolean,
  },

  data: () => ({
    isError: false,
    errorMsg: '',
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
    stopWatchisRecording: false,
    showCurrentAttacks: false,
  }),

  mounted() {
    setInterval(this.timer.bind(this), 1000);
  },

  watch: {
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
        }
      },
    },
    activeLogs: {
      handler() {
        this.scrollBottomTImeout();
      }
    },
    activeattacks: {
      handler() {
        this.attacksScrollBottomTimeout();
      }
    },
  },

  methods: {

    onAttachmentsChange(e) {
      // Fallback values when global are not loaded
      var maxfilesize = 12288 * 1024; // 12mb fallback when if global setting check below fails
      var maxfiles = 10;
      var acceptedExtensions = ["png", "jpg", "gif", "svg",  "txt", "mp4", "csv", "gif", "json", "pdf", "zip"];
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
          alert('File: ' + this.proxy.attachments[i].name + ' type is now allowed. Accapted filtetypes are: ' + acceptedExtensions);
          this.removeattachments();
          return;
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

    timer() {
      this.now = this.proxy.timestamp = this.moment().format('HH:mm');
    },

    handleEnter(e) {
      if (!e.shiftKey) {
        this.submitForm();
        e.preventDefault();
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
        //console.debug('log.user='+log.user.name+', user='+this.user.name);
        //if (log.user.name == this.user.name)
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
        //console.debug('attack.user='+attack.user.name+', user='+this.user.name);
        //if (attack.user.name == this.user.name)
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
    scrollBottomTImeout() {
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

    initStopwatch() {
      this.resetStopWatch();
      if (this.showStopwatch) {
        if (this.parties && Object.keys(this.parties).length > 0
            && this.user && Object.keys(this.user).length > 0) {
          let parties = Object.values(this.parties);
          for (let i = 0; i < Object.keys(parties).length; i++) {
            if (parties[i].id === this.user.party_id) {
              let actions = Object.values(parties[i].actions);
              let now = this.moment();
              for (let i = 0; i < Object.keys(actions).length; i++) {
                if (actions[i].start.diff(now) > 0) {
                  let previ = i - 1;
                  if (previ < 0) {
                    this.attackproxy.name = '';
                  } else {
                    this.attackproxy.name = actions[previ].name;
                  }
                  break;
                }
              }
            }
          }
        }

      }
    },

    startStopwatch() {
      if (!this.attackproxy.name) {
        this.triggerError('Empty attack name - please write something');
      } else if (!this.stopWatchisRecording) {
        this.attackproxy.status = "started";
        this.stopWatchisRecording = true;
        this.handleAttackChanges();
      }
    },

    pauseStopwatch() {
      if (!this.attackproxy.name) {
        this.triggerError('Empty attack name - please write something');
      } else if (this.stopWatchisRecording) {
        let name = this.attackproxy.name;
        this.stopwatchPaused = true;
        this.attackproxy.status = "paused";
        this.handleAttackChanges();
      }
    },

    resumeStopwatch() {
      if (!this.attackproxy.name) {
        this.triggerError('Empty attack name - please write something');
      } else if (this.stopWatchisRecording) {
        this.stopwatchPaused = false;
        this.attackproxy.status = "resumed";
        this.handleAttackChanges();
      }
    },

    stopStopwatch() {
      if (!this.attackproxy.name) {
        this.triggerError('Empty attack name - please write something');
      } else if (this.stopWatchisRecording) {
        this.attackproxy.status = "stopped";
        this.stopwatchPaused = false;
        this.handleAttackChanges();
        this.resetStopWatch();
        this.initStopwatch();
      }
    },

    resetStopWatch() {
      this.attackproxy.id = '';
      this.attackproxy.name = '';
      this.attackproxy.status = '';
      this.attackproxy.starttime = '';
      this.attackproxy.endtime = '';
      this.stopwatchPaused = false;
      this.stopWatchisRecording = false;
    },

    loadAttack(attack) {
      this.attackproxy.id = attack.id;
      this.attackproxy.name = attack.name;
      this.attackproxy.status = attack.status;
      this.attackproxy.starttime = attack.created_at;
      this.stopwatchPaused = false;
      this.stopWatchisRecording = true;
      if (this.attackproxy.status.includes("started") || this.attackproxy.status.includes("resumed")) {
        this.stopWatchisRecording = true;
        this.stopwatchPaused = false;
      }
      if (this.attackproxy.status.includes("paused")) {
        this.stopWatchisRecording = true;
        this.stopwatchPaused = true;
      }
      if (this.attackproxy.status.includes("stopped")) {
        this.stopWatchisRecording = false;
        this.stopwatchPaused = false;
      }
      this.toggleAttackClasses(attack.id);
    },

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
      this.hideError();
      if (this.proxy.log) {
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
        this.triggerError('Empty log (text) - please write something');
      }
      this.removeattachments();
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
  },

}
</script>

