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
