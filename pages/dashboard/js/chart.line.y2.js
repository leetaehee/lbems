function createLineY2Chart(id, color, color2, color3, legend, legend2, legend3, grad)
{
	let ctx = document.getElementById(id).getContext('2d');

	if (grad !== undefined && grad !== null) {
		let height = ctx.canvas.height;

		let gradientStroke = ctx.createLinearGradient(0, 0, 0, height);
		gradientStroke.addColorStop(0, `rgb(${grad.color1})`);
		gradientStroke.addColorStop(1, `rgb(${grad.color2})`);
		color = gradientStroke;
	}

	let borderColor = '182,182,182';

	let controller = {
		_chart: null,
		_color: color,
		_color2: color2,
		_color3: color3,
		_legend: legend,
		_legend2: legend2,
		_legend3: legend3,
		_chartType: 'line',
		_decimalPoint: 0,
		_unit: 'kwh', 
		callback: null,
		_create: function(x, y, y2, standards)
		{
			let self = this;
			let color = this._color;
			let color2 = this._color2;
			let color3 = this._color3;

			this._chart = new Chart(ctx, {
				type: self._chartType,
				data: {
					labels: x,
					datasets: [
						{
							label: self._legend,
							data: y,
							backgroundColor: `rgba(${color}, 0.6)`,
							borderColor: `rgba(${color}, 1)`,
							borderWidth: 2,
							fill: true,
							pointRadius: 2,
							pointBorderWidth: 4,
						},
						{
							label: self._legend2,
							data: y2,
							backgroundColor: `rgba(${borderColor}, 1)`,
							borderColor: `rgba(${borderColor}, 0.3)`,
							borderWidth: 2,
							fill: false,
							pointRadius: 3,
							pointBorderWidth: 1,
						},
						{
							label: self._legend3,
							data: standards,
							backgroundColor: `rgba(${color3}, 1)`,
							borderColor: `rgba(${color3}, 1)`,
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
								/*
								scaleLabel: {
									display: true,
									labelString: this._unit,
								},
								 */
								ticks: {
									beginAtZero:true,
									maxTicksLimit: 4,
								},
							}
						],
						xAxes: [
							{
								gridLines: {
									display: false
								},
								ticks:{
								}
							}
						]
					},
					onClick: function(evt, activeElements)
					{
					},
					onHover: function(evt, activeElements)
					{
					},
					tooltips: {
						custom: function(tooltip) 
						{
							if (!tooltip) { 
								return; 
							}
							let chart = this;
							
							tooltip.displayColors = false;
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
								let index = tooltipItem.index;
								let datasetIndex = tooltipItem.datasetIndex;

								let label = tooltipItem.label;

								const unit = self._unit;
								const decimalPoint = self._decimalPoint;
								
								let tooltips = [];

								let previousValue = parseFloat(data['datasets'][1]['data'][index]).toFixed(decimalPoint);
								let currentValue =  parseFloat(tooltipItem.value).toFixed(decimalPoint);
								const standardValue = parseFloat(data['datasets'][2]['data'][index]).toFixed(decimalPoint);

								tooltips.push(`${label}`);

								if (datasetIndex == 0) {
									tooltips.push(`이전: ${module.utility.addComma(previousValue)} ${unit}`);
									tooltips.push(`현재: ${module.utility.addComma(currentValue)} ${unit}`);
									tooltips.push(`기준값: ${module.utility.addComma(standardValue)} ${unit}`);
								}

								if (datasetIndex == 1) {
									previousValue = parseFloat(data['datasets'][1]['data'][index]).toFixed(decimalPoint);
									currentValue =  parseFloat(data['datasets'][0]['data'][index]).toFixed(decimalPoint);

									tooltips.push(`이전: ${module.utility.addComma(previousValue)} ${unit}`);
									tooltips.push(`현재: ${module.utility.addComma(currentValue)} ${unit}`);
									tooltips.push(`기준값: ${module.utility.addComma(standardValue)} ${unit}`);
								}

								if (datasetIndex == 2) {
									tooltips.push(`기준값: ${module.utility.addComma(standardValue)} ${unit}`);
								}

								return tooltips;
							},
							labelTextColor: function(tooltipItem, chart) {
								return '#ffffff';
							},
						},
					},
					legend: {
						display: false,
						position: 'bottom',
						onClick: null,
					},
					responsive: true,
					maintainAspectRatio: false,
				}
			});


		},
		update: function(x, y, y2, unit, standards, decimalPoint)
		{
			let xTemp = x;
			let yTemp = y;
			let yTemp2 = y2;
			let standardTemp = standards;

			if (Array.isArray(xTemp) == false) {
				xTemp = [];
			}

			if (Array.isArray(yTemp) == false) {
				yTemp = [];
			}

			if (Array.isArray(yTemp2) == false) {
				yTemp2 = [];
			}

			if (Array.isArray(standardTemp) == false){
				standardTemp = [];
			}

			if (this._chart == null) {
				this._create(xTemp, yTemp, yTemp2, standardTemp);
				return;
			}

			this._chart.data.labels = xTemp;
			this._chart.data.datasets[0].data = yTemp;
			this._chart.data.datasets[1].data = yTemp2;
			this._chart.data.datasets[2].data = standardTemp;
			this.setUnit(unit);
			this.setDecimalPoint(decimalPoint);

			this._chart.update();
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
		setDecimalPoint: function(decimalPoint)
		{
			this._decimalPoint = decimalPoint;
		}
	};

	return controller;
}
