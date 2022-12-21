// 상단 차트
function topHorizontal(percent){
	$.each(percent, function(i){
		var item = $('.item-list li').eq(i);
		var per = parseInt(percent[i]) + '%';
        
		item.find('.percent span').text(per);
        
        if(percent[i] > 100)
            per = 100 + '%';
        
        item.find('.percent').css('width', per);
	});
}

// 메인 차트
function mainChart(item, data){
	var ctx = document.getElementById('mainChart').getContext('2d');
	var config = {
        type: 'line',
        data: {
            labels: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
            datasets: [
                {
                    label: '2017',
                    backgroundColor: '#1DC990',
                    borderColor: '#1DC990',
                    borderWidth: 1,
                    pointBorderWidth: 1,
                    fill: false,
                    data: data[0],
                },
                {
                    label: '2018',
                    backgroundColor: '#3FABA5',
                    borderColor: '#3FABA5',
                    borderWidth: 1,
					pointBorderWidth: 1,
                    fill: false,
                    data: data[1],
                },
            ]
        },
        options: {
            legend: {
				display: false,
            },
            responsive: false,
            scales: {
                xAxes: [{
                    display: true,
                    gridLines: {
                        display: false,
                    },
                    scaleLabel: {
                        display: true,
                    },
                    ticks: {
                        fontSize: 16,
                        fontFamily: 'Noto Sans KR'
                    }
				}],
                yAxes: [{
                    display: true,
					gridLines: {
						color: '#dfddde',
						borderDash: [8, 4],
					},
                    ticks: {
                        beginAtZero: true,
                        fontSize: 15,
                        fontFamily: 'Noto Sans KR',
                    }
                }]
            },
			tooltips: {
				callbacks: {
					title: function(tooltipItem, data) {
						return ' ' + tooltipItem[0]['yLabel'] + ' kwh ';
					},
					label: function(tooltipItem, data) {
						var years;
						if (tooltipItem['datasetIndex'] == 0) years = 2018;
						if (tooltipItem['datasetIndex'] == 1) years = 2017;
						return years + '년 ' + tooltipItem['xLabel'];
					},
				},
				backgroundColor: '#3198cd',
				titleFontSize: 22,
				titleFontColor: '#fff',
				bodyFontColor: '#fff',
				bodyFontSize: 14,
				displayColors: false
			}
        }
    };
	return new Chart(ctx, config);
}

// 메인 차트
function createMainChart(data){
	var ctx = document.getElementById('mainChart').getContext('2d');
	var config = {
        type: 'bar',
        data: {
            labels: ['1일', '', '', '', '5일', '', '', '', '', '10일', '', '',
					'', '', '15일', '', '', '', '', '20일', '', '', '', '',
					'25일', '', '', '', '', '30일', ''],
            datasets: [
                {
					radius: 0,
					yAxisID: 'elec',
                    //backgroundColor: '#1DC990',
                    //borderColor: '#1DC990',
					backgroundColor: 'rgba(247, 180, 0, 0.2)',
					borderColor: 'rgba(247, 180, 0, 0.2)',
                    borderWidth: 1,
                    pointBorderWidth: 1,
                    fill: false,
                    data: data['prevMonth'],
					pointStyle: 'rect',
                },
                {
					radius: 0,
					yAxisID: 'elec',
                    //backgroundColor: '#3FABA5',
                    //borderColor: '#3FABA5',
					backgroundColor: 'rgba(247, 180, 0, 0.7)',
                    borderColor: 'rgba(247, 180, 0, 0.7)',
                    borderWidth: 1,
					pointBorderWidth: 1,
                    fill: false,
                    data: data['curMonth'],
					pointStyle: 'rect',
                },
				{
					radius: 0,
					yAxisID: 'temp',
					type: 'line',
                    backgroundColor: '#C2C2C2',
                    borderColor: '#C2C2C2',
                    borderWidth: 1,
                    pointBorderWidth: 1,
                    fill: false,
                    data: data['prevTemp'],
					pointRadius:3
					//pointStyle: 'line',
					//borderDash: [10,5]
                },
                {
					radius: 0,
					yAxisID: 'temp',
					type: 'line',
                    backgroundColor: '#333333',
                    borderColor: '#333333',
                    borderWidth: 1,
					pointBorderWidth: 1,
                    fill: false,
                    data: data['curTemp'],
					pointRadius:3
					//pointStyle: 'line',
					//borderDash: [10,5]
                },
            ]
        },
        options: {
            legend: {
				display: false,
            },
            responsive: false,
            scales: {
                xAxes: [{
                    display: true,
                    gridLines: {
                        display: false,
                    },
                    scaleLabel: {
                        display: true,
                    },
                    ticks: {
                        fontSize: 16,
                        fontFamily: 'Noto Sans KR'
                    }
				}],
                yAxes: [
					{
						id: 'elec',
						display: true,
						gridLines: {
							color: '#dfddde',
							borderDash: [8, 4],
						},
						ticks: {
							beginAtZero: true,
							fontSize: 15,
							fontFamily: 'Noto Sans KR',
						}
					},
					{
						id: 'temp',
						display: false,
						position: 'right',
						ticks: {
							//beginAtZero: true,
							fontSize: 15,
							fontFamily: 'Noto Sans KR',
						}
					}
				]
            },
			tooltips: {
				callbacks: {
					/*
					title: function(tooltipItem, data) {
						return ' ' + tooltipItem[0]['yLabel'] + ' kwh ';
					},
					label: function(tooltipItem, data) {
						var years;
						if (tooltipItem['datasetIndex'] == 0) years = 2018;
						if (tooltipItem['datasetIndex'] == 1) years = 2017;
						return years + '년 ' + tooltipItem['xLabel'];
					},
					*/
				},
				backgroundColor: '#3198cd',
				titleFontSize: 22,
				titleFontColor: '#fff',
				bodyFontColor: '#fff',
				bodyFontSize: 14,
				displayColors: false
			}
        }
    };
	return new Chart(ctx, config);
}

// 도넛 차트
function doughnutChart(id, data) {
	var gradientColor1, gradientColor2;
	var ctx = document.getElementById(id).getContext('2d');
	if (id == 'UsingPrevYearElec') {
		gradientColor1 = '#20cb9f';
		gradientColor2 = '#347693';
	} else {
		gradientColor1 = '#1293e4';
		gradientColor2 = '#43398a';
	}
	var gradient = ctx.createLinearGradient(0, 0, 0, 200);
	gradient.addColorStop(0, gradientColor1);
	gradient.addColorStop(1, gradientColor2);
	var config = {
		type: 'doughnut',
		data: {
			datasets: [{
				data: [
					100-data,
					data
				],
				backgroundColor: [
					'#c8e5ff',
					gradient,
				],
			}],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			legend: {
				position: 'center',
				text: 'a'
			},
			animation: {
				animateScale: true,
				animateRotate: true
			},
			tooltips: {enabled: false},
		    hover: {mode: null},
		}
	};
	// afterDatasetsDraw: function
	return new Chart(ctx, config);
}

// 하단 계량기 차트
function botVertical(leng){
	var max = 0;
	for (var i = 0; i < leng.length; i++) {
		if (max < leng[i]) max = leng[i];
	}
	$.each(leng, function(i){
		var item = $('.trouble-percent li').eq(i);
		var per = (leng[i] / max) * 100 + '%';
		item.find('.percent').css('height', per);
		item.find('span').text(leng[i]);
	});
}
