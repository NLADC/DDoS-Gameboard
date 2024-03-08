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
