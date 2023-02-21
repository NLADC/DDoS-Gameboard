<template>
  <div v-if="show">
    <div class="flex_title_wrapper">
      <h2 class="chartTitle">{{ graphname }}</h2>
      <select name="LeaveType" @change="setViewRange($event.target.value)" class="form-control"
              :disabled="disableViewRange" :class="{'disabled' : disableViewRange}">
        <option value="latest" v-html="l('theme.latest')"></option>
        <option value="gametimes" v-html="l('theme.fullgametime')"></option>
        <option value="latesthour" v-html="l('theme.latesthour')"></option>
        <option value="latesthalfhour" v-html="l('theme.latesthalfhour')"></option>
        <option value="latestquarter" v-html="l('theme.latestquarter')"></option>
      </select>
      <button @click="resetChartJsZoom()" class="material-icons mi-help-outline">
        zoom_out
      </button>
    </div>
    <div class="graphwrapper">
      <div class="chart-wrapper">
        <canvas class="chart" :id='"Graph" + graphname'></canvas>
      </div>

      <measurements-data
          :show="showMeasurementsData"
          :measurements="measurementsdata"
          :rerender="reRender.data"
          @rerendered="reRender.data = false"
          :id="measurementsdata.id"
      ></measurements-data>
    </div>
  </div>
</template>

<script>
import {Chart} from 'chart.js';
import zoomPlugin from 'chartjs-plugin-zoom';
import 'chartjs-adapter-moment';
import 'moment';

