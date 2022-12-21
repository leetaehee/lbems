let control;

$(document).ready(function() {
    control = createControl();
    control.requestChartLegend();
    control.requestFloorTable();
	control.request();
});

function createControl() 
{
	let control = {
		selectedOption: BTN_START_INDEX,
		selectedPeriod: DEFAULT_PERIOD,
        request: function() 
		{
            let self = control;
            let params = DEAULT_EMPTY_ARRAY;
            let data = DEAULT_EMPTY_ARRAY;

			data.push({ name: 'option', value: self.selectedOption });

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: command},
				{name: "params", value: JSON.stringify(data)}
			);

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestCallback,
                callbackParams: null,
                showAlert: true
            };

            module.request(requestParams);
        },
		requestCallback: function(data, params)
		{
			let self = control;

			if (data == 'Error') {
				return;
			}

			self.data = data;

			control.updateUsedData();
        },
		updateUsedData: function()
		{
			let self = control;

			let periods = self.data['periods'];
			let useds = self.data['useds'];

			// 주기별 시작일~종료일 표시 
			$labelDailyPeriod.html(periods['daily']['start'] + '~' + periods['daily']['end']);
			$labelWeeklyPeriod.html(periods['weekly']['start'] + '~' + periods['weekly']['end']);
			$labelMonthPeriod.html(periods['month']['start'] + '~' + periods['month']['end']);

			$.each(useds, function(key, items){
				let currentData = items['current']['data'];
				let predictData = items['predict']['data'];

				$.each(currentData, function(floor, value){
					if (floor !== 'all') {
						let currentVal = value;
						if (currentVal < 1) {
							currentVal = 0;
						}

						$("#label_" + key + "_" + floor + "_used").html(module.utility.addComma(currentVal.toFixed(0)));
					}
				});

				$.each(predictData, function(floor, value){
					if (floor !== 'all') {
						let predictVal = value;
						if (predictVal < 1) {
							predictVal = 0;
						}

						$("#label_" + key + "_" + floor + "_predict").html(module.utility.addComma(predictVal.toFixed(0)));
					}
				});
			});

			self.updatePercentChart();
		},
		updatePercentChart: function() 
		{
			let self = control;

			// 초기 로딩시에는 전기만..
			let period = self.selectedPeriod;
			let useds = self.data['useds'];

			let index = self.getUseSumArrayIndex(period);
			if (index === "") {
				return;
			}

			let dailyCurrentUsed = useds['daily']['current']['data']['all'].toFixed(0);
			if (dailyCurrentUsed === 'NaN') {
				dailyCurrentUsed = 0;
			}

			let dailyPredictUsed = useds['daily']['predict']['data']['all'].toFixed(0);
			if (dailyPredictUsed === 'NaN') {
				dailyPredictUsed = 0;
			}

			let weeklyCurrentUsed = useds['weekly']['current']['data']['all'].toFixed(0);
			if (weeklyCurrentUsed === 'NaN') {
				weeklyCurrentUsed = 0;
			}

			let weeklyPredictUsed = useds['weekly']['predict']['data']['all'].toFixed(0);
			if (weeklyPredictUsed === 'NaN') {
				weeklyPredictUsed = 0;
			}

			let monthCurrentUsed = useds['month']['current']['data']['all'].toFixed(0);
			if (monthCurrentUsed === 'NaN') {
				monthCurrentUsed = 0;
			}

			let monthPredictUsed = useds['month']['predict']['data']['all'].toFixed(0);
			if (monthPredictUsed === 'NaN') {
				monthPredictUsed = 0;
			}

			// 금일
			let dailyCurrentPercent = module.utility.getValidPercent(dailyCurrentUsed/DAILY_USED_RATE*BASE_VAL);
			let dailyPredictPercent = module.utility.getValidPercent(dailyPredictUsed/DAILY_USED_RATE*BASE_VAL);

			$labelGraphDailyUsedText.html(module.utility.addComma(dailyCurrentUsed));
			$labelGraphDailyPredictText.html(module.utility.addComma(dailyPredictUsed));
			$labelGraphDailyUsed.css("width", dailyCurrentPercent + "%");
			$labelGraphDailyPredict.css("width", dailyPredictPercent + "%");

			// 금주
			let weeklyCurrentPercent = module.utility.getValidPercent(weeklyCurrentUsed/WEEKLY_USED_RATE*BASE_VAL);
			let weeklyPredictPercent = module.utility.getValidPercent(weeklyPredictUsed/WEEKLY_USED_RATE*BASE_VAL);

			$labelGraphWeeklyUsedText.html(module.utility.addComma(weeklyCurrentUsed));
			$labelGraphWeeklyPredictText.html(module.utility.addComma(weeklyPredictUsed));
			$labelGraphWeeklyUsed.css("width", weeklyCurrentPercent + "%");
			$labelGraphWeeklyPredict.css("width", weeklyPredictPercent + "%");

			// 금월
			let monthCurrentPercent = module.utility.getValidPercent(monthCurrentUsed/MONTH_USED_RATE*BASE_VAL);
			let monthPredictPercent = module.utility.getValidPercent(monthPredictUsed/MONTH_USED_RATE*BASE_VAL);

			$labelGraphMonthUsedText.html(module.utility.addComma(monthCurrentUsed));
			$labelGraphMonthPredictText.html(module.utility.addComma(monthPredictUsed));
			$labelGraphMonthUsed.css("width", monthCurrentPercent + "%");
			$labelGraphMonthPredict.css("width", monthPredictPercent + "%");

			self.updateChart();
		},
		updatePercentDown: function(val)
		{
			// 퍼센트 범위를 줄임- 퍼블리싱이 정상적으로 될 경우 이 함수는 제거.. 100% 일 때 글자가 떨어짐
			let percent = val;

			if (percent >= 100) {
				percent = val - 25;
			}

			return percent;
		},
		updateChart: function()
		{
			let self = control;

			let dateType = self.selectedPeriod;
			let useds = self.data['useds'];
			let period = self.getUseSumArrayIndex(dateType);

			let periodKey = PERIOD_KEYS[period];

			let currentData = useds[periodKey]['current']['data'];
			let predictData = useds[periodKey]['predict']['data'];

			let currents = [];
			let predicts = [];
			let homeTypeLabels = [];

			let keys = Object.keys(currentData);
			let currentValues = Object.values(currentData);
			let predictValues = Object.values(predictData);

			// 차트 소수점 조회
			const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

			currentValues.splice(0, 1);
			predictValues.splice(0, 1);

			let floorKeyData = CONFIGS['floor_key_data'];

			for (let i = 0; i < keys.length; i++) {
				// 사용량
				let currentVal = 0;
				let predictVal = 0;
				let key = keys[i];

				if (currentValues[i] !== undefined && currentValues[i] > 0) {
					currentVal = currentValues[i];
				}
				if (predictValues[i] !== undefined &&  predictValues[i] > 0) {
					predictVal = predictValues[i];
				}

				currents.push(currentVal);
				predicts.push(predictVal);

				if (keys[i] !== 'all') {
					homeTypeLabels.push(floorKeyData[key]);
				}
			}

			let tempColumns = Array(currentNames[period], expectNames[period]);
			let tempCurrentUsed = Array(currentData['all'], 0);
			let tempPredictUsed = Array(0, predictData['all']);

			// 층별 예측 사용량 그래프 
			charts[0].update(homeTypeLabels, currentValues, predictValues, currentNames[period], expectNames[period], decimalPoint);

			// 사무소 예측 사용량 
			charts[1].update(tempColumns, tempCurrentUsed, tempPredictUsed, '', '', decimalPoint);
		},
		onPeriodButtonClicked: function($this, index) 
		{
			let self = control;

			self.selectedPeriod = index;

			$("#btn_period > button").removeClass();
			$this.addClass("on");

			self.updatePercentChart();
		},
		getUseSumArrayIndex: function(period)
		{
			let index = "";

			// 금일, 금주, 금월
			switch (period) {
				case 2:
					// 금일
					index = 0;			
					break;
				case 5:
					// 금주
					index = 1;
					break;
				case 1:
					// 금월
					index = 2;
					break;
			}

			return index;
		},
		requestChartLegend: function()
		{
			const keys = Object.keys(CHART_COLORS);
			const floorColorValues = Object.values(CHART_COLORS['floor_type']);
			const totalColorValues = Object.values(CHART_COLORS['total_type']);

			let pFloorTags = [];
			let pFloorString = '';
			let pOfficeTags = [];
			let pOfficeString = '';

			$.each(keys, function (index, value) {
				// 층별 예측 사용량 범주 동적 추가
				let spanUsedColor = $("<span></span>");
				spanUsedColor.css('background-color', "rgb(" + floorColorValues[index] + ")");

				// 사무소 예측 사용량 범주 동적 추가
				let spanTotalUsedColor = $("<span></span>");
				spanTotalUsedColor.css('background-color', "rgb(" + totalColorValues[index] + ")");

				let spanUsedLegend = $("<span></span>")
					.attr('class', 'label_' + LEGEND_KEYS[index] + '_useage')
					.html(DEFAULT_LEGEND[index]);

				let spanUnit = $("<span></span>")
					.attr('class', 'label_unit')
					.html('kWh');

				pFloorTags.push(`<p>${spanUsedColor[0].outerHTML} ${spanUsedLegend[0].outerHTML} (${spanUnit[0].outerHTML})</p>`);
				pOfficeTags.push(`<p>${spanTotalUsedColor[0].outerHTML} ${spanUsedLegend[0].outerHTML} (${spanUnit[0].outerHTML})</p>`);
			});

			pFloorString = pFloorTags.join('');
			pOfficeString = pOfficeTags.join('');

			$divChartLegendFloor.html(pFloorString);
			$divChartLegendOffice.html(pOfficeString);
		},
		requestFloorTable: function()
		{
			const FLOOR_INFOS = CONFIGS['floor_key_data'];
			const ELECTRIC_FLOOR_KEYS = Object.values(CONFIGS['electric_floor_key_data']);
			const FLOORS = CONFIGS['floor'];



			$.each(PERIOD_KEYS, function(periodIndex, item){
				let trTags = [];
				let trString = '';

				let periodKey = PERIOD_KEYS[periodIndex];
				let $tbody = $("#tbody_" + periodKey);

				$.each(FLOORS, function(floorIndex, value) {
					let floorName = FLOOR_INFOS[value];
					if (jQuery.inArray(floorName, ELECTRIC_FLOOR_KEYS) === -1) {
						return true;
					}

					let $spanCurrentUsed = $("<span></span>").attr({
						id: 'label_' + periodKey + '_' + value + '_used'
					}).html(0);

					let $spanPredictUsed = $("<span></span>").attr({
						id: 'label_' + periodKey + '_' + value + '_predict'
					}).html(0);

					trTags.push("<tr>");
					trTags.push("<td>" + floorName + "</td>");
					trTags.push("<td>" + $spanCurrentUsed[0].outerHTML + " kWh</td>");
					trTags.push("<td>" + $spanPredictUsed[0].outerHTML + " kWh</td>");
					trTags.push("</tr>");
				});

				// 배열을 문자열로 변환
				trString = trTags.join('');

				let $tbodyId = $tbody.prop('id');

				// 추가
				$("#" + $tbodyId + " > tr").remove();
				$tbody.append(trString);
			});
		},
	};

	$btnDaily.on("click", function() {
		control.onPeriodButtonClicked($(this), 2);
	});
	
	$btnWeekly.on("click", function() {
		control.onPeriodButtonClicked($(this), 5);
	});
	
	$btnMonth.on("click", function() {
		control.onPeriodButtonClicked($(this), 1);
	});

	return control;
}
