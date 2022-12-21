function createFacilityChart(id, isUseChartTooltip)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _chartType: 'line',
        _isUseChartTooltip: isUseChartTooltip,
        _unit: 'kWh',
        _decimalPoint: 0,
        _datasets: [],
        _keys: [],
        _labels: [],
        _colors: [],
        _values: [],
        _dates: [],
        _callback: null,
        _create: function(dates)
        {
            let self = this;

            this._chart = new Chart(ctx, {
                type: self._chartType,
                data: {
                    labels: dates,
                    datasets: self._datasets,
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
                                    labelString: this._unit,
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
                    tooltips:
                    {
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
                                let label = self._labels[index];

                                let tooltips = [label];

                                tooltips.push(`${date} : ${module.utility.addComma(val)} ${unit}`);

                                return tooltips;
                            },
                        }
                    }
                }
            });
        },
        update: function(dates, keys, values, labels, colors, decimalPoint)
        {
            if (this._chart != null) {
                this.clear.call(this);
            }

            if (Array.isArray(dates) === false) {
                this._create([]);
                return;
            }

            // 값 설정
            this.setData(keys, values, labels, colors, decimalPoint);

            //  그래프 생성
            this._create(dates);
        },
        setData: function(keys, values, labels, colors, decimalPoint)
        {
            if (keys.length === 0 || values.length === 0 || labels.length === 0 || colors.length === 0) {
                return;
            }

            let labelLength = labels.length;
            let datasets = [];

            for (let i = 0; i < labelLength; i++) {
                let obj = {
                    label: labels[i],
                    data: values[i],
                    yAxisID : "line-y",
                    backgroundColor: `rgba(${colors[i]})`,
                    borderColor: `rgba(${colors[i]})`,
                    borderWidth: 3,
                    fill : false,
                    pointRadius : 2,
                    pointBorderWidth : 4,
                };

                datasets.push(obj);
            }

            this._dates = keys;
            this._keys = keys;
            this._values = values;
            this._labels = labels;
            this._colors = colors;
            this._decimalPoint = decimalPoint

            this._datasets = datasets;
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