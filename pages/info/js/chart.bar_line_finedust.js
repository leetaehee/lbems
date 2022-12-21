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
		_tooltips: [],
		_units:['원', '㎍/m³'],
		_callback: null,
		_create: function(x, x2, y, y2, tooltips)
		{
			let self = this;
			let color = this._color;
			let color2 = this._color2;

			self._dates = x2;
			self._tooltips = tooltips;

			this._chart = new Chart(ctx, {
				type: self._chartType,
				data: {
					labels: x,
					datasets: [
						{
							label: self._legend2,
							data: y2,
							yAxisID: "line-y",
							type: "line",
							backgroundColor: `rgba(${color2}, 1)`,
							borderColor: `rgba(${color2}, 1)`,
							borderWidth: 2,
							fill: false,
							pointRadius: 3,
							pointBorderWidth: 4,
						},
						{
							label: self._legend,
							data: y,
							yAxisID: "line-y",
							backgroundColor: `rgba(${color}, 1)`,
							borderColor: `rgba(${color}, 1)`,
							borderWidth: 2,
							fill: false,
							pointRadius: 3,
							pointBorderWidth: 4,
						},
					]
				},
				options: {
					scales: {
						yAxes: [
							{
								id: "line-y",
								ticks: {
									beginAtZero:true,
									maxTicksLimit: 4,
								   //fontColor:'#a3a3a3'
								},
								/*
								scaleLabel: {
									display: true,
									labelString: this._units[1],
								},
								 */
							},
							{
								id: "line-y2",
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
								barThickness : 30,
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
						let date  = self._dates[index];
						let mode  = parseInt(self._mode) + 1;

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

							tooltip.borderColor='rgba(159, 140, 199, 60)';
							tooltip.borderWidth = 2;
							tooltip.cornerRadius = 5;
							tooltip.titleFontSize= 16;
							tooltip.footerFontColor= chart._data.tooltipColor;
							tooltip.bodyFontSize= 12;
							tooltip.displayColors= false;
                            tooltip.backgroundColor= '#ffffff';
							tooltip._footerAlign = 'center';
						},
						footerAlign: "center",
						callbacks: {
							title: function(tooltipItem, data)
							{
								return "";
							},
							footer: function(tooltipItem, data)
							{
								return "";
							},
							label: function(tooltipItem, data)
							{
								let index = tooltipItem.index;
								let val = tooltipItem.value;
								let date = self._dates[index];
								let unit = self._units[1];
								val = module.utility.addComma(val);

								let tooltip = [date];
								tooltip.push(`${val} ${unit}`);

								return tooltip;
							},
							labelTextColor: function(tooltipItem, chart)
							{
								return '#9f8cc7';
							}
						}
					}
				}
			});
		},
		update: function(x, x2, y, y2, tooltips)
		{
			if (this._chart != null) {
				this.clear.call(this);
			}

			if (Array.isArray(x) == false || Array.isArray(y) == false || Array.isArray(y2) == false) {
				this._create([], [], [], [], []);
				return;
			}

			this._create(x, x2, y, y2, tooltips);
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
