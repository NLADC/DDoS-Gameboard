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
  <div class="party-bubble" >
    <div class="flex">
      <div class="name"><input type="checkbox" :checked="groupChecked" @click="submitCheck(group.id,group.name)" :value="group.id"
                               :id="groupSelectid"> <label :for="groupSelectid">{{ group.name }}</label></div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    group: Object,
    csrf: String,
    groups: Object
  },

  computed: {
    groupSelectid() {
      return 'groupselect' + this.group.id;
    },

    groupChecked() {
      return ((this.groups[this.group.id].show == true) ? 'CHECKED' : '');
    }

  },

  methods: {

    async submitCheck(id,name) {

      var elm = document.getElementById('groupselect' + id);
      console.debug('elm.value=' + elm.value + ', checked=' + elm.checked);
      this.groups[elm.value].show = elm.checked;

      // very dirty
      var groupelm = document.getElementById('id' + name);
      if (groupelm) {
          groupelm.style.setProperty('display',(elm.checked?'block':'none'));
      }

    }
  }

};
</script>0
