function createUsePieChart(id, cutoutPercentage)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _txt: '0',
        _unit: 'kWh',
        _cutoutPercentage: cutoutPercentage,
        _vals: [],
        _labels: [],
        _colors: [],
        _values: [],
        _datasets: [],
        callback: null,
        _create: function(labels)
        {
            let self = this;

            this._chart = new Chart(ctx,
            {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: self._datasets,
                },
                options: {
                    cutoutPercentage: self._cutoutPercentage,
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
                                let vals = self._values;

                                let unit = self._unit;
                                let message = '';

                                if (index === 0 && vals[index] === 100) {
                                    message = labels[index];
                                } else {
                                    message = labels[index] + ': ' + module.utility.addComma(vals[index]) + ' ' + unit;
                                }

                                return message;
                            }
                        }
                    },
                    legend:
                    {
                        display: false,
                        position: 'bottom',
                        labels: {
                            boxWidth: 20,
                        },
                        onClick: null,
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    elements: {
                    }
                }
            });
        },
        update: function(labels, colors, values)
        {
            if (this._chart != null) {
                this.clear.call(this);
            }

            if(Array.isArray(labels) == false || Array.isArray(colors) == false || Array.isArray(values) == false) {
                this._create([]);
                return;
            }

            // 값 설정
            this.setData(labels, colors, values);

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
        setData: function(labels, colors, values)
        {
            if (values.length === 0 || labels.length === 0 || colors.length === 0) {
                return;
            }

            let labelLength = labels.length;

            let datasets = new Array();
            let rgbColors = new Array();

            for (let i = 0; i < labelLength; i++) {
                rgbColors[i] = `rgba(${colors[i]}, 0.8)`;
            }

            let obj = {
                data: values,
                backgroundColor: rgbColors,
                borderColor: rgbColors,
                borderWidth: 1,
                pointRadius : 7,
                pointBorderWidth : 5,
                pointBackgroundColor: "rgb(255, 255, 255)",
                percentageInnerCutout : 80,
            };

            datasets.push(obj);

            this._values = values;
            this._labels = labels;
            this._colors = colors;

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
