function createFacilityChart(id, color, legend, isUseChartTooltip)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _chartType: 'line',
        _isUseChartTooltip: isUseChartTooltip,
        _unit: 'kWh',
        _decimalPoint: 0,
        _color: color,
        _legend: legend,
        _callback: null,
        _create: function(labels, values, decimalPoint)
        {
            let self = this;
            let color = this._color;

            this._decimalPoint = decimalPoint;

            this._chart = new Chart(ctx, {
                type: self._chartType,
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: self._legend,
                            data: values,
                            yAxisID: 'line-y',
                            type: 'line',
                            backgroundColor: `rgba(${color}, 1)`,
                            borderColor: `rgba(${color}, 1)`,
                            borderWidth: 2,
                            fill: false,
                            pointRadius: 3,
                            pointBorderWidth: 4,
                        },
                    ],
                },
                options: {
                    scales: {
                        yAxes: [
                            {
                                id: 'line-y',
                                ticks: {
                                    beginAtZero:true,
                                    maxTicksLimit: 4,
                                },
                            },
                            {
                                id: 'line-y2',
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
                        xAxes: [{
                            gridLines: {
                                display: false
                            },
                            ticks:{
                                //fontColor:'#a3a3a3'
                            }
                        }]
                    },
                    legend: {
                        display: false,
                        position: 'top',
                        onClick: null,
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: function(evt, activeElements)
                    {
                    },
                    tooltips: {
                            enabled: self._isUseChartTooltip,
                            custom: function(tooltip)
                            {
                                if (!tooltip) {
                                    return;
                                }
                                tooltip.displayColors= false;
                            },
                            footerAlign: 'center',
                            callbacks:
                                {
                                    title: function (tooltipItem, data)
                                    {
                                        return '';
                                    },
                                    footer: function (tooltipItem, data)
                                    {
                                        return '';
                                    },
                                    label: function (tooltipItem, data)
                                    {
                                        const decimalPoint = self._decimalPoint;

                                        let index = tooltipItem.datasetIndex;
                                        let val = parseFloat(tooltipItem.value).toFixed(decimalPoint);
                                        let date = tooltipItem.label;
                                        let unit = self._unit;
                                        let label = self._legend;

                                        let tooltips = [label];

                                        tooltips.push(`${date} : ${module.utility.addComma(val)} ${unit}`);

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

            if (Array.isArray(dates) === false || Array.isArray(values) === false) {
                this._create([], [], 0);
                return;
            }

            //  그래프 생성
            this._create(dates, values, decimalPoint);
        },
        setUnit: function(unit)
        {
            this._unit = unit;
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