export default {
  props: {
    show: Boolean,
    graphid: String,
    measurements: Array,
    graphname: String,
    firsttime: String,
    endtime: String,
    cliprt: Number,
    latesttimestamp: String,
  },

  data: () => ({
        // locally build for whoing more info.
        activemeasurements: {},
        targetinfo: [],
        measurementsdata: [],
        showMeasurementsData: false,
        reRender: {
          data: false,
        },
        latestFetchedTimestamp: '',
        range: 'latest',
        disableViewRange: false,
        // Notice the scaleLabel at the same level as Ticks
        graphoptions: {
          zoomed: false,
          legend: {
            position: 'top',
            onClick: function (e, legendItem) {
              var index = legendItem.datasetIndex;
              var ci = this.chart;

              ci.data.datasets.forEach(function (dataset, i) {
                var meta = ci.getDatasetMeta(i);
                if (i === index) {
                  meta.hidden = meta.hidden === null ? !ci.data.datasets[index].hidden : null;
                  dataset.hidden = dataset.hidden === false ? !dataset.hidden : false;
                }
              });
              ci.update();
            },
          },
          responsive: true,
          maintainAspectRatio: false,
          cubicInterpolationMode: 'monotone',
          plugins: {
            zoom: {
              // Container for zoom options
              zoom: {
                onZoomComplete(chart) {
                  let ci = chart
                  ci.chart.options.zoomed = true;
                },
                // Boolean to enable zooming
                enabled: true,

                // Enable drag-to-zoom behavior
                drag: true,

                // Zooming directions. Remove the appropriate direction to disable
                // Eg. 'y' would only allow zooming in the y direction
                // A function that is called as the user is zooming and returns the
                // available directions can also be used:
                //   mode: function({ chart }) {
                //     return 'xy';
                //   },
                mode: 'xy',

                rangeMin: {
                  // Format of min zoom range depends on scale type
                  x: null,
                  y: null
                },
                rangeMax: {
                  // Format of max zoom range depends on scale type
                  x: null,
                  y: null
                },

                // Speed of zoom via mouse wheel
                // (percentage of zoom on a wheel event)
                speed: 0.1,

                // Minimal zoom distance required before actually applying zoom
                threshold: 2,

                // On category scale, minimal zoom level before actually applying zoom
                sensitivity: 3,
              },

            },
          },
          // Default
          scales: {
            xAxes: [{
              type: 'time',
              time: {
                tooltipFormat: 'YYYY-MM-DD HH:mm',
                displayFormats: {
                  millisecond: 'HH:mm:ss.SSS',
                  second: 'HH:mm:ss',
                  minute: 'HH:mm',
                  hour: 'HH',
                  unit: 'hour',
                },
                minUnit: 'minute',
                round: 'minute',
                stepSize: 5,
                bounds: 'ticks'
              },
              ticks: {},
              gridLines: {
                display: true,
                color: "#d76e6e"
              },
            }],
            yAxes: [{
              position: 'right',
              gridLines: {
                display: true,
                color: "#858585"
              },
              ticks: {
                beginAtZero: true,
                min: 0,
                max: 200,
                stepSize: 100,
              },
              scaleLabel: {
                display: true,
                labelString: 'ResponseTime (ms)',
              },

            }]
          },

          animation: {
            duration: 500
          },

        },
        graphcolors: [
          "#4dc6ff",
          "#38ffc3",
          "#7800cb",
          "#4b50ff",
          "#ffffff",
          "#dc0ab4",
          "#00bfa0"],
      }
  ),

  mounted() {
    this.latestFetchedTimestamp = this.fetchlatestTimestamp();
    this.initMeasurementsData();
    this.initGraphs();
    this.initLegendmetas();
    this.checkIfZoomisEnabled();
    this.setViewRange(this.range);

    Event.$on('rerenderGraphs', () => {
          console.debug('Event rerenderGraphs called');
          this.activeMeasurements = this.getAllMeasurements();
          this.latestFetchedTimestamp = this.fetchlatestTimestamp();
          this.updateChart();
          this.updateMeasurementsData();
          this.rerenderAll();
          this.checkIfZoomisEnabled();
          this.setViewRange(this.range);
        }
    );

    Event.$on('scaled', () => {
          this.setChartRelativeFont();
          this[this.graphname].update(0);
        }
    );

  }
  ,


  watch: {
    show: {
      handler() {
        this.activeMeasurements = this.getAllMeasurements();
        this.latestFetchedTimestamp = this.fetchlatestTimestamp();
      }
    }
    ,
  }
  ,

  show: {
    handler() {
      this.activeMeasurements = this.getAllMeasurements();
      this.latestFetchedTimestamp = this.fetchlatestTimestamp();
    }
    ,
  }
  ,

  methods: {
    /**
     * A viewrange is between wich time the x-axis is to be set
     * @param range
     */
    setViewRange(range) {
      if (this.latestFetchedTimestamp && !this[this.graphname].options.zoomed) {
        this.range = range;
        let min = this.latestFetchedTimestamp;
        let max = this.latestFetchedTimestamp;
        this[this.graphname].options.scales.xAxes[0].time.stepSize = 5;
        switch (range) {
          case 'latest':
            min = this.firsttime;
            break;
          default:
          case 'gametimes':
            min = this.firsttime;
            max = this.endtime;
            break;
          case 'latesthour':
            min = this.moment(this.latestFetchedTimestamp).subtract(1, 'hour').format('YYYY-MM-DD HH:mm:ss');
            break;
          case 'latesthalfhour':
            min = this.moment(this.latestFetchedTimestamp).subtract(30, 'minutes').format('YYYY-MM-DD HH:mm:ss');
            this[this.graphname].options.scales.xAxes[0].time.stepSize = 2;
            break;
          case 'latestquarter':
            min = this.moment(this.latestFetchedTimestamp).subtract(15, 'minutes').format('YYYY-MM-DD HH:mm:ss');
            this[this.graphname].options.scales.xAxes[0].time.stepSize = 1;
            break;
        }
        if (this.latestFetchedTimestamp && min) {
          this[this.graphname].options.scales.xAxes[0].ticks['min'] = min;
          this[this.graphname].options.scales.xAxes[0].ticks['max'] = max;
          this[this.graphname].update();
        }
      }
    },

    /**
     * Don't set any setViewRange() while we zoom
     */
    checkIfZoomisEnabled() {
      this.disableViewRange = this[this.graphname].options.zoomed === true;
    },

    initLegendmetas() {
      this[this.graphname].data.datasets.forEach(function (dataset, i) {
        dataset['hidden'] = false;
      });
    },

    getAllMeasurements() {
      var Measurements = [];
      Object.entries(this.measurements).forEach(function (measurement, idx) {
        measurement = measurement[1];
        Measurements.push(measurement);
      }.bind(this));

      return Measurements;
    },

    fetchlatestTimestamp() {
      return this.latesttimestamp;
    },

    rerenderAll() {
      this.reRender.data = true;
    }
    ,

    resetChartJsZoom() {
      this[this.graphname].options.zoomed = false;
      this.disableViewRange = false;
      this[this.graphname].resetZoom();
    }
    ,

    updateChart() {
      if (this[this.graphname]) {
        // Convert the measurements prop to something chartjs will be able to plot
        let convertedData = this.convertToGraphData(this.activeMeasurements);
        // Chop the converted measurements to data that in datasets
        var graphdata = this.generateDataSets(convertedData, true);
        this[this.graphname].data.datasets = graphdata.datasets;
        this[this.graphname].update(0);
      }
    }
    ,

    /**
     * This function is the main function of this component.
     * It will collect bundle several functions to generate the desired graph
     */
    initGraphs() {
      // Iterate through the object
      var canvas = document.getElementById("Graph" + this.graphname);
      var ctx = canvas.getContext('2d');

      Chart.plugins.register(zoomPlugin);

      this.setDefaults();

      // Convert the measurements prop to something chartjs will be able to plot
      let convertedData = this.convertToGraphData(this.measurements);
      // Chop the converted measurements to data that in datasets
      var graphdata = this.generateDataSets(convertedData);

      this.adjustGrapOptions();

      // Chart declaration:
      this[this.graphname] = new Chart(ctx, {
        type: 'line',
        data: graphdata,
        options: this.graphoptions,
        plugins: {
          zoomPlugin: true
        }
      });
    }
    ,


    adjustGrapOptions() {
      try {
        if (this.graphmaxresponsetime) {
          this.graphoptions.scales.yAxes[0].ticks['max'] = this.graphmaxresponsetime;
        }
        if (this.cliprt > 1) {
          this.graphoptions.scales.yAxes[0].ticks['max'] = this.cliprt;
        }
        if (this.firsttime && this.endtime) {
          this.graphoptions.scales.xAxes[0].ticks['min'] = this.firsttime;
          this.graphoptions.scales.xAxes[0].ticks['max'] = this.endtime;
        } else {
          this.graphoptions.scales.yAxes[0].ticks['max'] = 2000;
          this.graphoptions.scales.xAxes[0].ticks['min'] = '';
          this.graphoptions.scales.xAxes[0].ticks['max'] = '';
        }


      } catch
          (error) {
        console.error("Something went wrong on setting pre definied settings on the xAxes an yAxes" + error);
      }
    }
    ,

    initMeasurementsData() {
      for (let key in this.measurements) {
        let measurementdata = this.initMeasurementData(this.measurements[key]);
        this.measurementsdata[key] = measurementdata;
      }
      this.showMeasurementsData = true;
    }
    ,

    updateMeasurementsData() {
      for (let key in this.measurements) {
        let measurementdata = this.updateMeasurementData(this.measurements[key]);
        this.measurementsdata[key] = measurementdata;
      }
    }
    ,

    updateMeasurementData(measurement) {
      let measurementsdata = {
        name: measurement.name,
        status: '',
        latestrt: 0
      }
      let timestamps = [];
      for (let key in measurement.data) {
        timestamps.push(measurement.data[key].x);
      }

      let latesttimestamp = timestamps[timestamps.length - 1];

      for (let key in measurement.data) {
        if (measurement.data[key].x === latesttimestamp) {
          if (measurement.data[key].y <= 0 || measurement.data[key].responsetime <= 0) {
            measurement.data[key].y = 0;
          } else {
            if (measurement.data[key].y) {
              measurementsdata.latestrt = measurement.data[key].y;
            }
            if (measurement.data[key].responsetime) {
              measurementsdata.latestrt = measurement.data[key].responsetime;
            }
          }
        }
      }

      measurementsdata.status = this.getMeasurementStatus(
          measurementsdata.latestrt,
          measurement.thresholds.orange,
          measurement.thresholds.red
      );

      return measurementsdata;
    }
    ,


    initMeasurementData(measurement) {
      let measurementsdata = {
        name: measurement.name,
        status: '',
        latestrt: 0
      }
      let timestamps = [];
      for (let key in measurement.data) {
        timestamps.push(measurement.data[key].timestamp);
      }

      // The latest in array is the latest because the API supplies the measurements sorted on the timestamps
      let latesttimestamp = timestamps[timestamps.length - 1];

      for (let key in measurement.data) {
        if (measurement.data[key].timestamp === latesttimestamp) {
          if (measurement.data[key].responsetime <= 0) {
            measurementsdata.latestrt = "faulty";
          } else {
            measurementsdata.latestrt = measurement.data[key].responsetime;
          }
        }
      }

      measurementsdata.status = this.getMeasurementStatus(
          measurementsdata.latestrt,
          measurement.thresholds.orange,
          measurement.thresholds.red
      );

      return measurementsdata;
    }
    ,

    getLatestTimestamps(arrayTimestamps) {
      let latestTimestamp = arrayTimestamps.sort((a, b) => {
        return new Date(b) - new Date(a);
      })[0];

      return latestTimestamp;
    }
    ,

    getMeasurementStatus(rt, orangert, redrt) {
      if (rt < orangert) {
        return 'green';
      } else if (rt > redrt) {
        return 'red';
      } else {
        return 'orange';
      }
    }
    ,

    createArrayWithKeys(measurements) {
      let arrayWithKeys = {};

      for (const key in measurements) {
        let name = measurements[key].name;
        arrayWithKeys[name] = measurements[key].data;
      }

      return arrayWithKeys;
    }
    ,

    convertToGraphData(measurements) {
      var GraphData = this.createArrayWithKeys(measurements);

      for (let key in GraphData) {
        let innerObj = GraphData[key];
        for (let innerKey in innerObj) {
          if (innerObj[innerKey].timestamp) {
            innerObj[innerKey]["x"] = innerObj[innerKey].timestamp;
            delete innerObj[innerKey].timestamp;
          }
          if (innerObj[innerKey].responsetime) {
            innerObj[innerKey]["y"] = innerObj[innerKey].responsetime;
            delete innerObj[innerKey].responsetime
          }
        }
      }
      return GraphData;
    }
    ,

    setDefaults() {
      // Global Options:
      Chart.defaults.global.defaultFontColor = '#ffa144';
      Chart.defaults.global.defaultFontSize = 16;
      Chart.defaults.global.elements.line.borderColor = 'green';
      Chart.defaults.global.elements.point.backgroundColor = '#494949';
      this.setChartRelativeFont();


    }
    ,

    setChartRelativeFont() {
      try {
        let fontsize = Number(window.getComputedStyle(document.body).getPropertyValue('font-size').match(/\d+/)[0]);
        Chart.defaults.global.defaultFontSize = fontsize;
      } catch (error) {
        console.log("cannot set relative fontsize, legend text will not be responsive when zooming out: " + error.message)
      }
    },

    generateDataSets(data, isupdating = false) {
      let datasets = [];
      let info = {};
      let hidden = false;
      // Index for looping trough colors array for having a nice variating pallete per graphline
      let i = 0;
      // Create a dataset per
      for (let keyname in data) {
        let hidden = false;
        if (isupdating) {
          for (let datasetkey in this[this.graphname].data.datasets) {
            if (this[this.graphname].data.datasets[datasetkey].label === keyname) {
              if (this[this.graphname].data.datasets[datasetkey].hidden === true) {
                hidden = true;
              } else {
                hidden = false;
              }
            }
          }
        }
        datasets.push({
          label: keyname,
          data: Object.values(data[keyname]),
          hidden: hidden,
          borderColor: this.graphcolors[i],
          spanGaps: true
        })
        info.color = this.graphcolors[i];
        info.name = keyname;
        info.data = data;
        this.pushTargetinfo(info, i);
        i++
      }

      return {datasets};
    }
    ,

    pushTargetinfo(targetinfo, i) {
      let data = {};
      data.name = targetinfo.targetinfo;
      data.color = targetinfo.color;
      data.data = targetinfo.data;

      this.targetinfo[i] = data;

    }
  }
}
</script>


<!---->
<style scoped>

</style>
