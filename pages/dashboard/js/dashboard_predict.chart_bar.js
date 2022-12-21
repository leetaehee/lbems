function createPredictChart(id, colors, labels)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _colors: colors,
        _labels: labels,
        _chartType: 'bar',
        _decimalPoint: 0,
        _dates: [],
        _unit: 'kWh',
        _callback: null,
        _create: function(x, y, unit, decimalPoint)
        {
            let self = this;

            self._unit = unit;
            self._decimalPoint = decimalPoint;

            let labels = x;
            let colors = self._colors;

            this._chart = new Chart(ctx, {
                type: self._chartType,
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: labels,
                            data : y,
                            yAxisID : "line-y",
                            backgroundColor : [`rgba(${colors[0]}, 1)`,  `rgba(${colors[1]}, 1)`],
                            borderColor : [`rgba(${colors[0]}, 1)`,  `rgba(${colors[1]}, 1)`],
                            borderWidth : 2,
                            fill : false,
                            pointRadius : 0,
                            pointBorderWidth : 0,
                            pointHoverRadius : 0,
                        },
                    ]
                },
                options: {
                    hover: false,
                    scales: {
                        yAxes: [
                            {
                                id: "line-y",
                                ticks: {
                                    beginAtZero:true,
                                    maxTicksLimit: 4,
                                }
                            },
                        ],
                        xAxes: [
                            {
                                gridLines: {
                                    display: false
                                },
                                ticks:{
                                }
                            }
                        ]
                    },
                    legend: {
                        display: false,
                        position: 'bottom',
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: function(evt, activeElements)
                    {
                    },
                    tooltips: {
                        custom: function(tooltip)
                        {
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

                                let unit = self._unit;
                                let label = tooltipItem['xLabel'];
                                let value = parseFloat(tooltipItem['value']);
                                let fmtValue = module.utility.addComma(value.toFixed(decimalPoint));

                                let tooltips = new Array();

                                tooltips.push(`${label}: ${fmtValue} ${unit}`);

                                return tooltips;
                            },
                            labelTextColor: function(tooltipItem, chart)
                            {
                                return '#ffffff';
                            }
                        }
                    }
                }
            });
        },
        update: function(x, y, unit, decimalPoint)
        {
            if (this._chart != null) {
                this.clear.call(this);
            }

            if (Array.isArray(x) === false || Array.isArray(y) === false || unit === undefined) {
                this._create([], [], '', 0);
                return;
            }

            this._create(x, y, unit, decimalPoint);
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
