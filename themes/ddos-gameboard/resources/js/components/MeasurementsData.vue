<template>
    <div class="chartdata" v-if="show">
      <h6 class="flex_table_header"v-html="l('theme.lateststatus')"></h6>
      <div class="flex-table">
          <div v-for="measurement in measurementsdata" class="flex-cel" >
            {{ measurement.name }} :
            <span :class="measurement.status + 'color'">
              {{ measurement.latestrt }}ms
            </span>
          </div>
      </div>
    </div>
</template>

<script>
export default {
  props: {
    show: Boolean,
    rerender: Boolean,
    measurements: Array,
  },

  data:() => ({
    measurementsdata: Array,
  }),

  mounted() {
  },

  watch: {
    show: {
      handler() {
        this.measurementsdata = this.getAllMeasurements();
      }
    },
    rerender: {
      handler() {
        if (this.rerender === true) {
          console.debug('Watch rerender graphdata called');
          this.updateData();
          this.$emit('rerendered');
        }
      },
    },
  },


  show: {
    handler() {
      this.measurements = this.getAllMeasurements();
    },
  },

  methods: {
    updateData() {
      this.measurementsdata = this.getAllMeasurements();
    },

    getAllMeasurements() {
      var Measurements = [];
      Object.entries(this.measurements).forEach(function (measurement, idx) {
        measurement = measurement[1];
        Measurements.push(measurement);
      }.bind(this));


      return Measurements;
    }
  }
}
</script>

<style scoped>

</style>
