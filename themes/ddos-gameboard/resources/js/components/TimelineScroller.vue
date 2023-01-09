<template>
  <div id="timeline-scroller" class="timeline-scroller" :style="{top: offset}" v-if="scroll">
    <span>{{ now }}</span>
  </div>
</template>

<script>
export default {
  props: {
    start: Object,
    granularity: String,
    scroll: Boolean
  },

  data: () => ({
    defaultOffset: 14,
    clientHeight: 0,
    refreshOffset: true,
    timer: null,
    secondHeight: 0,
    now: '',
    show: false,
    scrollWarning: false,
    blockid: ''
  }),

  computed: {
    offset() {
      // refresh triger
      this.refreshOffset;
      // calculate middle of screen
      var doc = document.documentElement;
      this.clientHeight = doc.clientHeight;
      var mid = Math.floor(doc.clientHeight / 2) + this.defaultOffset + 'px';
      return mid;
    }
  },

  watch: {
    scroll: {
      handler() {
        if (this.scroll) {
          this.scrollWarning = false;
          this.calculateOffset();
          this.checkScroll();
        }
      }
    }
  },

  mounted() {
    this.timer = setInterval(this.calculateOffset.bind(this), 1000);
    this.scrollchecker = setInterval(this.checkScroll.bind(this), 5000);
  },

  methods: {

    calculateOffset() {
      var now = this.moment();
      this.now = now.format('HH:mm');
      // bereken verschil in seconden
      var diff = now.diff(this.start) / 1000;
      // laat scroller relatief op dagbasis werken
      var oneday = 60 * 60 * 24;
      if (diff > oneday) {
        diff = (diff % oneday);
      }
      if (diff > 0) {
        // onszelf tonen wanneer nog niet zichtbaar
        if (!this.show) this.show = true;
        // bepaal block met huidige tijd
        diff = diff / 60;   // minuten
        diff = parseInt(diff / this.granularity);   // granularity = block eenheid
        this.blockid = 'timeline-block-' + diff;
      }
    },

    checkScroll() {
      if (this.scroll) {
        var element = document.getElementById(this.blockid);
        if (element) {
          element.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'center'
          });
          this.scrollWarning = false;
        } else if (!this.scrollWarning) {
          this.scrollWarning = true;
          this.$emit('notify-user', 'warning', 'Not within timescope', 'Exercise start ' + this.moment(this.start).format('YYYY-MM-DD HH:mm') + ' out of scope of the time scroller');
        }
        var doc = document.documentElement;
        // detecteer scherm hoogte wijziging -> corrigeer timeline-scroller
        if (this.clientHeight != doc.clientHeight) {
          // trigger computed
          this.refreshOffset = !this.refreshOffset;
        }
      }
    }


  }
};
</script>
