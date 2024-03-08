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
  <transition name="modal" v-if="show">
    <div class="modal-mask">
      <div :class="{'shake animated': isError}" class="modal modal-fullscreen log-modal">
        <div class="modal-header">
          <h3 class="text-xl lg:text-4xl sm:text-2xl xm:text-1xl" v-html="l('theme.livelogging')"></h3>
          <button type="reset" @click="close()" class="btn btn-secondary btn-small close-button">X</button>
        </div>
        <div class="modal-subheader">
          <h3 class="current-time" @click="proxy.timestamp = now">{{ now }}</h3>
          <div class="checkboxes">
            <input
                type="checkbox"
                v-model="showBlue"
            />
            <label for="checkbox" v-html="l('theme.showblue')"></label>
            <input
                type="checkbox"
                v-model="showRed"
            />
            <label for="checkbox" v-html="l('theme.showred')"></label>
          </div>
        </div>
        <div id="idLiveLogBox" class="log-box" :class="{
          hidered: !showRed,
           hideblue: !showBlue
        }">
          <log-bubble v-for="log in activeLogs" :key="logs[log.id].id" :log="log"></log-bubble>
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
    logs: Object,
    rerender: Boolean
  },

  data: () => ({
    isError: false,
    errorMsg: '',
    proxy: {
      log: '',
      timestamp: ''
    },
    now: '',
    spawnTime: null,
    activeLogs: {},
    showRed: true,
    showBlue: true,
  }),

  mounted() {
    setInterval(this.timer.bind(this), 1000);
  },

  watch: {
    rerender: {
      handler() {
        console.debug('Watch livelogs rerender called');
        this.activeLogs = this.getLogs();
        this.$emit('rerendered');
      }
    },
    show: {
      handler() {
        if (this.show) {
          this.activeLogs = this.getLogs();
        }
      }
    },
    activeLogs: {
      handler() {
        // note: after 1 sec, else new log not inserted for correct scrollHeight
        setTimeout(this.scrollBottom, 1000);
      }
    }
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
      this.hideError();
      this.$emit('close');
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
        log.showedit = false;
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
