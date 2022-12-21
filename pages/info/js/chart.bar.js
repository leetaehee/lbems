function createBarChart(id, color, legend, legend2, mode)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _color: color,
        _legend: legend,
        _legend2: legend2,
        _chartType: 'bar',
        _mode: mode,
        _dates: [],
        _tooltips1: [],
        _statusColor: [],
        _decimalPoint: 0,
        _units:['원', 'kWh'],
        _callback: null,
        _create: function(dates, labels, values, decimalPoint)
        {
            let self = this;

            self._dates = dates;
            self._decimalPoint = decimalPoint;

            this._chart = new Chart(ctx, {
                type: self._chartType,
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label : self._legend,
                            data : values,
                            yAxisID : 'line-y',
                            backgroundColor : `rgba(${color}, 1)`,
                            borderColor : `rgba(${color}, 1)`,
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
                                id: 'line-y',
                                ticks: {
                                    beginAtZero:true,
                                    maxTicksLimit: 4,
                                },
                                /*
                                scaleLabel: {
                                    display: true,
                                    labelString: this._units[1],
                                },
                                 */
                            },
                        ],
                        xAxes: [
                            {
                                gridLines: {
                                    display: false
                                },
                                ticks:{
                                    //fontColor:'#a3a3a3'
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
                        if (self._callback instanceof Function == false) {
                            return;
                        }

                        let t = self._chart.getElementAtEvent(evt);

                        if (t.length <= 0 || t[0]._index === undefined) {
                            return;
                        }

                        let index = t[0]._index;
                        let date = self._dates[index];
                        let mode = parseInt(self._mode) + 1;

                        let params = {
                            mode : mode,
                            date : date
                        };

                        self._callback(params);
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
                                return '';
                            },
                            footer: function(tooltipItem, data)
                            {
                                return '';
                            },
                            label: function(tooltipItem, data)
                            {
                                const decimalPoint = self._decimalPoint;

                                let val = parseFloat(tooltipItem.value).toFixed(decimalPoint);
                                let formatVal = module.utility.addComma(val);

                                let date = tooltipItem.label;
                                let unit = self._units[1];

                                let tooltips = [];

                                tooltips.push(`${date}`);
                                tooltips.push(`사용량: ${formatVal} ${unit}`);

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
        update: function(dates, labels, values, decimalPoint)
        {
            if (this._chart != null) {
                this.clear.call(this);
            }

            if (Array.isArray(dates) === false || Array.isArray(labels) === false || Array.isArray(values) === false) {
                this._create([], [], [],  0);
                return;
            }

            this._create(dates, labels, values, decimalPoint);
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
            this._units[1]= unit;
        },
    };

    return controller;
}
