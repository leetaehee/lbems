function createFloorBarStackChart(id)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _chartType: 'bar',
        _dates: [],
        _datasets: [],
        _tooltips: [],
        _unit: 'kWh',
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
                                stacked: true,
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
                                stacked: true,
                                gridLines: {
                                    display: false
                                },
                                ticks:{
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
                        footerAlign: 'center',
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
                                let index = tooltipItem.index;
                                let label = tooltipItem.label;
                                let unit = self._unit;
                                let datasets = data['datasets'];
                                let tooltips = new Array();

                                tooltips.push(`${label}`);
                                for (let i = 0; i <datasets.length; i++) {
                                    let label = datasets[i]['label'];
                                    let fcVal = module.utility.addComma(datasets[i]['data'][index]);

                                    tooltips.push(`${label} : ${fcVal} ${unit}`);
                                }

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
        update: function(dates, keys, values, labels, colors)
        {
            if (this._chart != null) {
                this.clear.call(this);
            }

            if (Array.isArray(dates) === false) {
                this._create([]);
                return;
            }

            // 값 설정
            this.setData(keys, values, labels, colors);

            //  그래프 생성
            this._create(dates);
        },
        clear: function()
        {
            if (this._chart == null) {
                return;
            }

            this._chart.destroy();
            this._chart = null;
        },
        setData: function(keys, values, labels, colors)
        {
            if (keys.length === 0 || values.length === 0 || labels.length === 0 || colors.length === 0) {
                return;
            }

            let labelLength = labels.length;
            let datasets = new Array();

            for (let i = 0; i < labelLength; i++) {
                let obj = {
                    label: labels[i],
                    data: values[i],
                    backgroundColor: `rgba(${colors[i]})`,
                    borderColor: `rgba(${colors[i]})`,
                    borderWidth: 3,
                    fill: false,
                    pointRadius: 3,
                    pointBorderWidth: 1,
                };

                datasets.push(obj);
            }

            this._dates = keys;
            this._keys = keys;
            this._values = values;
            this._labels = labels;
            this._colors = colors;

            this._datasets = datasets;
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
