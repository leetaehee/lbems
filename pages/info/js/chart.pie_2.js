function createPieChart2(id, labels, color1, color2)
{
	let ctx = document.getElementById(id).getContext('2d');

	let controller = {
		_chart: null,
		_txt: "0",
		_unit: "kWh",
		_color: color1,
		_color1: color2,
		callback: null,
		_create: function(x) {
			let self = this;
			let color = this._color;
			let color1 = this._color1;

			this._chart = new Chart(ctx, {
				type: 'doughnut',
				data: {
					labels: labels,
					datasets: [
						{
							data : x,
							backgroundColor : [`rgba(${color}, 1)`, `rgba(${color1}, 0.8)`],
							borderColor : [`rgba(${color}, 1)`, `rgba(${color1}, 0.8)`],
							borderWidth : 0,
							fill : false,
							pointRadius : 7,
							pointBorderWidth : 5,
							pointBackgroundColor : "rgb(255,255,255)",
							percentageInnerCutout : 80,
						},
					]
				},
				options: {
					hover: false,
					cutoutPercentage: 80,
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
					elements: {
						center: {
							text: controller._txt + "월",
							label: "에너지 예상요금",
							color: "#8e8e8e", // Default is #000000
							fontStyle: 'Arial', // Default is Arial
							sidePadding: 20 // Defualt is 20 (as a percentage)
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
