function createLoadingChart(id, color)
{
    let ctx = document.getElementById(id).getContext('2d');

    let controller = {
        _chart: null,
        _color: color,
        callback: null,
        _create: function(x) {
            this._chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: x,
                        backgroundColor: [`rgba(${color}, 1)`],
                        borderColor: [`rgba(${color}, 1)`],
                        borderWidth: 1,
                        fill: false,
                        pointRadius: 7,
                        pointBorderWidth: 5,
                        pointBackgroundColor: "rgb(255, 255, 255)",
                        percentageInnerCutout: 80,
                    },
                    ]
                },
                options: {
                    cutoutPercentage: 85,
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
                    tooltips: {
                        enabled: false,
                    }
                }
            });
        },
        update: function(x)
        {
            if (this._chart != null) {
                this.clear.call(this);
            }

            if (Array.isArray(x) == false) {
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
    };

    return controller;
}
