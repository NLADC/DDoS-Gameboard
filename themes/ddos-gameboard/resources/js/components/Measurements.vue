<template>
  <div>
    <measurements-graph class="measurements_graph" v-if="show" :show="showMeasurmentsgraphs"
                        v-for="(measurements, key) in activeMeasurements" :key="measurements.name"
                        :measurements="measurements.data" :id='"id" + measurements.name' :graphid="measurements.graphid"
                        :graphname="measurements.name" :cliprt="measurements.cliprt"
                        :firsttime="firsttime" :endtime="endtime" :latesttimestamp="activelatesttimestamp"
    >
    </measurements-graph>
  </div>
</template>

<script>

export default {
  emits: ['rerendered', 'rerenderGraphs'],

  props: {
    show: Boolean,
    allmeasurements: Array,
    rerender: Boolean,
    firsttime: String,
    endtime: String,
    latesttimestamp: String,
  },

  data: () => ({
    showMeasurmentsgraphs: false,
    activeMeasurements: {},
    activelatesttimestamp: '',
    localindex: 0,
  }),

  computed: {
      groupGraphid() {
          return 'groupgraph' + this.measurements.id;
      },
  },

  watch: {
    rerender: {
      handler() {
        if (this.rerender === true) {
          console.debug('Watch allmeasurements rerender called')
          this.updateGraphsData();
          Event.$emit('rerenderGraphs');
          this.$emit('rerendered');
        }
      },
    },
    show: {
      handler() {
        this.activeMeasurements = this.getAllMeasurements();
        this.activelatesttimestamp = this.latesttimestamp;
      }
    },
  },

  show: {
    handler() {
      this.activeMeasurements = this.getAllMeasurements();
      this.activelatesttimestamp = this.latesttimestamp;
    },
  },

  mounted() {
    this.showMeasurmentsgraphs = true;
    this.activelatesttimestamp = this.latesttimestamp;
  },

  methods: {
    logthis(debug) {
      console.log(debug);
      return debug;
    },

    updateGraphsData: function () {
      this.activeMeasurements = this.getAllMeasurements();
      this.activelatesttimestamp = this.latesttimestamp;
    },


    getAllMeasurements() {
      var Measurements = [];
      Object.entries(this.allmeasurements).forEach(function (measurement, idx) {
        let measurementname = measurement[0];
        Measurements[measurementname] = measurement[1];

      }.bind(this));
      return Measurements;
    },
  }
}
</script>
