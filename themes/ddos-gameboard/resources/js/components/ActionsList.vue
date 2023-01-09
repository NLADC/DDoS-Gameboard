<template>
  <transition name="modal" v-if="show">
    <div class="modal-mask">
      <div :class="{'shake animated': isError}" class="modal modal-fullscreen log-modal">


        <div class="modal-header">
          <h3 class="text-xl lg:text-4xl sm:text-2xl xm:text-1xl">{{ partyName }} <span v-html="l('theme.actions')"></span></h3>
          <button type="reset" @click="close()" class="btn btn-secondary btn-small close-button">X</button>
        </div>
        <h3 class="current-time" @click="proxy.timestamp = now">{{ now }}</h3>

        <div id="idLiveLogBox" class="log-box">
          <action-bubble v-for="action in partyActions()" :key="action.id" :action="action"></action-bubble>
        </div>

        <div class="flex items-center justify-between">
          <button type="reset" @click="close()"
                  class="w-full py-2 px-4 rounded-r focus:outline-none focus:shadow-outline" v-html="l('theme.close')">
          </button>
        </div>

      </div>
    </div>
  </transition>
</template>

<script>
export default {
  props: {
    show: Boolean,
    parties: Object,
    logs: Object
  },

  data: () => ({
    isError: false,
    errorMsg: '',
    proxy: {
      log: '',
      timestamp: ''
    },
    now: '',
    sortedParties: {},
    partyName: '',
    spawnTime: null
  }),

  mounted() {
    this.sortedParties = _.orderBy(this.parties, 'sortkey');
    this.partyName = this.sortedParties[0].name;
    setInterval(this.timer.bind(this), 1000);
  },

  watch: {
    show: {
      handler() {
        if (this.show == true) {
          this.setFields();
          this.spawnTime = Date.now();
        }
      }
    }
  },

  methods: {

    partyActions() {
      return this.sortedParties[0].actions;
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
        log: '',
        timestamp: ''
      };
    },

    resetFields() {
      this.proxy = {
        log: '',
        timestamp: ''
      };
    },

    close() {
      if ((Date.now() - this.spawnTime) > 350) {
        this.hideError();
        this.resetFields();
        this.$emit('close');
      }
    },

    scrollBottom() {
      var livelog = document.getElementById('idLiveLogBox');
      if (livelog) livelog.scrollTop = livelog.scrollHeight;
    },

    timer() {
      this.now = this.moment().format('HH:mm:ss');
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
    }

  }
}
</script>

<style scoped>
</style>
