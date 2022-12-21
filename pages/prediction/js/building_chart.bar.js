function createBuildingBarChart(id, color, color2, legend, legend2)
{
	let ctx = document.getElementById(id).getContext('2d');

	let controller = {
		_chart: null,
		_color: color,
		_color2: color2,
		_legend: legend,
		_legend2: legend2,
		_chartType: 'bar',
		_decimalPoint: 0,
		_dates: [],
		_tooltips1: [],
		_tooltips2: [],
		_unit: 'kWh',
		_callback: null,
		_create: function(x, y, y2, tooltips1, tooltips2, decimalPoint)
		{
			let self = this;
			let color = this._color;
			let color2 = this._color2;

			self._tooltips1 = tooltips1;
			self._tooltips2 = tooltips2;
			self._decimalPoint = decimalPoint;

			this._chart = new Chart(ctx, {
				type: self._chartType,
				data: {
					labels: x,
					datasets: [
						{
							label: self._legend,
							data: y,
							yAxisID: 'line-y',
							backgroundColor: `rgba(${color}, 0.8)`,
							borderColor: `rgba(${color}, 1)`,
							borderWidth: 2,
							fill: false,
							pointRadius: 2,
							pointBorderWidth: 4,
						},
						{
							label: self._legend2,
							data: y2,
							yAxisID: 'line-y',
							backgroundColor: `rgba(${color2}, 0.8)`,
							borderColor: `rgba(${color2}, 1)`,
							borderWidth: 2,
							fill: false,
							pointRadius: 2,
							pointBorderWidth: 4,
						},
					]
				},
				options: {
					scales: {
						yAxes: [
							{
								id: 'line-y',
								ticks: {
									beginAtZero: true,
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
								gridLines: {
									display: false
								},
								ticks:{
									//fontColor:'#a3a3a3'
								},
								stacked: true
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
					tooltips: {
						callbacks: {
							label: function(tooltipItem, data)
							{
								const decimalPoint = self._decimalPoint;

								let val = Number(tooltipItem.value);
								let formatVal = module.utility.addComma(val.toFixed(decimalPoint));
						
								return `${formatVal} ${unit}`;
							},
						}
					}
				}
			});
		},
		update: function(x, y, y2, tooltips1, tooltips2, decimalPoint)
		{
			if (this._chart != null) {
				this.clear.call(this);
			}

			if (Array.isArray(x) == false || Array.isArray(y) == false || Array.isArray(y2) == false) {
				this._create([], [], [], '', '', 0);
				return;
			}

			this._create(x, y, y2, tooltips1, tooltips2, decimalPoint);
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
			this._unit = unit;
		},
		getData: function()
		{
			let data = [];

			if (this._chart == null) {
				return data;
			}

			let labels = this._chart.data.labels;

			if (this._chart.data.datasets.length < 2) {
				return data;
			}

			let data1  = this._chart.data.datasets[0].data;
			let data2  = this._chart.data.datasets[1].data;

			if (labels === undefined || data1 === undefined || data2 === undefined) {
				return data;
			}

			if (labels.length != data1.length || data1.length != data2.length) {
				return data;
			}

			let len = labels.length;

			for(let i = 0; i < len; i++) {
				let row = [labels[i], data1[i], data2[i]]
				data.push(row);
			}

			return data;
		}
	};

	return controller;
}
