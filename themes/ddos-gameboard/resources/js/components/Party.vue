<template>
  <div class="party" :class="{'global': global, 'hidden': !show}">
    <div class="party-header">
      <h2 class="header text-xs lg:text-xl" v-if="!global">
        {{ party.name }}
      </h2>
    </div>
    <div v-if="show">
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    party: Object,
    global: Boolean,
    show: Boolean,
    csrf: String
  }

}
</script>

<style scoped>

</style>
