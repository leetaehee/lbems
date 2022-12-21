 let control;

$(document).ready(function() {
	let date = new Date();

	// 캘린더 설정
	createDatepicker($startDate);
	createDatepicker($endDate);

	module.utility.initYearSelect($startMonthYm, gServiceStartYm);
	module.utility.initYearSelect($endMonthYm, gServiceStartYm);
	module.utility.initYearSelect($startYearYm, gServiceStartYm);

	initSelect();

	// 처음 로딩 시 전체 전기를 디폴트로 한다.
	$checkboxElectric.prop("checked", true);
	// 주기는 일을 디폴트로 한다.
	$btnPeriodDaily.prop("checked", true);

	control = createControl();
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

    tempDateStr = $.datepicker.formatDate('yy-mm-dd', module.utility.getBaseDate());

	$id.val(tempDateStr);
}

function initSelect()
{
    let d = new Date();
    let n = d.getFullYear();
    let m = d.getMonth();

    let selected = " selected='selected'";

    let startMonthSelected = "";
    let endMonthSelected = "";

    let systemOpenYear = systemOpenStartDate.getFullYear(),
        systemOpenMonth = systemOpenStartDate.getMonth() + 2;

    // 현재월
    let endMonth = (m+1);

    /*
    for (let i = 0; i < 5; i++) {
    	if (n <= systemOpenYear) {
			startYearSelected = (n-i) === systemOpenYear ? selected : "";
		} else {
			startYearSelected = selected;
			if (i != 0) {
				startYearSelected = "";
			}
		}

        if (i != 0) {
            selected = "";
        }

        $startMonthYm.append("<option value='" + (n - i) + "'" + startYearSelected + ">" + (n - i) + "</option>");
        $endMonthYm.append("<option value='" + (n - i) + "'" + selected + ">" + (n - i) + "</option>");
        $startYearYm.append("<option value='" + (n - i) + "'" + selected + ">" + (n - i) + "</option>");
    }
     */

    selected = "";

    for (let i = 1; i < 13; i++) {
		if (n <= systemOpenYear) {
			startMonthSelected = (i === systemOpenMonth) ? selected : "";
			endMonthSelected = (i === endMonth) ? " selected='selected'" : "";
		} else {
			if (i === 12) {
				endMonthSelected =   " selected='selected'";
			}
		}

        // 시작월
        $startMonth.append("<option value='" + i + "'" + startMonthSelected + ">" + i + "</option>");
        // 종료월
        $endMonth.append("<option value='" + i + "'" + endMonthSelected + ">" + i + "</option>");
    }

    for (let i = 0; i < 24; i++) {
        $startHour.append("<option value='" + i + "'>" + i + " 시</option>");
    }
}

function createControl()
{
	let control = {
		selectedGrp : grp,
		selectedEnergyType : $checkboxReport.serialize(),
		selectedPeriod : period,
		selectedTimelineFlag : timelineFlag,
		selectedDifferDay: 1,
		request: function()
		{
			let self = control;

			let params = [];
            let data = [];
            
            let dates = self.getPeriodDateRange();

			data.push({
				'energy_type' : self.selectedEnergyType,
				'grp' : self.selectedGrp,
				'period' : self.selectedPeriod,
				'start' : dates['start'],
				'end' : dates['end'],
				'timeline_flag' : self.selectedTimelineFlag,
			});

			params.push(
				{name: 'requester', value: requester},
				{name: 'request', value: command},
				{name: 'params', value: JSON.stringify(data)}
			);

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: self.requestCallback,
				callbackParams: null,
				showAlert: true,
			};

			module.request(requestParams);
		},
		requestCallback: function (data, params)
		{
			/**
             * 총사용량 =  일자별로 사용량 모두 더해서 총 합을 구한다.
             * 평균사용량 = 총사용량 / 일수 (오늘, 어제는 항목수)
             * 최저사용량 = 층마다 최저 사용량을 구하고 추출
             * 최고사용량 = 층마다 최고 사용량을 구하고 추출
             *
             * [참조] 전기 kwh 1000 으로 나눈다.
             */

			let self = control;

			if (data === undefined) {
				return;
			}

			self.data = data;
			
			// 사용량 정보 출력
			self.updateUsedDataInfo();
        },
		updateUsedDataInfo: function () 
		{
			let self = control;

			let data = self.data;
			let floor = self.selectedGrp;
			let period = parseInt(self.selectedPeriod);
			let timelineFlag = self.selectedTimelineFlag;

			let totalUsed = 0,
                averageUsed = 0,
                maxUsed = 0,
                minUsed = 0,
				solarOutUsedSum = 0;

			let mode = "all";

			let solarData = new Array();
			let timelineTotal = new Array();
			let dates = new Array();
			let yearRange = new Array();

			let graphKeys = new Array();
			let graphValues = new Array();
			let detailLabels = new Array(); // 층별 그래프에서 동적 범주 리스트 
			let detailColors = new Array(); // 층별 그래프에서 동적 색상 리스트

			let startDate = new Date($startDate.val());
			let endDate = new Date($endDate.val());

			let differDay = module.utility.getDateSpan(startDate, endDate);

			if (period === 2 && timelineFlag === 0) {
				// 주기 선택이 일이면서 지난주, 지난달, 최근7일, 지난30일 등 일로 보여주는 경우 모드를 일시적으로 변경
				period = 1;
			}

			if (period === 2 && timelineFlag === 1 && differDay > 0) {
				// 주기 선택이 일이면서, 일 수가 1일 이상인 경우
				period = 1;
			}

			if (floor !== "all" && jQuery.inArray(floor.toLowerCase(), floors) >= 0) {
				mode = "floor";
			}

			if (data['solar_out'] !== undefined) {
				solarData = data['solar_out'];
				solarOutUsedSum = module.utility.getSumOfValues(Object.values(solarData));

				// 태양광 소비량은 삭제
				delete data['solar_out'];
			}

			const decimalPoint = module.utility.getDecimalPointFromDateType(period);

			$.each(data, function(key, values){
				let keys = Object.keys(data[key]);
                let items = Object.values(data[key]);

				let index = 0;
                $.each(values, function(itemKey, itemValue) {
					let timeValue = timelineTotal[index];
					if (timeValue === undefined) {
                       timeValue = 0;
					}

					if (period === 0 && timeValue > 0 && $.inArray(itemKey, yearRange) === -1) {
                       // 연도일 때는 해당 연도 추가
                       yearRange.push(itemKey);
					}
					timelineTotal[index] = timeValue + itemValue;

					index++; // 인덱스 증가
				});

				let charts = self.getChartLabels(keys, period);

				// 그래프 데이터 추가
				graphKeys.push(key);
				graphValues.push(items);
				dates.push(charts);

				// 동적라벨 생성 (층별 그래프는 층마다 에너지원이 달라 동적으로 생성함)
				if (mode == "floor") {
					detailLabels.push(energyTypeColors[key]['label_name']);
					detailColors.push(energyTypeColors[key]['color']);
				}
			});

			// 값이 있는 날짜 배열만 조회
            dates = self.updateDateArray(dates);

			// 사용량 총 합계
            totalUsed = module.utility.getSumOfValues(timelineTotal) - solarOutUsedSum;
            if (totalUsed !== undefined) {
                $labelTotalUsed.html(module.utility.addComma(totalUsed.toFixed(0)));
            }

            // 평균 사용량
            averageUsed = self.getAverageUsed(period, totalUsed, yearRange);
            if (averageUsed !== undefined) {
                $labelAverageUsed.html(module.utility.addComma(averageUsed.toFixed(0)));
            }

            // 최고사용량
            maxUsed = module.utility.getArrayMax(timelineTotal);
            if (maxUsed !== undefined) {
                $labelMaximumUsed.html(module.utility.addComma(maxUsed.toFixed(0)));
            }

            // 최저사용량
            minUsed = module.utility.getArrayMin(timelineTotal);
            if (minUsed !== undefined) {
                $labelMinimumUsed.html(module.utility.addComma(minUsed.toFixed(0)));
            }

			if (mode === "all") {
				// 전체 전기 사용량 및 그래프 출력 
				self.updateAllFloorGraph(dates, graphValues, decimalPoint);
			}

			floor = floor.toLowerCase();
			if (mode !== "all" && jQuery.inArray(floor.toLowerCase(), floors) >= 0) {
				// 층별 사용량 및 그래프 출력
				self.updateFloorGraph(dates, graphKeys, graphValues, detailLabels, detailColors, decimalPoint);
			}

			// 초기화
			//self.selectedTimelineFlag = 0;
		},
		updateAllFloorGraph: function (keys, values, decimalPoint)
		{
			chartFloorReport.clear();
			chartAllReport.clear();

			// 전체 층 그래프
			chartAllReport.update(keys, values, decimalPoint);
		},
		updateFloorGraph: function (dates, keys, values, labels, colors, decimalPoint)
		{
			chartAllReport.clear();
			chartFloorReport.clear();

			// 층에 에너지원별 그래프
			chartFloorReport.update(dates, keys, values, labels, colors, decimalPoint);
		},
		updateDateArray: function(arr) 
        {
            // 날짜 배열에 값이 없는 경우는 그래프 출력을 하지 않는다.
            let lDates = new Array();

            $.each(arr, function(key, value){
               if (value['labels'] !== undefined) {
                   lDates = value['labels'];
                   return lDates;
               }
            });

            return lDates;
        },
        getAverageUsed: function(period, used, ranges)
        {
            let self = control;
            let data = self.data;

            let average = 0;

            /**
             * 오늘/어제 1일 검색 할 경우는 항목수로 나눈다.
             * 그 외에는 일수로 나눈다.
             */
            let differDay = Object.keys(data).length;

			// 오늘,어제,1일 이상인 경우메나 아래 로직을 수행한다.
            if (period !== 2) {
                let dates = self.getPeriodDateRange();
                let start = dates['start'];
                let end = dates['end'];

                // 월 검색
                if (start.length === 6 && end.length === 6) {
                    start = new Date($startYearYm.val() + "-" + $startMonth.val() + "-01");
                    end = new Date($endMonthYm.val() + "-" + $endMonth.val() + "-01");

                    // 종료일 말일로 설정
                    end.setDate(30);
                }

                // 년 검색
                if (start.length === 4 && end.length === 4) {
                    let lastIndex = ranges.length;
                    if (lastIndex === 0) {
                        return;
                    }

                    let s = ranges[0];
                    let e = ranges[lastIndex-1];

                    let startIndex = s.substring(0, 4) + "-" + s.substring(5, 7);
                    let endIndex = e.substring(0,4) + "-" + e.substring(5, 7);

                    start = new Date(startIndex +"-01");
                    end = new Date(endIndex + "-31");

                    // 종료일 말일로 설정
                    end.setDate(30);
                }

                differDay = module.utility.getDateSpan(start, end) + 1;

				self.selectedDifferDay = differDay;
            }

            if (used > 0) {
				// 반올림 할 것
                average = Math.round(used/differDay);
            }

            return parseFloat(average);
        },
		getChartLabels: function(d, chart) 
		{
            let labels = [];
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
                        let year  = x.substring(0, 4);
                        let month = x.substring(4, 6);
                        let day   = x.substring(6, 8);
                        let label = month + "/" +day;
                        labels.push(label);
                    });
                break;
                case 2: 
					//day
                    d.forEach(x => {
                        let year = x.substring(0, 4);
                        let month = x.substring(4, 6);
                        let day = x.substring(6, 8);
                        let hour = x.substring(8, 10);
                        let label = hour + "시";
                        labels.push(label);
                    });
                break;
                case 3: 
					//day
                    d.forEach(x => {
                        let year = x.substring(0, 4);
                        let month = x.substring(4, 6);
                        let day = x.substring(6, 8);
                        let hour = x.substring(8, 10);
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
                        let day = x.substring(6, 8);
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

            if (period == 3) {
				// 분 검색
                let tempToday = new module.utility.getBaseDate();

                let lstartHour = $startHour.val();
                if (lstartHour < 10) {
                    lstartHour = "0" + lstartHour;
                }

                tempToday = $.datepicker.formatDate('yy-mm-dd', new Date(tempToday));

                start = tempToday + "" + lstartHour;
                end = tempToday + "" + lstartHour;
            }

            return {
                "start" : start,
                "end" : end
            }
        },
		updateCheckboxStatus: function($this)
		{
			let self = control;

			self.selectedGrp = grp;
			self.selectedEnergyType = $checkboxReport.serialize();
			self.selectedTimelineFlag = 0;

			let value = $this.val();
			let floor = "";

			// 선택한 항목이 몇층인지 추출
			floor = self.getFloor(value);
			if (floor === "") {
				// 유효성 실패 시 더 이상 진행하지 않는다. 
				return;
			}

			const $floorCheckboxGroup = $("#checkbox_" + floor + "_group");
			const $floorCheckboxLength = $(".checkbox_" + floor + ":checked").length;
			
			if ($floorCheckboxLength === 0) {
				// 아무것도 선택이 되지 않은 경우 전체전기에 체크되도록 한다.
                //$checkboxElectric.prop("checked", true);
				$floorCheckboxGroup.prop("checked", false);
				return;
			}

			if ($checkboxElectric.prop("checked") === true) {
				alert("전체 전기를 해제 후 선택 할 수 있습니다.");

				$this.prop("checked", false);
				return;
			}

			// 층별 선택 
			$floorCheckboxGroup.prop("checked", true);

			// 서로 다른 층이 선택이 되지 않도록 한다.
			let isMultipleFloorSelected = self.getMultipleFloorSelected();
			if (isMultipleFloorSelected === false) {
				alert("1개 층만 조회 할 수 있습니다.");

				$this.prop("checked", false);
				// 전체전기에 선택이 되도록한다.
                //$energyCheckboxGroup.prop("checked", false);

				return;
			}

			if (self.period === 2) {
                self.selectedTimelineFlag = 1;
            }

			self.selectedGrp = floor.toUpperCase();
			self.selectedEnergyType = $checkboxReport.serialize();
		},
		updateFloorCheckboxStatus: function($this, $event, floor)
		{
			// 체크박스를 눌렀을 때는 체크박스 그룹이 접히지 접히지 않는다
			$event.stopPropagation();

			let self = control;
			let period = self.selectedPeriod;

			self.selectedGrp = grp;
			self.selectedTimelineFlag = 0;
			self.selectedEnergyType = $checkboxReport.serialize();

			let checked = $this.prop("checked");

			const $floorCheckbox = $(".checkbox_" + floor);

			if ($checkboxElectric.prop("checked") === true) {
				alert("전체 전기를 해제 후 선택 할 수 있습니다.");

				$this.prop("checked", false);
				return;
			}

			let isMultipleFloorSelected = self.getMultipleFloorSelected();
			if (isMultipleFloorSelected === false) {
				alert("1개 층만 조회 할 수 있습니다.");

				$this.prop("checked", false);
				return;
			}

			if (period === 2) {
				self.selectedTimelineFlag = 1;
			}

			if (checked === true) {
				// 체크가 된 경우..
				$floorCheckbox.prop("checked", true);
			}

			if (checked === false) {
				// 체크가 되지 않은 경우..
				$floorCheckbox.prop("checked", false);
				
				/** 체크박스가 해제가 될 시 전체전기에 선택되도록 함*/
				/*
					$checkboxElectric.prop("checked", true);
					self.selectedGrp = "all";
					self.selectedEnergyType = $checkboxReport.serialize();
				*/

				return;
			}

			self.selectedGrp = floor.toUpperCase();
			self.selectedEnergyType = $checkboxReport.serialize();
		},
		updateAllFloorCheckboxStatus: function($this, floor)
		{
			let self = control;
			let period = self.selectedPeriod;

			self.selectedGrp = grp;
			self.selectedTimelineFlag = 0;
			self.selectedEnergyType = $checkboxReport.serialize();

			let checked = $this.prop("checked");

			if (checked === true) {
				// 체크가 된 경우..
				$itemCheckbox.prop("checked", false);
				$checkboxFloor.prop("checked", false);
			}

			if (period === 2) {
				self.selectedTimelineFlag = 1;
			}

			self.selectedGrp = "all";
			self.selectedEnergyType = $checkboxReport.serialize();
		},
		updateDatePeriodByPeriodButton: function($this, period)
		{
			let self = control;
			self.selectedTimelineFlag = 0;

			let tempToday = module.utility.getBaseDate();
			tempStartDate = tempEndDate = "";

			if (self.selectedPeriod != 2) {
				alert("주기를 '일'로 선택하세요.");
				return;
			}

			switch(period)
			{
				case "today":
					// 오늘
					tempStartDate = tempToday;
					tempEndDate = tempToday;
					break;
				case "yesterday":
					// 하루전
					tempDay = tempToday.getDate() - 1;
					
					tempStartDate = year + "-" + month + "-" + tempDay;
					tempEndDate = year + "-" + month + "-" + tempDay;
					break;
				case "last_week":
					// 일요일
					tempToday.setDate(tempToday.getDate() - 6);
					tempToday.setDate(tempToday.getDate() - tempToday.getDay());
					tempStartDate = year + "-" + (tempToday.getMonth()+1) + "-" + tempToday.getDate();

					// 토요일
					tempToday.setDate(tempToday.getDate() - tempToday.getDay() + 6);
					tempEndDate = year + "-" + (tempToday.getMonth()+1) + "-" + tempToday.getDate();

					break;
				case "7day":
					// 7일전
					tempDay = tempToday.getDate();
					tempEndDate  = year + "-" + month + "-" + tempDay;

					tempToday.setDate(tempDay-6);
					tempStartDate = tempToday;

					break;
				case "last_month" :
					// 1~말일 
					tempToday.setMonth(tempToday.getMonth()+1);
					tempToday.setDate(1)+1;
					tempStartDate = year + "-" + tempToday.getMonth() + "-" + tempToday.getDate();

					tempToday.setMonth(tempToday.getMonth()+1);
					tempToday.setDate(0);
					tempEndDate = year + "-" + (tempToday.getMonth()) + "-" + tempToday.getDate();

					break;
				case "30day" : 
					// 30일전
					tempToday.setDate(tempToday.getDate()-30);
					tempStartDate = year + "-" + (tempToday.getMonth()+1) + "-" + tempToday.getDate();

					// 오늘
					tempToday.setDate(tempToday.getDate()+30);
					tempEndDate = year + "-" + (tempToday.getMonth()+1) + "-" + tempToday.getDate();
					break;
			}

			if (period === "today" || period === "yesterday") {
				// 어제와 오늘은 시간으로 보여줄 것 (0-23시)
				self.selectedTimelineFlag = 1;
			}

			// 포맷에 맞게 변경 
			tempStartDate = $.datepicker.formatDate('yy-mm-dd', new Date(tempStartDate));
			tempEndDate = $.datepicker.formatDate('yy-mm-dd', new Date(tempEndDate));

			// 선택한 값으로 설정 
			$startDate.val(tempStartDate);
			$endDate.val(tempEndDate);
		},
		getMultipleFloorSelected: function()
		{	
			return $(".checkbox_floor:checked").length > 1 ? false : true;
		},
		getFloor: function(value)
		{
			let fcFloor = "";

			if (value.indexOf("1f") !== -1) {
				fcFloor = floors[0];
			} else if (value.indexOf("2f") !== -1) {
				fcFloor = floors[1];
			} else if (value.indexOf("3f") !== -1) {
				fcFloor = floors[2];
			} else if (value.indexOf("ph") !== -1) {
				fcFloor = floors[3];
			} else if (value.indexOf("0m") !== -1) {
				fcFloor = floors[4];
			}
			
			return fcFloor;
		},
		getRealPeriodNo: function(period)
		{
			fcPeriod = period;

			switch (fcPeriod)
			{
				case 5:
					// 기간 월 검색
					fcPeriod = 1
					break;
			}

			return fcPeriod;
		},
		onPeriodChangeClicked: function($this)
		{
			let self = control;

			self.selectedTimelineFlag = 0;

			let val = parseInt($this.val());
			let radioButtonSelector = $this.prop("id");

			let tempVal = self.getRealPeriodNo(val);
			let periodKeyName = periods[tempVal];

			// 사용자가 지정한 라디오버튼에 체크가 되도록 한다.
			$btnRadioPeriod.prop("checked", false);
			$("#" + radioButtonSelector).prop("checked", true);

			// 주기에 따라 검색항목 다르게 한다.
			$(".period_box").css("display", "none");
			$("#period_" + periodKeyName + "_box").css("display", "block");

			// 오늘, 어제, 지난주 등 검색 시 유용한 버튼은 일 검색시에만 보인다.
			if (val === 2) {
				self.selectedTimelineFlag = 1;

				$periodEnjoyBox.css("display", "block");
			} else {
				$periodEnjoyBox.css("display", "none");
			}
			
			// 주기에 따른 평균 사용량 구할 때 기준 값 변경
			$labelPeriodTimeUnit.html(periodTimeUnits[tempVal]);

			//  주기 변경 
			self.selectedPeriod = val;
		},
		onExcelClicked: function()
		{
			let self = control;

			// 날짜, 주기타입, 차이 일수 조회
            let dates = self.getPeriodDateRange();
            let period = self.selectedPeriod;
            let differDay = self.selectedDifferDay;
            let timelineFlag = self.selectedTimelineFlag;

            let data = [];
            let params = [];

            const decimalPoint = module.utility.getDecimalPointFromDateType(period);

			data.push({
                'start' : dates['start'],
                'end' : dates['end'],
                'period' : period,
                'differ_day' : differDay,
                'timeline_flag' : timelineFlag,
				'decimal_point': decimalPoint,
            });

			// 엑셀 다운로드는 ajax가 아니라 submit 으로 처리.. 
			$formParam.val(JSON.stringify(data));

			$formExcel.attr("action", "../http/index.php");
			$formExcel.submit();
		},
		onSearchClicked: function()
		{
			let self = control;
			let period = parseInt(self.selectedPeriod);

			if ($(".checkbox_report:checked").length < 1) {
				alert("체크박스 1개 이상 선택하세요.");
				return;
			}

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

				// 사이트 오픈일 체크
                let installDifferDay = systemOpenStartDate.getTime()-tempStartDate.getTime();
                if (installDifferDay > 0) {
                    alert("시작 월은 사이트 오픈 이전 월로 조회 할 수 없습니다.");
                    return;
                }

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
		updateCheckboxGroupStatus: function($this)
		{
			if ($this.hasClass('lnb_up')) {
				$this.parent().find('ul').slideDown('slow');
				$this.removeClass('lnb_up');
				$this.addClass('lnb_down');
			} else if ($this.hasClass('lnb_down')){
				$this.parent().find('ul').slideUp('slow');
				$this.removeClass('lnb_down');
				$this.addClass('lnb_up');
			}
		},
	};

	$btnSearch.on("click", function () {
		control.onSearchClicked();
	});

	$itemCheckbox.on("click", function () {
		// 개별 체크박스 항목 감지
		control.updateCheckboxStatus($(this));
	});

	$checkboxElectric.on("click", function () {
		// 전체 전기
		control.updateAllFloorCheckboxStatus($(this), "all");
	});

	$.each($floors, function(index, item){
		// 층 검색 체크박스 이벤트 등록
		item.on("click", function(event){
			control.updateFloorCheckboxStatus($(this), event, floors[index]);
		})
	});

	$btnSearchToday.on("click", function () {
		// 오늘 날짜로 설정 (시작일, 종료일 동일)
		control.updateDatePeriodByPeriodButton($(this), "today");
	});

	$btnSearchYesterday.on("click", function () {
		// 어제 날짜로 설정 (시작일, 종료일 동일)
		control.updateDatePeriodByPeriodButton($(this), "yesterday");
	});

	$btnSearchLastWeek.on("click", function () {
		// 지난주로 설정 (일요일~토요일로 설정)
		control.updateDatePeriodByPeriodButton($(this), "last_week");
	});

	$btnSearchLastMonth.on("click", function () {
		// 지난달로 설정 (1일부터 말일로 설정)
		control.updateDatePeriodByPeriodButton($(this), "last_month");
	});

	$btnSearch1Week.on("click", function () {
		// 최근 7일로 설정 (7일전 ~ 현재날짜로 설정);
		control.updateDatePeriodByPeriodButton($(this), "7day");
	});

	$btnSearch30Day.on("click", function () {
		// 30일로 설정 (30일전 ~ 현재날짜)
		control.updateDatePeriodByPeriodButton($(this), "30day");
	});

	$btnPaperExcel.on("click", function () {
		// 엑셀 버튼 
		control.onExcelClicked();
	});

	$btnRadioPeriod.on("click", function () {
		// 주기 변경 버튼
		control.onPeriodChangeClicked($(this));
	});

	$btnFoldFloorGroup.on("click", function () {
		// 층별 체크박스 그룹 접기 버튼 
		control.updateCheckboxGroupStatus($(this));
	});

	return control;
}