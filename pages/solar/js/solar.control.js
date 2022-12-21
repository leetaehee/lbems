let control;

$(document).ready(function() {
	// 캘린더 설정
	createDatepicker($startDate);
	createDatepicker($endDate);

	module.utility.initYearSelect($startMonthYm, gServiceStartYm);
	module.utility.initYearSelect($endMonthYm, gServiceStartYm);
	module.utility.initYearSelect($startYearYm, gServiceStartYm);

	initSelect();

	// 주기는 일을 디폴트로 한다.
	$btnPeriodDaily.prop('checked', true);

	control = createControl();
	control.requestChartLegend();
	control.request();
});

function createDatepicker($id) 
{
    $id.datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        showMonthAfterYear: true,
		maxDate: 0,
    });

    tempDateStr = $.datepicker.formatDate('yy-mm-dd', new module.utility.getBaseDate());

	$id.val(tempDateStr);
}

function initSelect()
{
    let d = new Date();
    let m = d.getMonth();

    let selected = " selected='selected'";

    // 현재월
    let endMonth = (m+1);

    selected = "";

    for (let i = 1; i < 13; i++) {
        // 시작월
		selected = (i === 1) ? " selected='selected'" : "";
        $startMonth.append("<option value='" + i + "'" + selected + ">" + i + "</option>");
        // 종료월
		selected = (i === endMonth) ? " selected='selected'" : "";
        $endMonth.append("<option value='" + i + "'" + selected + ">" + i + "</option>");
    }
}

