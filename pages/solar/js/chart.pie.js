Chart.pluginService.register({
  beforeRender: function (chart) {
    if (chart.config.options.showAllTooltips) {
        // create an array of tooltips
        // we can't use the chart tooltip because there is only one tooltip per chart
        chart.pluginTooltips = [];
        chart.config.data.datasets.forEach(function (dataset, i) {
            chart.getDatasetMeta(i).data.forEach(function (sector, j) {
                chart.pluginTooltips.push(new Chart.Tooltip({
                    _chart: chart.chart,
                    _chartInstance: chart,
                    _data: chart.data,
                    _options: chart.options.tooltips,
                    _active: [sector]
                }, chart));
            });
        });

        // turn off normal tooltips
        chart.options.tooltips.enabled = false;
    }
},
  afterDraw: function (chart, easing) {
    if (chart.config.options.showAllTooltips) {
        // we don't want the permanent tooltips to animate, so don't do anything till the animation runs atleast once
        if (!chart.allTooltipsOnce) {
            if (easing !== 1)
                return;
            chart.allTooltipsOnce = true;
        }

        // turn on tooltips
        chart.options.tooltips.enabled = true;
        Chart.helpers.each(chart.pluginTooltips, function (tooltip) {
            tooltip.initialize();
            tooltip.update();
            // we don't actually need this since we are not animating tooltips
            tooltip.pivot();
            tooltip.transition(easing).draw();
        });
        chart.options.tooltips.enabled = false;
    }
  }
});

function createPieChart(id, labels, color1, color2, color3) {
	let ctx = document.getElementById(id).getContext('2d');

	let controller = {
		_chart: null,
		_txt: "0",
		_unit: "%",
		_color: color1,
		_color1: color2,
		_color2: color3,
		_val: [],
		callback: null,
		_create: function(x) 
		{
			let self = this;

			let color = this._color;
			let color1 = this._color1;
			let color2 = this._color2;

			self._val = x;

			this._chart = new Chart(ctx, {
				type: 'pie',
				data: {
					labels: labels,
					datasets: [{
						data : x,
						backgroundColor : [`rgba(${color}, 0.8)`, `rgba(${color1}, 0.8)`, `rgba(${color2}, 0.8)`],
						borderColor: [`rgba(${color}, 1)`, `rgba(${color1}, 0.8)`, `rgba(${color2}, 0.8)`],
						borderWidth: 1,
						fill: false,
						pointRadius: 7,
						pointBorderWidth: 5,
						pointBackgroundColor: "rgb(255, 255, 255)",
					},
					]
				},
				options: {
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
					tooltips: {
						callbacks: {
							label: function(tooltipItem, data) {
								let index = tooltipItem.index;
								let labels = data.labels;
								let val = self._val;

								let message = '';
								message = labels[index] + ': ' + val[index] + '%';

								return message;
							}
						}
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
