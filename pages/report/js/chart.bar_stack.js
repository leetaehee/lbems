function createReportBarStackFloorChart(id, mode, isUseLegend = false)
{
	let ctx = document.getElementById(id).getContext('2d');

	let controller = {
		_chart: null,
		_chartType: 'bar',
		_unit: 'kWh',
		_isUseLegend: isUseLegend,
		_datasets: [],
		_dates: [],
		_mode: mode,
		_keys: [],
		_values: [],
		_labels: [],
		_colors: [],
		_decimalPoint: 0,
		_callback: null,
		_create: function(dates)
		{
			let self = this;
			let isUseLegend = this._isUseLegend;

			this._chart = new Chart(ctx, {
				type: self._chartType,
				data: {
					labels: dates,
					datasets: self._datasets,
				},
				options: {
					scales: {
						yAxes: [
							{
								id: "line-y",
								stacked: true,
								ticks: {
									beginAtZero:true,
									maxTicksLimit: 4,
								}
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
								stacked: true,
								gridLines: {
									display: false
								},
							}
						]
					},
					legend: {
						display: isUseLegend,
						position: 'top',
						onClick: null,
					},
					responsive: true,
					maintainAspectRatio: false,
					onClick: function(evt, activeElements)
					{
						if (Array.isArray(this.active) == true && this.active.length >= 2) {
							if (self._lastAct1 != null) {
								self._lastAct1.custom = null;
								self._lastAct1 = null;
							}
						}

						if (self._callback instanceof Function == false) {
							return;
						}

						let t = self._chart.getElementAtEvent(evt);

						if (t.length <= 0 || t[0]._index === undefined) {
							return;
						}

						let index = t[0]._index;
						let date = self._keys[index];
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
								let date = tooltipItem.label;
								let datasets = data['datasets'];
								let unit = self._unit;
								const decimalPoint = self._decimalPoint;

								let tooltips = [date];
								for (let i = 0; i <datasets.length; i++) {
									let label = datasets[i]['label'];
									let value = datasets[i]['data'][index];
									let fcVal = module.utility.addComma(value.toFixed(decimalPoint));

									tooltips.push(`${label} : ${fcVal} ${unit}`);
								}

								return tooltips;
							},
						}
					}
				}
			});
		},
		update: function(dates, keys, values, labels, colors, decimalPoint)
		{
			if (this._chart != null) {
				this.clear.call(this);
			}

			if (Array.isArray(dates) === false) {
				this._create([]);
				return;
			}

			// 값 설정
			this.setData(keys, values, labels, colors, decimalPoint);

			//  그래프 생성
			this._create(dates);
		},
		setColor: function(color)
		{
			this._color = color;
		},
		setUnit: function(unit)
		{
			this._unit = unit;
		},
		setData: function(keys, values, labels, colors, decimalPoint)
		{
			if (keys.length === 0 || values.length === 0 || labels.length === 0 || colors.length === 0) {
				return;
			}

			let labelLength = labels.length;
			let datasets = [];

			for (let i = 0; i < labelLength; i++) {
				let obj = {
					label: labels[i],
					data: values[i],
					yAxisID : "line-y",
					backgroundColor: `rgba(${colors[i]})`,
					borderColor: `rgba(${colors[i]})`,
					borderWidth: 3,
					fill: false,
					pointRadius: 3,
					pointBorderWidth: 1,
					barThickness : 40,
				};

				datasets.push(obj);
			}

			this._dates = keys;
			this._keys = keys;
			this._values = values;
			this._labels = labels;
			this._colors = colors;
			this._decimalPoint = decimalPoint;

			this._datasets = datasets;
		},
		clear: function()
		{
			if (this._chart === null) {
				return;
			}

			this._chart.destroy();
			this._datasets = [];
			this._chart = null;
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

			let data1 = this._chart.data.datasets[0].data;
			let data2 = this._chart.data.datasets[1].data;

			if (labels === undefined || data1 === undefined || data2 === undefined) {
				return data;
			}

			if (labels.length != data1.length || data1.length != data2.length) {
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
