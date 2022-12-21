function createBarLineChart(id, color, color2, color3, legend, legend2, mode)
{
	let ctx = document.getElementById(id).getContext('2d');

	let controller = {
		_chart: null,
		_color: color,
		_color2: color2,
		_color3: color3,
		_legend: legend,
		_legend2: legend2,
		_chartType: 'bar',
		_mode: mode,
		_dates: [],
		_tooltips1: [],
		_units:['kgCO2eq', 'kwh', 'kwh'],
		_callback: null,
		_create: function(x, x2, y, y2, tooltips1, tooltips2) 
		{
			let self = this;

			self._dates = x2;
			self._tooltips1 = tooltips1;
			self._tooltips2 = tooltips2;

			this._chart = new Chart(ctx, {
				type: self._chartType,
				data: {
					labels: x,
					datasets: [
					{
						label: self._legend2,
						data: x2,
						yAxisID: "line-y2",
						type: "line",
						backgroundColor: `rgba(${color3}, 1)`,
						borderColor: `rgba(${color3}, 1)`,
						borderWidth: 2,
						fill: false,
						pointRadius: 3,
						pointBorderWidth: 4,
					},
					{
						label: self._legend,
						data: y, // 생산량
						yAxisID: "line-y",
						backgroundColor: `rgba(${color}, 1)`,
						borderColor: `rgba(${color}, 1)`,
						borderWidth: 2,
						fill: false,
						pointRadius: 0,
						pointBorderWidth: 0,
						pointHoverRadius: 0,
					},
					{
						label: self._legend,
						data: y2, // 소비량
						yAxisID: "line-y",
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
						yAxes: [{
							id: "line-y",
							stacked: true,
							ticks: {
								beginAtZero:true,
								maxTicksLimit: 4,
                                fontColor:'#a3a3a3'
							},
							/*
							scaleLabel: {
								display: true,
								labelString: "kWh / kgCO2eq",
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
						}],
						xAxes: [{
							stacked: true,
							barThickness : 30,
							gridLines: {
								display: false
							},
                            ticks:{
                                fontColor:'#a3a3a3'
                            }
						}]
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
						let label = t[0]._chart.data.labels;
						let date  = self._dates[index];
						let mode  = parseInt(self._mode) + 1;

						let graphData = t[0]._chart.data.datasets;

						let params = {
							month : label[index],
							productions : graphData[1].data[index],
							consumptions : graphData[2].data[index]
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
								return "";
							},
							footer: function(tooltipItem, data) 
							{
								return "";
							},
							label: function(tooltipItem, data) 
							{
								let titles = ['CO2배출량', '생산량', '소비량'];

								let index = tooltipItem.index;
								let datasetIndex = tooltipItem.datasetIndex;

								let val	= parseFloat(tooltipItem.value);
								let formatVal = module.utility.addComma(val.toFixed(2));
	
								let label = tooltipItem.label;
								let unit = self._units[datasetIndex];

								let tooltips = new Array();

								tooltips.push(`${label} ${titles[datasetIndex]}`);
								tooltips.push(`${formatVal} ${unit}`);

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
		update: function(x, x2, y, y2, tooltips1, tooltips2)
		{
			if(this._chart != null) {
				this.clear.call(this);
			}

			if(Array.isArray(x) == false || Array.isArray(y) == false || Array.isArray(y2) == false) {
				this._create([], [], [], [], [], []);
				return;
			}

			this._create(x, x2, y, y2, tooltips1, tooltips2);
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
		getData: function() 
		{
			let data = [];

			if (this._chart == null) {
				return data;
			}

			let labels = this._chart.data.labels;

			if(this._chart.data.datasets.length < 2)
				return data;

			let data1  = this._chart.data.datasets[0].data;
			let data2  = this._chart.data.datasets[1].data;

			if (labels === undefined || data1 === undefined || data2 === undefined){ 
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
		}
	};

	//controller.update([1, 2, 3], [1, 2, 3], [4, 5, 6]);

	return controller;
}
