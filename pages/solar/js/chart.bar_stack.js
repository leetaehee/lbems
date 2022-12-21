function createBarStackLineChart(id, color, color2)
{
	let ctx = document.getElementById(id).getContext('2d');

	let controller = {
		_chart: null,
		_color1: color,
		_color2: color2,
		_chartType: 'bar',
		_dates: [],
		_units: 'kWh',
		_decimalPoint: 0,
		_callback: null,
		_create: function(x, x2, y, decimalPoint)
		{
			let self = this;

			self._dates = x2;
			self._decimalPoint = decimalPoint;

			let color1 = self._color1;
			let color2 = self._color2;

			this._chart = new Chart(ctx, {
				type: self._chartType,
				data: {
					labels: x,
					datasets: [
						{
							label: self._legend2,
							data: x2, //생산량
							yAxisID: 'line-y',
							backgroundColor: `rgba(${color1}, 1)`,
							borderColor: `rgba(${color1}, 1)`,
							borderWidth: 2,
							fill: false,
							pointRadius: 3,
							pointBorderWidth: 4,
						},
						{
							label: self._legend,
							data: y, // 소비량
							yAxisID: 'line-y',
							backgroundColor: `rgba(${color2}, 1)`,
							borderColor: `rgba(${color2}, 1)`,
							borderWidth: 2,
							fill: false,
							pointRadius: 0,
							pointBorderWidth: 0,
							pointHoverRadius: 0,
						},
					]
				},
				options: {
					scales: {
						yAxes: [
							{
								id: 'line-y',
								stacked: true,
								ticks: {
									beginAtZero:true,
									maxTicksLimit: 4,
									fontColor:'#a3a3a3'
								},
								/*
								scaleLabel: {
									display: true,
									labelString: this._units,
								},
								 */
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
							}
						],
						xAxes: [
							{
								stacked: true,
								barThickness : 30,
								gridLines: {
									display: false
								},
								ticks:{
									fontColor:'#a3a3a3'
								}
							}
						]
					},
					legend: {
						display: false,
						position: 'bottom',
					},
					responsive: true,
					maintainAspectRatio: false,
					onClick: function(evt, activeElements)
					{
					},
					tooltips:
					{
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
								let titles = ['생산량', '소비량'];

								let index = tooltipItem.index;
								let datasetIndex = tooltipItem.datasetIndex;

								const decimalPoint = self._decimalPoint;

								let val	= parseFloat(tooltipItem.value);
								let formatVal = module.utility.addComma(val.toFixed(decimalPoint));

								let label = tooltipItem.label;
								let unit = self._units;

								let tooltips = new Array();

								tooltips.push(`${label} ${titles[datasetIndex]}`);
								tooltips.push(`${formatVal} ${unit}`);

								return tooltips;
							},
							labelTextColor: function(tooltipItem, chart) {
								return '#ffffff';
							}
						}
					}
				}
			});
		},
		update: function(x, x2, y, decimalPoint)
		{
			if(Array.isArray(x) == false || Array.isArray(y) == false || Array.isArray(y) == false) {
				this._create([], [], [], 0);
				return;
			}

			this._create(x, x2, y, decimalPoint);
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
		setUnit: function(unit)
		{
			this._units[1]= unit;
		},
	};

	return controller;
}
