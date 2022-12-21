function createUseAndDistributionPieChart(id)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _txt: '0',
        _unit: 'kWh',
        _labels: [],
        _colors: [],
        _usages: [],
        _distributions: [],
        _datasets: [],
        callback: null,
        _create: function(labels)
        {
            let self = this

            this._chart = new Chart(ctx,
            {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: self._datasets,
                },
                options: {
                    cutoutPercentage: 83,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: false,
                                display: false
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false
                            }
                        }],
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data)
                            {
                                let index = tooltipItem.index;

                                let labels = data.labels;
                                let distributions = self._distributions;
                                let usages = self._usages;

                                let unit = self._unit;
                                let message = '';

                                if (index === 0 && distributions[index] === 100) {
                                    message = labels[index];
                                } else {
                                    let used = usages[index];

                                    message = labels[index] + '(' + module.utility.addComma(distributions[index]) + '%) : ';
                                    message += module.utility.addComma(used.toFixed(1)) + ' ' + unit;
                                }

                                return message;
                            }
                        }
                    },
                    legend: {
                        display: false,
                        position: 'bottom',
                        labels:
                            {
                                boxWidth: 15,
                            },
                        onClick: null,
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    elements: {
                        center: {
                        }
                    }
                }
            });
        },
        update: function(labels, colors, usages, distributions)
        {
            if(this._chart != null) {
                this.clear.call(this);
            }

            if(Array.isArray(labels) == false || Array.isArray(colors) == false || Array.isArray(usages) == false || Array.isArray(distributions) == false) {
                this._create([], [], [], []);
                return;
            }

            // 값 설정
            this.setData(labels, colors, usages, distributions);

            //  그래프 생성
            this._create(labels);
        },
        clear: function()
        {
            if (this._chart == null) {
                return;
            }

            this._chart.destroy();
            this._chart = null;
        },
        setData: function(labels, colors, usages, distributions)
        {
            if (usages.length === 0 || distributions.length === 0 || labels.length === 0 || colors.length === 0) {
                return;
            }

            let labelLength = labels.length;

            let datasets = new Array();
            let rgbColors = new Array();

            for (let i = 0; i < labelLength; i++) {
                rgbColors[i] = `rgba(${colors[i]}, 0.8)`;
            }

            let obj = {
                data: distributions,
                backgroundColor: rgbColors,
                borderColor: rgbColors,
                borderWidth: 1,
                pointRadius : 7,
                pointBorderWidth : 5,
                pointBackgroundColor: "rgb(255, 255, 255)",
                percentageInnerCutout : 80,
            };

            datasets.push(obj);

            this._labels = labels;
            this._colors = colors;
            this._usages = usages;
            this._distributions = distributions;

            this._datasets = datasets;
        },
        setColor: function(color)
        {
            this._color = color;
        },
        setText: function(text)
        {
            this._txt = text;
        },
        setUnit: function(unit)
        {
            this._unit = unit;
        }
    };

    return controller;
}
