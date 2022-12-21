function createLineDoubleYChart(id, color, color2, legend, legend2)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _color: color,
        _color2: color2,
        _unit: 'kwh',
        _legend: legend,
        _legend2: legend2,
        _chartType: 'line',
        callback: null,
        _create: function(x, y, y2)
        {
            let self = this;
            let color = this._color;
            let color2 = this._color2;

            this._chart = new Chart(ctx, {
                type: self._chartType,
                data: {
                    labels: x,
                    datasets: [
                        {
                            label: self._legend,
                            data: y,
                            yAxesGroup: 'humidity',
                            yAxisID: 'y-axis-1',
                            backgroundColor: `rgba(${color}, 0.8)`,
                            borderColor: `rgba(${color}, 0.8)`,
                            borderWidth: 3,
                            fill: true,
                            pointRadius: 2,
                            pointBorderWidth: 4,
                        },
                        {
                            label: self._legend2,
                            data: y2,
                            yAxesGroup: 'temperature',
                            yAxisID: 'y-axis-2',
                            backgroundColor: `rgba(${color2}, 0.8)`,
                            borderColor: `rgba(${color2}, 0.8)`,
                            borderWidth: 3,
                            fill: true,
                            pointRadius: 2,
                            pointBorderWidth: 4,
                        },

                    ]
                },
                options: {
                    scales: {
                        yAxes: [
                            {
                                name: 'humidity',
                                id: 'y-axis-1',
                                type: 'linear',
                                position: 'left',
                                scalePositionLeft: true,
                                gridLines: {
                                    display: true,
                                },
                                /*
                                scaleLabel: {
                                    display: true,
                                    labelString: '습도 %',
                                },
                                 */
                            },
                            {
                                name: 'temperature',
                                id: 'y-axis-2',
                                type: 'linear',
                                position: 'right',
                                scalePositionLeft: false,
                                gridLines: {
                                    display: false
                                },
                                /*
                                scaleLabel: {
                                    display: true,
                                    labelString: '온도 ℃',
                                },
                                 */
                            },
                        ],
                        xAxes: [
                            {
                                /*
                                gridLines: {
                                    display: false
                                },
                                 */
                            }
                        ]
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data)
                            {
                                let dataIndex = tooltipItem.datasetIndex;
                                let val = parseInt(tooltipItem.value);
                                let message  = '';

                                if (dataIndex === 0) {
                                    // 습도
                                    message = "습도: " + val + "%";
                                }

                                if (dataIndex === 1) {
                                    // 온도
                                    message = "온도: " + val  + "℃";
                                }

                                return message
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'bottom',
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        },
        update: function(x, y, y2)
        {
            let xTemp = x;
            let yTemp = y;
            let yTemp2 = y2;

            if(Array.isArray(xTemp) == false) {
                xTemp = [];
            }

            if(Array.isArray(yTemp) == false) {
                yTemp = [];
            }

            if(Array.isArray(yTemp2) == false) {
                yTemp2 = [];
            }

            if(this._chart == null) {
                this._create(xTemp, yTemp, yTemp2);
                return;
            }

            //this.setUnit(unit);

            this._chart.data.labels = xTemp;
            this._chart.data.datasets[0].data = yTemp;
            this._chart.data.datasets[1].data = yTemp2;

            this._chart.update();
        },
        clear: function()
        {
            if (this._chart == null) {
                return;
            }

            this._chart.destroy();
            this._chart = null;
        },
        setColor: function(color)
        {
            this._color = color;
        },
        setUnit: function(unit)
        {
            this._unit = unit;
        },
    };

    return controller;
}