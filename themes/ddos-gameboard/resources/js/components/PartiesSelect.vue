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
      <div class="modal system-modal">
        <div class="modal-header">
          <h3 class="text-xl lg:text-3xl sm:text-2xl xm:text-1xl" v-html="l('theme.selectparties')"></h3>
          <button type="reset" @click="close()" class="btn btn-secondary btn-small close-button">X</button>
        </div>
        <div class="party-select">
          <party-select v-for="party in parties" :key="party.id" :csrf="csrf" :parties="parties"
                        :party="party"></party-select>
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
    showPartiesSelect: Boolean,
    show: Boolean,
    csrf: String,
    parties: Object
  },

  methods: {
    close() {
      this.$emit('close')
    }
  }

}
</script>

<style scoped>
</style>
