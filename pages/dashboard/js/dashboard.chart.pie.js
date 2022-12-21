function createPieChart(id, labels, color1, color2, color3, color4)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _txt: "0",
        _unit: "kWh",
        _color: color1,
        _color1: color2,
        _color2: color3,
        _color3: color4,
        callback: null,
        _create: function(x)
        {
            let self = this;
            let color = this._color;
            let color1 = this._color1;
            let color2 = this._color2;
            let color3 = this._color3;

            this._chart = new Chart(ctx,
                {
                    type: 'pie',
                    data: {
                        //labels: x,
                        labels: labels,
                        datasets: [{
                            data: x,
                            backgroundColor: [`rgba(${color}, 1)`, `rgba(${color1}, 0.8)`, `rgba(${color2}, 0.8)`, `rgba(${color3}, 0.8)`],
                            borderColor: [`rgba(${color}, 1)`, `rgba(${color1}, 0.8)`, `rgba(${color2}, 0.8)`, `rgba(${color3}, 0.8)`],
                            borderWidth: 1,
                            fill: false,
                            pointRadius : 7,
                            pointBorderWidth: 5,
                            pointBackgroundColor: "rgb(255, 255, 255)",
                            percentageInnerCutout : 80,

                        },
                        ]
                    },
                    options: {
                        cutoutPercentage: 85,
                        //showAllTooltips: true,
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero:true,
                                    display: false
                                },
                                gridLines: {
                                    display: false,
                                    drawBorder: false
                                }
                            }],
                        },
                        legend: {
                            display: false,
                            position: 'bottom',
                            labels:{
                                boxWidth:10
                            }

                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        //events: [],
                        elements: {
                            center: {
                            }
                        },
                        tooltips: {
                            enabled: false,
                        }
                    }
                });
        },
        update: function(x)
        {
            if(this._chart != null) {
                this.clear.call(this);
            }

            if(Array.isArray(x) == false) {
                this._create([]);
                return;
            }

            this._create(x);
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
