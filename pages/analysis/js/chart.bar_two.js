function createBarTwoChart(id, color, color2, legend, legend2)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _color: color,
        _color2: color2,
        _legend: legend,
        _legend2: legend2,
        _chartType: 'bar',
        _decimalPoint: 0,
        _dates: [],
        _headers: [],
        _tooltips1: [],
        _tooltips2: [],
        _units:['원','kWh'],
        _callback: null,
        _create: function(x, y, y2, tooltips1, tooltips2, headers, decimalPoint)
        {
            let self = this;
            let color = this._color;
            let color2 = this._color2;

            self._decimalPoint = decimalPoint
            self._tooltips1 = tooltips1;
            self._tooltips2 = tooltips2;
            self._headers = headers;

            this._chart = new Chart(ctx, {
                type: self._chartType,
                data: {
                    labels: x,
                    datasets: [
                        {
                            label: self._legend,
                            data: y,
                            yAxisID: "line-y",
                            backgroundColor: `rgba(${color}, 1)`,
                            borderColor: `rgba(${color}, 1)`,
                            borderWidth: 3,
                            fill: false,
                            pointRadius: 2,
                            pointBorderWidth: 4,
                        },
                        {
                            label: self._legend2,
                            data: y2,
                            yAxisID: "line-y",
                            backgroundColor: `rgba(${color2}, 1)`,
                            borderColor: `rgba(${color2}, 1)`,
                            borderWidth: 3,
                            fill: false,
                            pointRadius: 2,
                            pointBorderWidth: 4,
                        },
                    ]
                },
                options: {
                    scales: {
                        yAxes: [
                            {
                                id: "line-y",
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
                            {
                                id: "line-y2",
                                position: 'right',
                                display: false,
                                gridLines: {
                                    display: false
                                },
                                ticks: {
                                    beginAtZero:true,
                                    maxTicksLimit: 4
                                }
                            }],
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

                        self._callback(params);
                    },
                    tooltips: {
                        custom: function(tooltip)
                        {
                            if (!tooltip) {
                                return;
                            }

                            let chart = this;

                            tooltip.borderColor='rgba(159, 140, 199, 60)';
                            tooltip.borderWidth = 2;
                            tooltip.cornerRadius = 5;
                            tooltip.titleFontSize= 16;
                            //tooltip.titleFontColor= '#e05959';
                            //tooltip.bodyFontColor= '#6ad46a';
                            tooltip.footerFontColor= chart._data.tooltipColor;
                            tooltip.bodyFontSize= 12;
                            tooltip.displayColors= false;
                            tooltip.backgroundColor= '#ffffff';
                            tooltip._footerAlign = 'center';
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

                                let value = parseFloat(tooltipItem.value);
                                let formatValue = module.utility.addComma(value.toFixed(decimalPoint));
                                let date = tooltipItem.label;
                                let labelType = data.datasets[1].label;
                                let unit = labelType == '사용요금' ? self._units[0] : self._units[1];
                                let index = tooltipItem.datasetIndex;

                                let tooltip = [];
                                tooltip.push(`${self._headers[index]}`);
                                tooltip.push(`${date}`);
                                tooltip.push(`${formatValue} ${unit}`);

                                return tooltip;
                            },
                            labelTextColor: function(tooltipItem, chart)
                            {
                                return '#9f8cc7';
                            }
                        }
                    }
                }
            });
        },
        update: function(x, y, y2, tooltips1, tooltips2, headers, decimalPoint)
        {
            if (this._chart != null) {
                this.clear.call(this);
            }

            if (Array.isArray(x) == false || Array.isArray(y) == false || Array.isArray(y2) == false) {
                this._create([], [], [], [], [], [], decimalPoint);
                return;
            }

            this._create(x, y, y2, tooltips1, tooltips2, headers, decimalPoint);
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
            this._units[1] = unit;
        },
        getData: function()
        {
            let data = [];

            if (this._chart == null) {
                return data;
            }

            let labels = this._chart.data.labels;

            if (this._chart.data.datasets.length < 2) {
                return data;
            }

            let data1  = this._chart.data.datasets[0].data;
            let data2  = this._chart.data.datasets[1].data;

            if (labels === undefined || data1 === undefined || data2 === undefined) {
                return data;
            }

            if (labels.length != data1.length || data1.length != data2.length) {
                return data;
            }

            let len = labels.length;

            for (let i = 0; i < len; i++) {
                let row = [labels[i], data1[i], data2[i]]
                data.push(row);
            }

            return data;
        }
    };

    return controller;
}