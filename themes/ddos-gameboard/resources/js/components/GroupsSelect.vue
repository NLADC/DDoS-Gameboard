<template>
  <transition name="modal" v-if="show">
    <div class="modal-mask">
      <div class="modal system-modal">
        <div class="modal-header">
          <h3 class="text-xl lg:text-3xl sm:text-2xl xm:text-1xl" v-html="l('theme.selectgroups')"></h3>
          <button type="reset" @click="close()" class="btn btn-secondary btn-small close-button">X</button>
        </div>
        <div class="party-select">
          <group-select v-for="group in groups" :key="group.id" :csrf="csrf" :groups="groups"
                        :group="group">
          </group-select>
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
    showGroupsSelect: Boolean,
    show: Boolean,
    csrf: String,
    groups: Object
  },

  mounted() {
    console.debug('open show groups');
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
