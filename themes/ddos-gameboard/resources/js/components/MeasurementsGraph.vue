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
import {Chart, registerables} from 'chart.js';
import zoomPlugin from 'chartjs-plugin-zoom';
import 'chartjs-adapter-moment';
import 'moment';

Chart.register(...registerables);
Chart.register(zoomPlugin);

import mitt from 'mitt';


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
    created() {
        this.emitter = mitt();
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
                responsive: true,
                maintainAspectRatio: false,
                cubicInterpolationMode: 'monotone',
                plugins: {
                    zoom: {
                        limits: {
                            x: {
                                min: 'original', // Maintain original x-axis minimum
                                minRange: 60 * 1000 // Minimum zoom range for x-axis (e.g., 1 minute in milliseconds)
                            },
                            y: {
                                min: 0, // Prevent y-axis from going below 0
                                minRange: 10 // Minimum zoom range for y-axis (adjust as needed)
                            }
                        },
                        zoom: {
                            drag: {
                                enabled: true,
                                threshold: 60,
                            },
                            pinch: {
                                enabled: true,
                            },
                            wheel: {
                                enabled: true,
                                speed: 0.1,
                                sensitivity: 3,
                                threshold: 2,
                            },
                            mode: 'xy',
                            scaleMode: 'xy',
                        },
                    }
                },
                legend: {
                    position: 'top',
                    onClick: function (e, legendItem) {
                        const index = legendItem.datasetIndex;
                        const ci = this.chart;

                        ci.data.datasets.forEach(function (dataset, i) {
                            const meta = ci.getDatasetMeta(i);
                            if (i === index) {
                                meta.hidden = meta.hidden === null ? !ci.data.datasets[index].hidden : null;
                                dataset.hidden = dataset.hidden === false ? !dataset.hidden : false;
                            }
                        });
                        ci.update();
                    },
                },
                scales: {
                    x: {
                        type: 'time',
                        axis: 'x',
                        time: {
                            tooltipFormat: 'YYYY-MM-DD HH:mm',
                            displayFormats: {
                                minute: 'HH:mm',
                                hour: 'HH',
                                day: 'MMM D',
                                week: 'MMM D',
                                month: 'MMM YYYY',
                                quarter: '[Q]Q - YYYY',
                                year: 'YYYY',
                            },
                            minUnit: 'minute',
                            round: 'minute',
                            bounds: 'ticks',
                        },
                        ticks: {
                            min: '', // Ensure min is set to a default value
                            max: '', // Ensure max is set to a default value
                            stepSize: 5, // Adjusted to a more appropriate value
                        },
                        grid: {
                            display: true,
                            color: "#d76e6e"
                        },
                    },
                    y: {
                        position: 'right',
                        min: 0, // Set minimum value to 0
                        grid: {
                            display: true,
                            color: "#858585"
                        },
                        ticks: {
                            beginAtZero: true,
                            min: 0, // Prevent negative values on Y-axis
                            stepSize: 100,
                        },
                        title: {
                            display: true,
                            text: 'ResponseTime (ms)',
                        },
                    },
                },
                animation: {
                    duration: 125,
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
        this.emitter.on('rerenderGraphs', () => {
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

        this.emitter.on('scaled', () => {
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
        },
    }
    ,

    show: {
        handler() {
            this.activeMeasurements = this.getAllMeasurements();
            this.latestFetchedTimestamp = this.fetchlatestTimestamp();
        },
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

                // Ensure the x scale configuration exists
                if (!this[this.graphname].options.scales) {
                    this[this.graphname].options.scales = {};
                }

                if (!this[this.graphname].options.scales.x) {
                    this[this.graphname].options.scales.x = {};
                }

                // Initialize the time configuration if it doesn't exist
                if (!this[this.graphname].options.scales.x.time) {
                    this[this.graphname].options.scales.x.time = {};
                }

                // Log the options for debugging
                console.log(this[this.graphname]);

                // Accessing and modifying the x scale settings
                const xScale = this[this.graphname].options.scales.x;

                xScale.time.unit = 'minute'; // Example, set default unit
                xScale.ticks.StepSize = 5;    // Default step size

                switch (range) {
                    case 'latest':
                        min = undefined;  // Reset to default behavior
                        max = undefined;  // Reset to default behavior
                        delete xScale.ticks.stepSize;
                        break;
                    default:
                    case 'gametimes':
                        min = this.firsttime;
                        max = this.endtime;
                        break;
                    case 'latesthour':
                        min = this.moment(this.latestFetchedTimestamp).subtract(1, 'hour').format('YYYY-MM-DD HH:mm:ss');
                        xScale.ticks.StepSize = 5;
                        break;
                    case 'latesthalfhour':
                        min = this.moment(this.latestFetchedTimestamp).subtract(30, 'minutes').format('YYYY-MM-DD HH:mm:ss');
                        xScale.ticks.StepSize = 2;
                        break;
                    case 'latestquarter':
                        min = this.moment(this.latestFetchedTimestamp).subtract(15, 'minutes').format('YYYY-MM-DD HH:mm:ss');
                        xScale.ticks.StepSize = 1;
                        break;
                }

                xScale.min = min;
                xScale.max = max;
                this[this.graphname].update();

            }
        },


        /**
         * Don't set any setViewRange() while we zoom
         */
        checkIfZoomisEnabled() {
            this.disableViewRange = this[this.graphname].isZoomedOrPanned() === true;
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
        },

        resetChartJsZoom() {
            this.disableViewRange = false;
            this[this.graphname].resetZoom();
            this.setViewRange(this.range);
        },

        updateChart() {
            if (this[this.graphname]) {
                // Convert the measurements prop to something chartjs will be able to plot
                let convertedData = this.convertToGraphData(this.activeMeasurements);
                // Chop the converted measurements to data that in datasets
                var graphdata = this.generateDataSets(convertedData, true);
                this[this.graphname].data.datasets = graphdata.datasets;
                this[this.graphname].update(0);
            }
        },

        /**
         * This function is the main function of this component.
         * It will collect bundle several functions to generate the desired graph
         */
        initGraphs() {
            // Iterate through the object
            var canvas = document.getElementById("Graph" + this.graphname);
            var ctx = canvas.getContext('2d');

            Chart.register(zoomPlugin);

            this.setDefaults();

            // Convert the measurements prop to something chartjs will be able to plot
            let convertedData = this.convertToGraphData(this.measurements);
            // Chop the converted measurements to data that in datasets
            var graphdata = this.generateDataSets(convertedData);

            this.adjustGraphOptions();

            // Chart declaration:
            this[this.graphname] = new Chart(ctx, {
                type: 'line',
                data: graphdata,
                options: this.graphoptions,
                plugins: {
                    zoomPlugin: true
                }
            });
        },


        adjustGraphOptions() {
            if (this.graphoptions.scales) {
                const yScale = this.graphoptions.scales.y;
                if (yScale) {
                    // Adjust y-axis max value
                    if (this.graphmaxresponsetime) {
                        yScale.max = this.graphmaxresponsetime;
                    }
                    if (this.cliprt > 1) {
                        yScale.max = this.cliprt;
                    }
                    yScale.max = 2000;

                }
            }
        },


        initMeasurementsData() {
            for (let key in this.measurements) {
                let measurementdata = this.initMeasurementData(this.measurements[key]);
                this.measurementsdata[key] = measurementdata;
            }
            this.showMeasurementsData = true;
        },

        updateMeasurementsData() {
            for (let key in this.measurements) {
                let measurementdata = this.updateMeasurementData(this.measurements[key]);
                this.measurementsdata[key] = measurementdata;
            }
        },

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
        },


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
        },

        getLatestTimestamps(arrayTimestamps) {
            let latestTimestamp = arrayTimestamps.sort((a, b) => {
                return new Date(b) - new Date(a);
            })[0];

            return latestTimestamp;
        },

        getMeasurementStatus(rt, orangert, redrt) {
            if (rt < orangert) {
                return 'green';
            } else if (rt > redrt) {
                return 'red';
            } else {
                return 'orange';
            }
        },

        createArrayWithKeys(measurements) {
            let arrayWithKeys = {};

            for (const key in measurements) {
                let name = measurements[key].name;
                arrayWithKeys[name] = measurements[key].data;
            }

            return arrayWithKeys;
        },

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
        },

        setDefaults() {
            // Global Options:
            Chart.defaults.defaultFontColor = '#ffa144';
            Chart.defaults.defaultFontSize = 16;
            Chart.defaults.elements.line.borderColor = 'green';
            Chart.defaults.elements.point.backgroundColor = '#494949';
            this.setChartRelativeFont();

        },

        setChartRelativeFont() {
            try {
                let fontsize = Number(window.getComputedStyle(document.body).getPropertyValue('font-size').match(/\d+/)[0]);
                Chart.defaults.defaultFontSize = fontsize;
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
        },

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