function createControl() 
{
    let control = {
		selectedPeriod: DEFAULT_PERIOD,
		selectedLoading: DEFAUT_LOADING,
		request: function() 
		{
            let self = control;

            let params = [];
            let data = [];

			let dates = self.getPeriodDateRange();

			data.push({name: 'is_loading', value: self.selectedLoading});
			data.push({name: 'start', value: dates['start']});
			data.push({name: 'end', value: dates['end']});
			data.push({name: 'date_type', value: $(".radio_period:checked").val()});

			params.push(
				{name: "requester", value: requester},
				{name: "request", value: command},
				{name: "params", value: JSON.stringify(data)}
			);

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: self.requestCallback,
				callbackParams: [],
			};

			module.request(requestParams);
		},
		requestCallback: function(data, params) 
		{
            let self = control;
			self.data = data;

			if (self.selectedLoading === 1) {
				// 검색기준이 아니라 금일,금월,금년 기준으로 보여주는 차트 
				self.updateSolarMainChart();
			}

			// 검색시 상세그래프 표현
			self.updateSolarPeriodChart();

			self.selectedLoading = 0;
		},
		updateSolarMainChart: function() 
		{
			let self = control;
			let data = self.data['solar_used'];
			let solarEfficiency = self.data['solar_efficiency'];

			// 0 이하인 경우 예외처리
			solarEfficiency = (solarEfficiency < 0) ? 0 : solarEfficiency;

			let solarRateSum = 0;
			let productionRate = 0;
			let consumptionRate = 0;
			let efficiencyPercent = 0;
			let percentSum = 0;

			// 금일
			let dailySolarProductionSum = data['daily']['in'];
			let dailySolarConsumptionSum = data['daily']['out'];
			$labelSolarDailyProductionUsed.html(module.utility.addComma(dailySolarProductionSum));
			$labelSolarDailyConsumptionUsed.html(module.utility.addComma(dailySolarConsumptionSum));

			// 금월
			let monthSolarProductionSum = data['month']['in'];
			let monthSolarConsumptionSum = data['month']['out'];
			$labelSolarMonthProductionUsed.html(module.utility.addComma(monthSolarProductionSum));
			$labelSolarMonthConsumptionUsed.html(module.utility.addComma(monthSolarConsumptionSum));

			// 금년
			let yearSolarProductionSum = data['year']['in'];
			let yearSolarConsumptionSum = data['year']['out'];
			$labelSolarYearProductionUsed.html(module.utility.addComma(yearSolarProductionSum));
			$labelSolarYearConsumptionUsed.html(module.utility.addComma(yearSolarConsumptionSum));

			// 태양광 소비 생산 그래프 (월)
			$("#label_solar_production_used").html(module.utility.addComma(monthSolarProductionSum));
			$("#label_solar_consumption_used").html(module.utility.addComma(monthSolarConsumptionSum));
			
			// 태양광 소비 생산량 비율 계산 
			percentSum = Math.round((monthSolarProductionSum + monthSolarConsumptionSum)/100);
			productionRate = Math.round(monthSolarProductionSum/percentSum);
			consumptionRate = Math.round(monthSolarConsumptionSum/percentSum);

			if ((productionRate + consumptionRate) > 0) {
				solarGraph.clear();
				solarGraph.update([0, productionRate, consumptionRate]);
			}

			// 실시간 발전효율
			$labelEfficiencyPercent.html(module.utility.addComma(solarEfficiency.toFixed(1)) + "%");

			// 발전효율 그래프
			efficiencyGraph.clear();
			efficiencyGraph.update([solarEfficiency, 100-solarEfficiency]);
		},
		updateSolarPeriodChart: function()
		{
			let self = control;
			let solarPeriodUseds = self.data['solar_period_used'];

			let solarInUseds = solarPeriodUseds['in'];
			let solarOutUseds = solarPeriodUseds['out'];

			let period = parseInt($(".radio_period:checked").val());
			let tempPeriod = period;

			let startDate = new Date($startDate.val());
			let endDate = new Date($endDate.val());

			let differDay = module.utility.getDateSpan(startDate, endDate);
			if (tempPeriod === 2 && differDay > 0) {
				// 금일이면서 1일 이상 검색 하는 경우.
				tempPeriod = 1;
			}

			if (solarInUseds === undefined || solarInUseds === null) {
				return;
			}

			if (solarOutUseds === undefined || solarOutUseds === null) {
				return;
			}

			let keys = Object.keys(solarInUseds);
			let inUseds = Object.values(solarInUseds);
			let outUseds = Object.values(solarOutUseds);
			let charts = self.getChartLabels(keys, tempPeriod);

			let decimalPoint = module.utility.getDecimalPointFromDateType(period);

			// 세부그래프 출력
			detailGraph.clear();
			detailGraph.update(charts['labels'], inUseds, outUseds, decimalPoint);
		},
		onSearchClicked: function()
		{
			let self = control;
			let period = self.selectedPeriod;

			if (period === 2) {
				// 금일 
				let startDate = $startDate.val();
				let endDate = $endDate.val();

				let differDay = module.utility.getDateSpan(startDate, endDate);

				if (differDay > 31) {
					// 28일, 30일, 31일 어떻게 대처할것인가? 우선 31일로.. 
					alert("주기 '일'로 검색 할 경우 31일까지 검색 가능합니다.");
					return;
				}

				if (startDate === endDate) {
					// 시작일과 종료일이 같으면 0시-23시로 보여준다.
					self.selectedTimelineFlag = 1;
				}
				
				if (startDate > endDate) {
					// 시작일은 종료일보다 늦을 수 없다. 
					alert("시작일은 종료일보다 이전 또는 같은 일이어야 합니다.");
					return;
				}
			}

			if (period === 5) {
				// 금월
				let tempStartDate = new Date($startMonthYm.val(), $startMonth.val()-1, 1);
				let tempEndDate = new Date($endMonthYm.val(), $endMonth.val()-1, 31);
				let tempEndCompare = new Date($endMonthYm.val(), $endMonth.val()-1, 1);

				let differDay = module.utility.getDateSpan(tempStartDate, tempEndDate);

				if (tempStartDate > tempEndDate) {
					alert("시작월은 종료일 보다 이전이어야 합니다.");
					return;
				}

				if (tempStartDate.getTime() === tempEndCompare.getTime()) {
					alert("시작월과 종료일이 같습니다. '일'로 검색하세요.");
					return;
				}

				if (differDay > 365) {
					alert("1년 이상은 검색 할 수 없습니다.");
					return;
				}
			}

			self.request();
		},
		onPeriodChangeClicked: function($this)
		{
			let self = control;

			let val = $this.val();
			let radioButtonSelector = $this.prop("id");

			let tempVal = self.getRealPeriodNo(val);
			let periodKeyName = periods[tempVal];

			// 사용자가 지정한 라디오버튼에 체크가 되도록 한다.
			$btnRadioPeriod.prop("checked", false);
			$("#" + radioButtonSelector).prop("checked", true);

			// 주기에 따라 검색항목 다르게 한다.
			$(".period_box").css("display", "none");
			$("#period_" + periodKeyName + "_box").css("display", "block");

			self.selectedPeriod = parseInt(val);
		},
		getRealPeriodNo: function(period)
		{
			let fcPeriod = period;

			switch (fcPeriod)
			{
				case '5':
					// 기간 월 검색
					fcPeriod = 1
					break;
			}

			return fcPeriod;
		},
		getPeriodDateRange: function ()
        {
            let self = control;
            
            let period = self.selectedPeriod;
            let start, end;

            if (period == 0) {
				// 년도 검색
                start = end = $startYearYm.val();
            }

            if (period == 5) {
				// 기간별로 월 검색
                let lStartMonth = $startMonth.val();
                let lEndMonth = $endMonth.val();

                if (lStartMonth < 10) {
                    lStartMonth = "0" + lStartMonth;
                }

                if (lEndMonth < 10) {
                    lEndMonth = "0" + lEndMonth;
                }

                start = $startMonthYm.val() + "" + lStartMonth; 
                end = $endMonthYm.val() + "" + lEndMonth;
            }

            if (period == 2) {
				// 일 검색
                start = $startDate.val();
                end = $endDate.val();
            }

            return {
                'start' : start,
                'end' : end
            }
        },
		getChartLabels: function(d, chart)
		{
            let labels   = [];
            let tooltips = [];

            switch(chart) {
                case 0: 
					//year
                    d.forEach(x => {
                        let label = x.substring(4, 6);
                        labels.push(label + "월");
                    });
                	break;
                case 1: 
					//month
                    d.forEach(x => {
                        let month = x.substring(4, 6);
                        let day   = x.substring(6, 8);
                        let label = month + "/" +day;
                        labels.push(label);
                    });
                	break;
                case 2:
					//day
                    d.forEach(x => {
                        let hour = x.substring(8, 10);
                        let label = hour + "시";
                        labels.push(label);
                    });
                	break;
                case 3: 
					//day
                    d.forEach(x => {
                        let minute = x.substring(10, 12);
                        let label = minute + "분";
                        labels.push(label);
                    });
                	break;
				case 5:
					// 기간별 월 검색
                    d.forEach(x => {
                        let year = x.substring(2, 4);
                        let month = x.substring(4, 6);
                        let label = year + "-" + month;
                        labels.push(label);
                    });
					break;
                default:
                	break;
            }

            return { 
				labels: labels, 
				tooltips: tooltips
			};
        },
		onExcelClicked: function()
		{
			let self = control;

			// 날짜, 주기타입, 차이 일수 조회
			let dates = self.getPeriodDateRange()

			let dateType = self.selectedPeriod;
			let data = [];

			const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

			data.push({'name': 'start', 'value': dates['start']});
			data.push({'name': 'end', 'value': dates['end']});
			data.push({'name': 'date_type', 'value': dateType});
			data.push({'decimal_point': 'decimal_point', value: decimalPoint});

			// 엑셀 다운로드는 ajax가 아니라 submit 으로 처리..
			$formParam.val(JSON.stringify(data));

			$formExcel.attr("action", "../http/index.php");
			$formExcel.submit();
		},
		requestChartLegend: function()
		{
			const solarTypeColors = colorData['solar_compare_color'];
			const keys = Object.keys(solarTypeColors);
			const colors = Object.values(solarTypeColors);

			let pTags = [];
			let spanTag = [];
			let pString = '';
			let spanString = '';

			$.each(colors, function (index, value) {
				let spanSolarColor = $("<span></span>");
				spanSolarColor.css('background-color', "rgb(" + colors[index] + ")");

				let spanSolarUnit = $("<span></span>")
					.attr('class', 'label_solar_unit')
					.html('kWh');

				let spanSolarUsed = $("<span></span>").attr({
					'class' : 'fs15',
					'id' : 'label_solar_' + keys[index] + '_used',
				}).html(0);

				pTags.push(`<p>${spanSolarColor[0].outerHTML} ${SOLAR_LABELS[index]}(${spanSolarUnit[0].outerHTML})</p>`);
				spanTag.push(`${spanSolarColor[0].outerHTML}${SOLAR_LABELS[index]} : ${spanSolarUsed[0].outerHTML} kWh`);
			});

			pString = pTags.join('');
			$divChartLegendSolar.html(pString);

			$("#year_in_str").append(spanTag[0]);
			$("#year_out_str").append(spanTag[1]);
		},
	};

	$btnSearch.on("click", function () {
		control.onSearchClicked();
	});

	$btnRadioPeriod.on("click", function () {
		// 주기 변경 버튼
		control.onPeriodChangeClicked($(this));
	});

	$btnSolarExcel.on("click", function () {
		// 엑셀 버튼
		control.onExcelClicked();
	});

	return control;
}