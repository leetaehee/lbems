function createPieChart(id, labels, color1, color2, color3, color4, color5)
{
	let ctx = document.getElementById(id).getContext('2d');

	let controller = {
		_chart: null,
		_txt: "0",
		_unit: "%",
		_color: color1,
		_color1: color2,
		_color2: color3,
		_color3: color4,
		_color4: color5,
		_val: [],
		callback: null,
		_create: function(x)
		{
			let self = this;
			let color = this._color;
			let color1 = this._color1;
			let color2 = this._color2;
			let color3 = this._color3;
			let color4 = this._color4;

			self._val = x;

			this._chart = new Chart(ctx, {
				type: 'doughnut',
				data: {
					labels: labels,
					datasets: [
						{
							data : x,
							backgroundColor : [`rgba(${color}, 0.8)`, `rgba(${color1}, 0.8)`, `rgba(${color2}, 0.8)`, `rgba(${color3}, 0.8)`, `rgba(${color4}, 0.8)`],
							borderColor : [`rgba(${color}, 1)`, `rgba(${color1}, 0.8)`, `rgba(${color2}, 0.8)`, `rgba(${color3}, 0.8)`, `rgba(${color4}, 0.8)`],
							borderWidth : 1,
							fill : false,
							pointRadius : 7,
							pointBorderWidth : 5,
							pointBackgroundColor : "rgb(255, 255, 255)",
							percentageInnerCutout : 80,
						},
					]
				},
				options: {
					hover: false,
					cutoutPercentage: 80,
					scales: {
						yAxes: [
							{
								ticks: {
									beginAtZero:true,
									display: false
								},
								gridLines: {
									display: false,
									drawBorder: false
								}
							}
						],
					},
					tooltips: {
						callbacks: {
							label: function(tooltipItem, data) {
								let index = tooltipItem.index;
								let labels = data.labels;
								let val = self._val;
								let unit = '%';

								let message = '';
								message = labels[index] + ': ' + val[index] + '' + unit;

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
					//events: [],
					elements: {
						center: {
							text: controller._txt + "월",
							label: "에너지 예상요금",
							color: "#8e8e8e", // Default is #000000
							fontStyle: 'Arial', // Default is Arial
							sidePadding: 20 // Defualt is 20 (as a percentage)
						}
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
		updatePrice: function(x)
		{
			if (this._chart == null) {
				return;
			}

			this._chart.options.elements.center.text2 = "절감비용";
			this._chart.options.elements.center.text = Utility.addComma(x) + '원';
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
