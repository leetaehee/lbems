/**
 stack bar 그래프 (전체)
 */
function createPaperBarStackAllChart(id, labels, color1, color2, color3, color4, isUseLegend)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _color1: color1,
        _color2: color2,
        _color3: color3,
        _color4: color4,
        _labels: labels,
        _isUseLegend: isUseLegend,
        _chartType: 'bar',
        _decimalPoint: 0,
        _dates: [],
        _tooltips1: [],
        _tooltips2: [],
        _units:['원', 'kWh'],
        _callback: null,
        _create: function(dates, values, decimalPoint)
        {
            let self = this;

            let color1 = this._color1;
            let color2 = this._color2;
            let color3 = this._color3;
            let color4 = this._color4;
            let isUseLegend = this._isUseLegend;

            this._decimalPoint = decimalPoint;

            this._chart = new Chart(ctx, {
                type: self._chartType,
                data: {
                    labels: dates,
                    datasets: [
                        {
                            // 1층
                            label : self._labels[0],
                            data : values[0],
                            yAxisID : "line-y",
                            backgroundColor :`rgba(${color1}, 1)`,
                            borderColor : `rgba(${color1}, 1)`,
                            borderWidth : 3,
                            fill : false,
                            pointRadius : 0,
                            pointBorderWidth : 0,
                        },
                        {
                            // 2층
                            label : self._labels[1],
                            data : values[1],
                            yAxisID : "line-y",
                            backgroundColor : `rgba(${color2}, 1)`,
                            borderColor : `rgba(${color2}, 1)`,
                            borderWidth : 3,
                            fill : false,
                            pointRadius : 0,
                            pointBorderWidth : 0,
                        },
                        {
                            // 3층
                            label : self._labels[2],
                            data : values[2],
                            yAxisID : "line-y",
                            backgroundColor : `rgba(${color3}, 1)`,
                            borderColor : `rgba(${color3}, 1)`,
                            borderWidth : 3,
                            fill : false,
                            pointRadius : 0,
                            pointBorderWidth : 0,
                        },
                        {
                            // 옥탑
                            label : self._labels[3],
                            data : values[3],
                            yAxisID : "line-y",
                            backgroundColor : `rgba(${color4}, 1)`,
                            borderColor : `rgba(${color4}, 1)`,
                            borderWidth : 3,
                            fill : false,
                            pointRadius : 0,
                            pointBorderWidth : 0,
                        },
                    ]
                },
                options: {
                    scales: {
                        yAxes: [
                            {
                                id: "line-y",
                                stacked: true,
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
                            }
                        ],
                        xAxes: [
                            {
                                stacked: true,
                                barThickness : 25,
                                gridLines: {
                                    display: false
                                },
                                ticks:{
                                    //fontColor:'#a3a3a3'
                                }
                            }
                        ]
                    },
                    legend:
                        {
                            display: isUseLegend,
                            position: 'top',
                            onClick: null,
                        },
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: function(evt, activeElements)
                    {
                    },
                    tooltips:
                    {
                        custom: function(tooltip)
                        {
                            if (!tooltip) {
                                return;
                            }
                            tooltip.displayColors= false;
                        },
                        footerAlign: "center",
                        callbacks:
                        {
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

                                let index = tooltipItem.datasetIndex;
                                let value = parseFloat(tooltipItem.value).toFixed(decimalPoint);
                                let date = tooltipItem.label;
                                let unit = self._units[1];
                                let label = self._labels[index];

                                let tooltips = [label];

                                tooltips.push(`${date} :  ${module.utility.addComma(value)} ${unit}`);

                                return tooltips;
                            },
                        }
                    }
                }
            });
        },
        update: function(dates, values, decimalPoint)
        {
            if (this._chart != null) {
                this.clear.call(this);
            }

            if (Array.isArray(dates) == false || Array.isArray(values) == false) {
                this._create([], [], 0);
                return;
            }

            this._create(dates, values, decimalPoint);
        },
        clear: function()
        {
            if (this._chart == null) {
                return;
            }

            this._chart.destroy();
            this._chart = null;
        },
    };

    return controller;
}