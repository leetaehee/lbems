function createAnalysisLineChart(id, color, color2, legend, legend2)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _color: color,
        _color1: color2,
        _legend: legend,
        _legend1: legend2,
        _chartType: 'line',
        _decimalPoint: 0,
        _unit: 'kwh',
        callback: null,
        _create: function(x, y, y2, unit, decimalPoint, headers = [])
        {
            let self = this;
            let color = this._color;
            let color1 = this._color1;

            this._decimalPoint = decimalPoint;

            this._chart = new Chart(ctx, {
                type: self._chartType,
                data: {
                    labels: x,
                    datasets: [
                        {
                            label : self._legend,
                            data : y,
                            backgroundColor : `rgba(${color}, 0.8)`,
                            borderColor : `rgba(${color}, 1)`,
                            borderWidth : 2,
                            fill: true,
                            pointRadius: 2,
                            pointBorderWidth : 1,
                        },
                        {
                            label : self._legend2,
                            data : y2,
                            backgroundColor : `rgba(${color1},0.8)`,
                            borderColor : `rgba(${color1}, 0.1)`,
                            borderWidth : 2,
                            fill : true,
                            pointRadius : 2,
                            pointBorderWidth : 1,
                        },
                    ]
                },
                options: {
                    scales: {
                        yAxes: [
                            {
                                ticks: {
                                    beginAtZero:true,
                                    maxTicksLimit: 4,
                                },
                                /*
                                scaleLabel: {
                                    display: true,
                                    labelString: this._unit,
                                },
                                 */
                            }
                        ],
                        xAxes: [
                            {
                                gridLines: {
                                    display: false
                                },
                                ticks:{
                                    //fontColor:'#fff'
                                }
                            }
                        ]
                    },
                    onClick: function(evt, activeElements)
                    {
                    },
                    onHover: function(evt, activeElements)
                    {
                    },
                    tooltips: {
                        custom: function(tooltip)
                        {
                            if (!tooltip) {
                                return;
                            }

                            let chart = this;

                            tooltip.displayColors= false;
                        },
                        footerAlign: "center",
                        callbacks: {
                            title: function(tooltipItem, data)
                            {
                                return "";
                            },
                            footer: function(tooltipItem, data)
                            {
                                return "";
                            },
                            label: function(tooltipItem, data)
                            {
                                const decimalPoint = self._decimalPoint;

                                let datasetIndex = tooltipItem.datasetIndex;
                                let label = tooltipItem.label;
                                let value = parseFloat(tooltipItem.value).toFixed(decimalPoint);
                                let tooltips = new Array();

                                let header;
                                if (Object.keys(headers).length > 1) {
                                    header = headers[datasetIndex];
                                } else {
                                    header = headers[0];
                                }

                                if (header !== undefined) {
                                    tooltips.push(`${header}: `);
                                }

                                tooltips.push(`${label}`);
                                tooltips.push(`${module.utility.addComma(value)} ${unit}`);

                                return tooltips;
                            },
                            labelTextColor: function(tooltipItem, chart)
                            {
                                return '#ffffff';
                            },
                        },
                    },
                    legend: {
                        display: false,
                        position: 'bottom',
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        },
        update: function(x, y, y2, unit, headers, decimalPoint)
        {
            let xTemp  = x;
            let yTemp  = y;
            let yTemp2 = y2;

            if (Array.isArray(xTemp) == false) {
                xTemp = [];
            }

            if (Array.isArray(yTemp) == false) {
                yTemp = [];
            }

            if (Array.isArray(yTemp2) == false) {
                yTemp2 = [];
            }

            if (this._chart == null) {
                this._create(xTemp, yTemp, yTemp2, unit, decimalPoint, headers);
                return;
            }

            this._chart.data.labels = xTemp;
            this._chart.data.datasets[0].data = yTemp;
            this._chart.data.datasets[1].data = yTemp2;

            this.setUnit(unit);
            this.setDecimalPoint(decimalPoint);
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
        setUnit: function(unit)
        {
            this._unit = unit;
        },
        setDecimalPoint: function(decimalPoint)
        {
            this._decimalPoint = decimalPoint;
        },
    };

    return controller;
}
