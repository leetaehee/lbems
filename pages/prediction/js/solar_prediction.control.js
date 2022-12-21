let control;

$(document).ready(function() {
	control = createControl();
	control.requestChartLegend();
	control.request();
});

function createControl()
{
	let control = {
		selectedOption: BTN_START_INDEX,
		selectedPeriod: DEFAULT_PERIOD,
		selectedCurrentUsed: DEAULT_EMPTY_ARRAY,
		selectedPredictUsed: DEAULT_EMPTY_ARRAY,
		request: function()
		{
			let self = control;
			let params = DEAULT_EMPTY_ARRAY;
			let data = DEAULT_EMPTY_ARRAY;

			data.push({
				"energy_no" : self.selectedOption
			});

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

			// 사용량 합계 배열- 금일, 금주, 금월
			let currentUsedSums = new Array(0, 0, 0);
			let predictUsedSums = new Array(0, 0, 0);

			// 주기별 시작일~종료일 표시
			$labelDailyPeriod.html(periods['daily']['start'] + '~' + periods['daily']['end']);
			$labelWeeklyPeriod.html(periods['weekly']['start'] + '~' + periods['weekly']['end']);
			$labelMonthPeriod.html(periods['month']['start'] + '~' + periods['month']['end']);

			let fcIndex = 0;

			$.each(useds, function(key, items){
				let currentValue = parseInt(items['current']['data']);
				let predictValue = parseInt(items['predict']['data']);

				currentUsedSums[fcIndex] += currentValue;
				predictUsedSums[fcIndex] += predictValue;

				fcIndex++;
			});

			self.selectedCurrentUsed = currentUsedSums;
			self.selectedPredictUsed = predictUsedSums;

			self.updatePercentChart();
		},
		updatePercentChart: function()
		{
			let self = control;

			// 초기 로딩시에는 전기만..
			let period = self.selectedPeriod;
			let currentUseds = self.selectedCurrentUsed;
			let predictUseds = self.selectedPredictUsed;

			let index = self.getUseSumArrayIndex(period);
			if (index === "") {
				return;
			}

			let dailyCurrentUsed = currentUseds[1].toFixed(0);
			let dailyPredictUsed = predictUseds[1].toFixed(0);

			let weeklyCurrentUsed = currentUseds[2].toFixed(0);
			let weeklyPredictUsed = predictUseds[2].toFixed(0);

			let monthCurrentUsed = currentUseds[0].toFixed(0);
			let monthPredictUsed = predictUseds[0].toFixed(0);

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
			$labelGraphMonthUsed.css("width", self.updatePercentDown(monthCurrentPercent) + "%");
			$labelGraphMonthPredict.css("width", self.updatePercentDown(monthPredictPercent) + "%");

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
			let period = self.getUseSumArrayIndex(self.selectedPeriod);

			let periodKey = PERIOD_KEYS[period];

			let currentValue = useds[periodKey]['current']['data'];
			let predictValue = useds[periodKey]['predict']['data'];

			// 차트 소수점 조회
			const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

			let tempColumns = Array(currentNames[period], expectNames[period]);
			let tempCurrentUsed = Array(currentValue, 0);
			let tempPredictUsed = Array(0, predictValue);

			// 층별 예측 사용량 그래프
			charts[0].update(homeTypes, [currentValue], [predictValue], currentNames[period], expectNames[period], decimalPoint);

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

			let pRoomTags = [];
			let pRoomString = '';
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

				pRoomTags.push(`<p>${spanUsedColor[0].outerHTML} ${spanUsedLegend[0].outerHTML} (${spanUnit[0].outerHTML})</p>`);
				pOfficeTags.push(`<p>${spanTotalUsedColor[0].outerHTML} ${spanUsedLegend[0].outerHTML} (${spanUnit[0].outerHTML})</p>`);
			});

			pRoomString = pRoomTags.join('');
			pOfficeString = pOfficeTags.join('');

			$divChartLegendRoom.html(pRoomString);
			$divChartLegendOffice.html(pOfficeString);
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

	charts.forEach(function(item, index, array) {
		item.setUnit(unit);
	});

	return control;
}
