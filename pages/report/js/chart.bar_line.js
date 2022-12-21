function createBarLineChart(id, color, color2, color3, legend, legend2, isDisplayPrice, mode)
{
	let ctx = document.getElementById(id).getContext('2d');

	let controller = {
		_chart: null,
		_color: color,
		_color2: color2,
		_color3: color3,
		_legend: legend,
		_legend2: legend2,
		_decimalPoint: 0,
		_chartType: 'bar',
		_mode: mode,
		_dates: [],
		_isDisplayPrice: isDisplayPrice,
		_tooltips: [],
		_units:['원', 'kWh'],
		_callback: null,
		_create: function(x, x2, y, y2, tooltips, decimalPoint)
		{
			let self = this;
			let color = this._color;
			let color2 = this._color2;
			let legend = this._legend;
			let legend2 = this._legend2;

			self._dates = x2;
			self._tooltips = tooltips;
			self._decimalPoint = decimalPoint;

			this._chart = new Chart(ctx, {
				type: self._chartType,
				data: {
					labels: x,
					datasets: [
						{
							label: self._legend2,
							data: y2,
							yAxisID: 'line-y2',
							type: 'line',
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
							yAxisID: 'line-y',
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
								id: 'line-y',
								ticks: {
									beginAtZero: true,
									maxTicksLimit: 4,
								},
								/*
									scaleLabel:
									{
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
						let $id = evt['target']['id'];
						let color = self._color3;

						if(Array.isArray(this.active) == true && this.active.length >= 2) {
							if(self._lastAct1 != null) {
								self._lastAct1.custom = null;
								self._lastAct1 = null;
							}

							this.active[1].custom = this.active[1] || {};
							this.active[1].custom.backgroundColor = `rgba(${color}, 1)`;

							self._lastAct1 = this.active[1];
							self._chart.update();
						}

						if (self._callback instanceof Function === false) {
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

							tooltip.borderColor='rgba(159, 140, 199, 60)';
							tooltip.borderWidth = 2;
							tooltip.cornerRadius = 5;
							tooltip.titleFontSize= 16;
							tooltip.footerFontColor = chart._data.tooltipColor;
							tooltip.bodyFontSize= 12;
							tooltip.displayColors= false;
                            
							tooltip.backgroundColor= '#ffffff';
							tooltip._footerAlign = 'center';
						},
						footerAlign: 'center',
						callbacks: {
							title: function(tooltipItem, data)
							{
								return "";
							},
							footer: function(tooltipItem, data)
							{
								return '';
							},
							label: function(tooltipItem, data)
							{
								let index = tooltipItem.index;
								let val = parseFloat(tooltipItem.value);

								let date = self._tooltips[index];
								let unit = self._units[tooltipItem.datasetIndex];
								const decimalPoint = self._decimalPoint;
								const isDisplayPrice = self._isDisplayPrice;

								let tooltip = [date];

								if (tooltipItem.datasetIndex == 1 ) {
									tooltip[0] = tooltip[0] + "" + "현황";
									val = module.utility.addComma(val.toFixed(decimalPoint));

									if (legend === '발전량' && legend2 === '절감비용') {
										tooltip[0] = tooltip[0].slice(0, 10) + "" + "발전 현황";
									}

									let tmpCost = data['datasets'][0]['data'][index];
									let tmpCostUnit = self._units[0];

									if (tmpCost !== undefined) {
										tmpCost = module.utility.addComma(tmpCost.toFixed(0));
									}

									tooltip.push(`${legend}: ${val} ${unit}`);

									if (isDisplayPrice === true) {
										tooltip.push(`${legend2}: ${tmpCost} ${tmpCostUnit}`);
									}
								}

								if (tooltipItem.datasetIndex == 0) {
									tooltip[0] = tooltip[0] + " " + "비용";
									tooltip.push(`${module.utility.addComma(val.toFixed(0))} ${unit}`);
								}

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
		update: function(x, x2, y, y2, tooltips, decimalPoint)
		{
			if (this._chart != null) {
				this.clear.call(this);
			}

			if (Array.isArray(x) == false || Array.isArray(y) == false || Array.isArray(y2) == false) {
				this._create([], [], [], [], [], 0);
				return;
			}

			this._create(x, x2, y, y2, tooltips, decimalPoint);
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
		setLegend: function(used, price)
		{
			this._legend = used;
			this._legend2 = price;
		},
		getData: function()
		{
			let data = [];
			const isDisplayPrice = self._isDisplayPrice;

			if (this._chart == null) {
				return data;
			}

			let labels = this._chart.data.labels;

			if (this._chart.data.datasets.length < 2) {
				return data;
			}

			let data1 = this._chart.data.datasets[1].data;
			let data2 = this._chart.data.datasets[0].data;

			if (labels === undefined
				|| data1 === undefined
				|| (data2 === undefined && isDisplayPrice === true)) {
				return data;
			}

			if (isDisplayPrice === true && (labels.length != data1.length || data1.length != data2.length)) {
				return data;
			}

			let len = labels.length;

			for (let i = 0; i < len; i++) {
				let row = [labels[i], data1[i], data2[i]]
				data.push(row);
			}

			return data;
		},
	};

	return controller;
}
