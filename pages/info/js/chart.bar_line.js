function createBarLineChart(id, color, color2, legend, legend2, mode)
{
	let ctx = document.getElementById(id).getContext('2d');

	let controller = {
		_chart: null,
		_color: color,
		_color2: color2,
		_legend: legend,
		_legend2: legend2,
		_chartType: 'bar',
		_mode: mode,
		_dates: [],
		_tooltips1: [],
		_statusColor: [],
		_decimalPoint: 0,
		_units:['원', 'kWh'],
		_callback: null,
		_create: function(x, x2, y, y2, tooltips1, tooltips2, statusColor, decimalPoint)
		{
			let self = this;
			let color;

			if (statusColor.length > 0) {
				color = statusColor;
			} else {
				color = this._color;
			}

			self._dates = x2;
			self._tooltips1 = tooltips1;
			self._tooltips2 = tooltips2;
			self._statusColor = statusColor;
			self._decimalPoint = decimalPoint;

			this._chart = new Chart(ctx, {
				type: self._chartType,
				data: {
					labels: x,
					datasets: [
						{
							label : self._legend2,
							data : y2,
							yAxisID : 'line-y',
							type : 'line',
							backgroundColor : `rgba(${color2}, 1)`,
							borderColor : `rgba(${color2}, 1)`,
							borderWidth : 2,
							fill : false,
							pointRadius : 0,
							pointBorderWidth : 0,
							pointHoverRadius : 0,
						},
						{
							label : self._legend,
							data : y,
							yAxisID : 'line-y',
							backgroundColor : color,
							borderColor : color,
							borderWidth : 2,
							fill : false,
							pointRadius : 0,
							pointBorderWidth : 0,
							pointHoverRadius : 0,
						},
					]
				},
				options: {
					hover: false,
					scales: {
						yAxes: [
							{
								id: 'line-y',
								ticks: {
									beginAtZero:true,
									maxTicksLimit: 4,
								},
								/*
								scaleLabel: {
									display: true,
									labelString: this._units[1],
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
						if (self._callback instanceof Function == false) {
							return;
						}

						let t = self._chart.getElementAtEvent(evt);

						if (t.length <= 0 || t[0]._index === undefined) {
							return;
						}

						let index = t[0]._index;
						let date = self._dates[index];
						let mode = parseInt(self._mode) + 1;

						let params = {
							mode : mode,
							date : date
						};

						self._callback(params);
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
						footerAlign: "center",
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
								let datasetIndex = tooltipItem.datasetIndex;
								const decimalPoint = self._decimalPoint;

								let val = parseFloat(tooltipItem.value).toFixed(decimalPoint);
								let formatVal = module.utility.addComma(val);

								let date = tooltipItem.label;
								let unit = self._units[1];

								let standardVal = data.datasets[0]['data'][index];

								let tooltips = new Array();

								if (datasetIndex == 0) {
									tooltips.push(`기준값: ${formatVal} ${unit}`)
								}

								if (datasetIndex == 1) {
									tooltips.push(`${date}`);
									tooltips.push(`${self._tooltips1[index]}`);
									tooltips.push(`기준값: ${standardVal} ${unit}`);
									tooltips.push(`부하량: ${formatVal} ${unit}`);
								}

								return tooltips;
							},
							labelTextColor: function(tooltipItem, chart)
							{
								return '#ffffff';
							}
						}
					}
				}
			});
		},
		update: function(x, x2, y, y2, tooltips1, tooltips2, statusColor, decimalPoint)
		{
			if (this._chart != null) {
				this.clear.call(this);
			}

			if (Array.isArray(x) === false || Array.isArray(y) === false || Array.isArray(y2) === false) {
				this._create([], [], [], [], [], [], [], 0);
				return;
			}

			this._create(x, x2, y, y2, tooltips1, tooltips2, statusColor, decimalPoint);
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
