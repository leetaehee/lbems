function createPieChart(id)
{
	let ctx = document.getElementById(id).getContext('2d');

	let controller = {
		_chart: null,
		_txt: "0",
		_unit: "%",
		_datasets: [],
		_labels: [],
		_values: [],
		_colors: [],
		callback: null,
		_create: function(labels)
		{
			let self = this;

			this._chart = new Chart(ctx, {
				type: 'doughnut',
				data: {
					labels: labels,
					datasets: self._datasets,
				},
				options: {
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
					tooltips: {
						callbacks: {
							label: function(tooltipItem, data)
							{
								let index = tooltipItem.index;
								let labels = data.labels;
								let val = self._values;
								let unit = self._unit;

								return labels[index] + ": "+val[index] + '' + unit;
							}
						}
					},
					responsive: true,
					maintainAspectRatio: false,
				}
			});
		},
		update: function(labels, colors, values)
		{
			if(this._chart != null) {
				this.clear.call(this);
			}

			if(Array.isArray(labels) == false || Array.isArray(colors) == false || Array.isArray(values) == false) {
				this._create([]);
				return;
			}

			// 값 설정
			this.setData(labels, colors, values);

			//  그래프 생성
			this._create(labels);
		},
		clear: function()
		{
			if (this._chart == null) {
				return;
			}

			this._chart.destroy();
			this._chart = null;
		},
		setData: function(labels, colors, values)
		{
			if (values.length === 0 || labels.length === 0 || colors.length === 0) {
				return;
			}

			let labelLength = labels.length;

			let datasets = [];
			let rgbColors = [];

			for (let i = 0; i < labelLength; i++) {
				rgbColors[i] = `rgba(${colors[i]}, 0.8)`;
			}

			let obj = {
				data: values,
				backgroundColor: rgbColors,
				borderColor: rgbColors,
				borderWidth: 1,
				fill: false,
				pointRadius: 7,
				pointBorderWidth: 5,
				pointBackgroundColor: "rgb(255, 255, 255)",
				percentageInnerCutout : 80,
			};

			datasets.push(obj);

			this._values = values;
			this._labels = labels;
			this._colors = colors;

			this._datasets = datasets;
		},
		setColor: function(color)
		{
			this._color = color;
		},
		setText: function(text) {
			this._txt = text;
		},
		setUnit: function(unit)
		{
			this._unit = unit;
		}
	};

	return controller;
}
