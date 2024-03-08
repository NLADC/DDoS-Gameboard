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
  <div class="party-bubble" v-if="party.sortkey > 1">
    <div class="flex">
      <div class="name"><input type="checkbox" :checked="partyChecked" @click="submitCheck(party.id)" :value="party.id"
                               :id="partySelectid"> <label :for="partySelectid">{{ party.name }}</label></div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    party: Object,
    csrf: String,
    parties: Object
  },

  computed: {
    partySelectid() {
      return 'partyselect' + this.party.id;
    },

    partyChecked() {
      return ((this.parties[this.party.id].show == true) ? 'CHECKED' : '');
    }

  },

  methods: {

    async submitCheck(id) {

      var elm = document.getElementById('partyselect' + id);
      console.debug('elm.value=' + elm.value + ', checked=' + elm.checked);
      this.parties[elm.value].show = elm.checked;

      var tmp = {
        _token: this.csrf,
        mode: 'setParties',
        show: elm.checked,
        partyId: id
      }

      var path = '/api/setting';
      var method = 'POST';

      // simple sent to server setting - no show to user of errors
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
    }
  }

};
</script>0
