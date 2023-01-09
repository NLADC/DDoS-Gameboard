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
