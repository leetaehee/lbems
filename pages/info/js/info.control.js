let control;

$(document).ready(function() {
    createDatepicker();
    control = createControl();
    control.requestChartLegend();

    let buildingManager = module.BuildingManager();
    buildingManager.setEnergyKey(defaultEnergyKey);
    buildingManager.request(btnStartIndex);

    let makeEnergyButton = module.makeEnergyButton(CONFIGS['auto_loading']);
    makeEnergyButton._callback = control.onMenuButtonClicked;
    makeEnergyButton.request();
});

function createDatepicker()
{
    $dateSelect.datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        showMonthAfterYear: true,
        maxDate: 0,
    });

    let d = $.datepicker.formatDate('yy-mm-dd', module.utility.getBaseDate());

    $dateSelect.val(d);
}

function createControl()
{
    let control = {
        selectedOption: btnStartIndex,
        selectedChartOption: chartOption,
        selectedBuildingDong: defaultDong,
        selectedBuildingFloor: defaultFloor,
        selectedBuildingRoom: defaultRoom,
        selectedEnergyKey: defaultEnergyKey,
        selectedDateType: PERIOD_STATUS,
        selectedMenu: '',
        clearLabels: function()
        {
            $labelUsageNow.html(0);
            $labelUsageLast.html(0);
            $labelDiff.html(0);
            $labelPercentValueLowTop.html(0);
            $labelPercentValueMidTop.html(0);
            $labelPercentValueMaxTop.html(0);
            $labelPrevLowSum.html(0);
            $labelPrevMidSum.html(0);
            $labelPrevMaxSum.html(0);
            $labelAverage.html(0);
            $labelPercent.html(0);
        },
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'option', value: self.selectedOption });
            data.push({ name: 'date', value: $dateSelect.val() });
            data.push({ name: 'date_type', value: self.selectedDateType });
            data.push({ name: 'floor_type', value: self.selectedBuildingFloor });
            data.push({ name: 'room_type', value: self.selectedBuildingRoom });
            data.push({ name: 'energy_key', value: self.selectedEnergyKey });
            data.push({ name: 'dong_type', value: self.selectedBuildingDong });

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: command},
                {name: "params", value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestCallback,
                callbackParams: self.selectedDateType,
                showAlert: true
            };

            self.clearChart();
            self.changeUnit();

            module.request(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;

            self.data = data;
            self.updateChart();
        },
        clearChart: function()
        {
            let self = control;
            let len  = charts.length;

            let chartOption = self.selectedChartOption;

            for (let i = chartOption; i < len; i++) {
                charts[i].clear();
            }

            charts[0].update();
            charts[1].update([100, 0, 0, 0]);
            charts[2].update([0, 200]);
        },
        updateStatusGraph: function(data)
        {
            let self = control;
            let period = self.selectedDateType;

            let len = data.length;

            let labels = [];
            let vals = [];
            let standards = [];
            let color = [];
            let graphName = [];

            for (let i = 0; i < len; i++) {
                let type = data[i]['type'];

                if (type == 0) {
                    // 경부하
                    color[i] = `rgba(${statusChartColor[2]}, 1)`;
                    graphName[i] = '경부하';
                }

                if (type == 1) {
                    // 중부하
                    color[i] = `rgba(${statusChartColor[1]}, 1)`;
                    graphName[i] = '중부하';
                }

                if (type == 2) {
                    // 최대부하
                    color[i] = `rgba(${statusChartColor[0]}, 1)`;
                    graphName[i] = '최대부하';
                }

                if (type == 3) {
                    // 정상
                    color[i] = `rgba(${statusChartColor[3]}, 1)`;
                    graphName[i] = '정상부하';
                }

                labels[i] = data[i]['date'];
                vals[i] = data[i]['val'];
                standards[i] = data[i]['standard'];
            }

            let dates = self.getChartLabels(labels, period);
            let decimalPoint = module.utility.getDecimalPointFromDateType(period);

            charts[0].update(dates['labels'], dates['labels'], vals, standards, graphName, [], color, decimalPoint);
        },
        getChartLabels: function(d, chart)
        {
            let labels = [];
            let tooltips = [];

            chart = parseInt(chart);

            switch(chart)
            {
                case 0:
                    //year
                    $.each(d, function(index, item) {
                        let date = new String(item);

                        let label = date.substring(4, 6);
                        labels.push(label + "월");
                    });
                    break;
                case 1:
                    //month
                    $.each(d, function(index, item) {
                        let date = new String(item);

                        let day = date.substring(6, 8);
                        labels.push(day + "일");
                    });
                    break;
                case 2:
                    //day
                    $.each(d, function(index, item) {
                        let date = new String(item);

                        let hour = date.substring(8, 10);
                        let label = hour + "시";

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
        updateIndicator: function(indicators, sumData)
        {
            let self = control;

            let lowDifferPercent = indicators['low']['differ_percent'];
            let midDifferPercent = indicators['mid']['differ_percent'];
            let maxDifferPercent = indicators['max']['differ_percent'];

            let lowPrevPeriodSum = indicators['low']['prev_period_sum'];
            let midPrevPeriodSum = indicators['mid']['prev_period_sum'];
            let maxPrevPeriodSum = indicators['max']['prev_period_sum'];

            let lowCurrentSum = sumData['low'];
            let midCurrentSum = sumData['mid'];
            let maxCurrentSum = sumData['max'];

            $labelUsageNow.html(module.utility.addComma(lowCurrentSum.toFixed(0)));
            $labelUsageLast.html(module.utility.addComma(midCurrentSum.toFixed(0)));
            $labelDiff.html(module.utility.addComma(maxCurrentSum.toFixed(0)));

            $labelPercentValueLowTop.html(module.utility.addComma(lowDifferPercent.toFixed(0)));
            $labelPercentValueMidTop.html(module.utility.addComma(midDifferPercent.toFixed(0)));
            $labelPercentValueMaxTop.html(module.utility.addComma(maxDifferPercent.toFixed(0)));

            let $lowRemoveClass = self.getPercentClass($labelPercentLowTop);
            let $lowTextColorRemoveClass = self.getPercentColorClass($labelPercentColorLowTop);
            let $lowValueRemoveClass= self.getPercentColorClass($labelPercentValueLowTop);

            $lowRemoveClass = $lowRemoveClass === '' ? 'percent_zero' : $lowRemoveClass;
            $lowTextColorRemoveClass = $lowTextColorRemoveClass === '' ? 'fcGray' : $lowTextColorRemoveClass;
            $lowValueRemoveClass = $lowValueRemoveClass === '' ? 'fcGray' : $lowValueRemoveClass;

            if (lowCurrentSum <= lowPrevPeriodSum) {
                if (lowCurrentSum == 0 && (lowCurrentSum == lowPrevPeriodSum)) {
                    $labelPercentLowTop.removeClass($lowRemoveClass).addClass('percent_zero');
                    $labelPercentColorLowTop.removeClass($lowTextColorRemoveClass).addClass('fcGray');
                    $labelPercentValueLowTop.removeClass($lowValueRemoveClass).addClass('fcGray');
                } else {
                    $labelPercentLowTop.removeClass($lowRemoveClass).addClass('percent_down');
                    $labelPercentColorLowTop.removeClass($lowTextColorRemoveClass).addClass('fcprimary');
                    $labelPercentValueLowTop.removeClass($lowValueRemoveClass).addClass('fcprimary');
                }
            } else {
                $labelPercentLowTop.removeClass($lowRemoveClass).addClass('percent_up');
                $labelPercentColorLowTop.removeClass($lowTextColorRemoveClass).addClass('fcRed');
                $labelPercentValueLowTop.removeClass($lowValueRemoveClass).addClass('fcRed');
            }

            let $midRemoveClass = self.getPercentClass($labelPercentMidTop);
            let $midTextColorRemoveClass = self.getPercentColorClass($labelPercentColorMidTop);
            let $midValueRemoveClass= self.getPercentColorClass($labelPercentValueMidTop);

            $midRemoveClass = $midRemoveClass === '' ? 'percent_zero' : $midRemoveClass;
            $midTextColorRemoveClass = $midTextColorRemoveClass === '' ? 'fcGray' : $midTextColorRemoveClass;
            $midValueRemoveClass = $midValueRemoveClass === '' ? 'fcGray' : $midValueRemoveClass;

            if (midCurrentSum <= midPrevPeriodSum) {
                if (midCurrentSum == 0 && (midCurrentSum == midPrevPeriodSum)) {
                    $labelPercentMidTop.removeClass($midRemoveClass).addClass('percent_zero');
                    $labelPercentColorMidTop.removeClass($midTextColorRemoveClass).addClass('fcGray');
                    $labelPercentValueMidTop.removeClass($midValueRemoveClass).addClass('fcGray');
                } else {
                    $labelPercentMidTop.removeClass($midRemoveClass).addClass('percent_down');
                    $labelPercentColorMidTop.removeClass($midTextColorRemoveClass).addClass('fcprimary');
                    $labelPercentValueMidTop.removeClass($midValueRemoveClass).addClass('fcprimary');
                }
            } else {
                $labelPercentMidTop.removeClass($midRemoveClass).addClass('percent_up');
                $labelPercentColorMidTop.removeClass($midTextColorRemoveClass).addClass('fcRed');
                $labelPercentValueMidTop.removeClass($midValueRemoveClass).addClass('fcRed');
            }

            let $maxRemoveClass = self.getPercentClass($labelPercentMaxTop);
            let $maxTextColorRemoveClass = self.getPercentColorClass($labelPercentColorMaxTop);
            let $maxValueRemoveClass= self.getPercentColorClass($labelPercentValueMaxTop);

            $maxRemoveClass = $maxRemoveClass === '' ? 'percent_zero' : $maxRemoveClass;
            $maxTextColorRemoveClass = $maxTextColorRemoveClass === '' ? 'fcGray' : $maxTextColorRemoveClass;
            $maxValueRemoveClass = $maxValueRemoveClass === '' ? 'fcGray' : $maxValueRemoveClass;

            if (maxCurrentSum <= maxPrevPeriodSum) {
                if (maxCurrentSum == 0 && (maxCurrentSum == maxPrevPeriodSum)) {
                    $labelPercentMaxTop.removeClass($maxRemoveClass).addClass('percent_zero');
                    $labelPercentColorMaxTop.removeClass($maxTextColorRemoveClass).addClass('fcGray');
                    $labelPercentValueMaxTop.removeClass($maxValueRemoveClass).addClass('fcGray');
                } else {
                    $labelPercentMaxTop.removeClass($maxRemoveClass).addClass('percent_down');
                    $labelPercentColorMaxTop.removeClass($maxTextColorRemoveClass).addClass('fcprimary');
                    $labelPercentValueMaxTop.removeClass($maxValueRemoveClass).addClass('fcprimary');
                }
            } else {
                $labelPercentMaxTop.removeClass($maxRemoveClass).addClass('percent_up');
                $labelPercentColorMaxTop.removeClass($maxTextColorRemoveClass).addClass('fcRed');
                $labelPercentValueMaxTop.removeClass($maxValueRemoveClass).addClass('fcRed');
            }

            $labelPrevLowSum.html(module.utility.addComma(lowPrevPeriodSum.toFixed(0)));
            $labelPrevMidSum.html(module.utility.addComma(midPrevPeriodSum.toFixed(0)));
            $labelPrevMaxSum.html(module.utility.addComma(maxPrevPeriodSum.toFixed(0)));
        },
        updateStatusTypeRateGraph: function (data)
        {
            let low = data['low'];
            let mid = data['mid'];
            let max = data['max'];

            let status = [0, 0, 0, 0];

            status[1] = max;
            status[2] = mid;
            status[3] = low;

            let statusSum = module.utility.getSumOfValues(status);
            if (statusSum < 1) {
                status[0] = 100;
                status[1] = status[2] = status[3] = 0
            }

            charts[1].update(status);
        },
        updateAverageGraph: function (data)
        {
            let average = data['average'];
            let percent = data['percent'];

            $labelAverage.html(average);
            $labelPercent.html(percent);

            charts[2].update([percent, 200-percent]);
        },
        updateChart: function()
        {
            let self = control;
            let data = self.data;

            let indicatorData = data['indicator_data'];
            let status = data['status_data'];
            let customUnit = data['custom_unit'];

            let statusSumData = status['status_sum'];
            let statusData = status['status_data'];
            let statusRate = status['status_rate'];
            let statusAverage = status['status_average'];

            if (customUnit != null) {
                // 커스텀 단위가 있는 경우 변경한다.
                charts[0].setUnit(customUnit);
                $(".label_unit").html(customUnit);
            }

            // 부하현황 그래프 생성
            self.updateStatusGraph(statusData);
            // 주요지표 출력
            self.updateIndicator(indicatorData, statusSumData);
            // 부하별 비율 그래프 출력
            self.updateStatusTypeRateGraph(statusRate);
            // 기준값 대비 평균 사용량 그래프 출력
            self.updateAverageGraph(statusAverage);
        },
        getPercentClass: function($selector) {
            const removeCls = $selector.prop('class');
            const tmpArray = removeCls.split(' ');

            let addCls = '';

            for (let i = 0; i < tmpArray.length; i++){
                for (let j = 0; j < $percentCSS.length; j++){
                    if (tmpArray[i] == $percentCSS[j]){
                        return tmpArray[i];
                    }
                }
            }

            return '';
        },
        getPercentColorClass: function($selector)
        {
            const removeCls = $selector.prop('class');
            const tmpArray = removeCls.split(' ');

            let addCls = '';

            for (let i = 0; i < tmpArray.length; i++){
                for (let j = 0; j < $percentColorCSS.length; j++){
                    if (tmpArray[i] == $percentColorCSS[j]){
                        return tmpArray[i];
                    }
                }
            }

            return '';
        },
        onPeriodRadioButtonClicked: function($this)
        {
            let self   = control;
            let index = $this.val();

            // 라디오버튼 설정
            $(".radio_period").prop('checked', false);
            $this.prop('checked', true);

            self.selectedDateType = parseInt(index);
        },
        onSelectedBuildingInfoChanged: function(type)
        {
            let self = control;

            switch (type) {
                case 'dong' :
                    self.selectedBuildingDong = $selectBuildingDong.val();
                    self.selectedBuildingFloor = defaultFloor;
                    self.selectedBuildingRoom = defaultRoom;
                    break;
                case 'floor' :
                    self.selectedBuildingDong = $selectBuildingDong.val();
                    self.selectedBuildingFloor = $selectBuildingFloor.val();
                    self.selectedBuildingRoom = defaultRoom;
                    break;
                case 'room' :
                    self.selectedBuildingDong = $selectBuildingDong.val();
                    self.selectedBuildingFloor = $selectBuildingFloor.val();
                    self.selectedBuildingRoom = $selectBuildingRoom.val();
                    break;
            }
        },
        onMenuButtonClicked: function($this, index)
        {
            let buildingManager= module.BuildingManager();

            // 동적으로 생성한 버튼이므로 .define에서 정의 할 경우 인식을 못함.
            const $buttonGroup = $("#energy_btn_group > button");

            let buttons = [];
            let arrayIndex = $this.index();

            let self = control;

            let $id = $this.prop('id');
            let energyKey = self.getEnergyKeyName($id);

            self.selectedOption = index;
            self.selectedBuildingDong = defaultDong;
            self.selectedBuildingFloor = defaultFloor;
            self.selectedBuildingRoom = defaultRoom;
            self.selectedEnergyKey = energyKey;

            // 동적 버튼 생성
            $.each($buttonGroup, function(index, item){
                let btnId =  $(this).prop("id");
                let $id = $("#" + btnId);

                buttons[index] = $id;
            });

            buttons.forEach(function(item, index) {
                item.removeClass("on");
            });
            buttons[arrayIndex].addClass("on");

            // 단위변경
            self.changeUnit();
            // 화면 초기화
            self.clearLabels();
            self.clearChart();

            // 라디오버튼 '월'로 설정
            $(".radio_period").prop('checked', false);
            $("input[name='radio_date2']").prop('checked', true);

            self.selectedDateType = PERIOD_STATUS;

            buildingManager.setEnergyKey(energyKey);
            buildingManager.request(index);

            // 요청
            if (CONFIGS['auto_loading'] === true) {
                self.request();
            }
        },
        onSearchBtnClicked: function()
        {
            let self = control;

            let today = self.getToday();
            let period = self.selectedDateType;

            let date = $dateSelect.val();

            if (period === 2 && date == today) {
                //alert("일 검색은 전일부터 조회 가능합니다.");
                //return;
            }

            self.request();
        },
        getEnergyKeyName: function(id)
        {
            let energyTypes = id.split('btn_');
            let key = energyTypes[1];

            if (key === undefined || key === '') {
                return;
            }

            return energyTypes[1];
        },
        changeUnit: function()
        {
            let self = control;
            let selectedOption = self.selectedOption;

            let units = module.utility.getBemsUnits2();
            let unit  = units[selectedOption];

            charts.forEach(function(item, index, array) {
                item._callback = control.onBarLineChartClicked;
                item.setUnit(unit);
            });

            $(".label_unit").html(unit);
        },
        getToday: function()
        {
            let date = new Date();

            let year = date.getFullYear();
            let month = date.getMonth()+1;
            let day = date.getDate();

            if (month < 10) {
                month = "0" + month;
            }

            if (day < 10) {
                day = "0" + day;
            }

            return year + "-" + month + "-" + day;
        },
        energyStandardPopup: function()
        {
            let self = control;

            let params = [];
            let data = [];

            // 팝업오픈
            standardFormPopup.open();

            data.push({ name: 'mode', value: 'getStandards' });
            data.push({ name: 'option', value: self.selectedOption });
            data.push({ name: 'menu', value: self.selectedMenu });

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: "info_popup"},
                {name: "params", value: JSON.stringify(data)}
            );

            let getStandardParam = {
                url: requestUrl,
                params: params,
                callback: self.getStandardValue,
                callbackParams: null,
                showAlert: true
            };

            module.request(getStandardParam);
        },
        getStandardValue: function(data, params)
        {
            let standards = data['standard_data'];

            $inputPopupHour.val(standards['hour']);
            $inputPopupDay.val(standards['day']);
            $inputPopupMonth.val(standards['month']);
        },
        energyStandardPopupSave: function()
        {
            let self = control;

            let params = [];
            let data = [];

            let isValidMessage = self.isValidStandard();
            if (isValidMessage != "") {
                alert(isValidMessage);
                return
            }

            // 팝업닫기
            standardFormPopup.close();

            data.push({ name: 'mode', value: 'saveStandards' });
            data.push({ name: 'option', value: self.selectedOption });
            data.push({ name: 'menu', value: self.selectedMenu});
            data.push({ name: 'hour', value: $inputPopupHour.val() });
            data.push({ name: 'day', value: $inputPopupDay.val() });
            data.push({ name: 'month', value: $inputPopupMonth.val()});

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: 'info_popup'},
                {name: "params", value: JSON.stringify(data)}
            );

            let getStandardParam = {
                url: requestUrl,
                params: params,
                callback: null,
                callbackParams: null,
                showAlert: true
            };

            module.request(getStandardParam);

            // 화면 초기화
            self.clearLabels();
            self.clearChart();

            self.request();
        },
        isValidStandard: function()
        {
            let hour = $inputPopupHour.val()*1;
            let day = $inputPopupDay.val()*1;
            let month = $inputPopupMonth.val()*1;

            let message = '';

            if ($.isNumeric(hour) == false) {
                message = "한시간 목표 사용량에는 숫자만 입력하세요.";
                return message;
            }

            if ($.isNumeric(day) == false) {
                message = "하루 목표 사용량에는 숫자만 입력하세요.";
                return message;
            }

            if ($.isNumeric(month) == false) {
                message = "한달 목표 사용량에는 숫자만 입력하세요.";
                return message;
            }

            if (hour > day) {
                message = "한시간 목표 사용량은 하루 목표 사용량보다 넘을 수 없습니다.";
                return message;
            }

            if (day > month) {
                message = "하루 목표 사용량은 한달 목표 사용량보다 넘을 수 없습니다.";
                return message;
            }

            return message;
        },
        requestChartLegend: function()
        {
            const keys = Object.keys(colorData['status']);
            const statusColorValues = Object.values(colorData['status']);

            let pStatusTags = [];
            let pStatusRateTags = [];
            let pStatusString = '';
            let pStatusRateString = '';

            // 부하현황 범주 동적 추가
            $.each(keys, function (index, value){
                let $spanStatusColor = $("<span></span>");
                $spanStatusColor.css('background-color', "rgb(" + statusColorValues[index] + ")");

                let $spanStatusUnit = $("<span></span>").attr({
                    'class' : 'label_unit'
                }).html('kWh');

                pStatusTags.push("<p>" + $spanStatusColor[0].outerHTML + "" + labelStatus[index] + "("+ $spanStatusUnit[0].outerHTML +")</p>")
            });

            pStatusString = pStatusTags.join('');
            $divChartLegendStatus.html(pStatusString);

            // 부하별 비율 범주 동적 추가
            $.each(keys, function (index, value){
                if (index >= 3) {
                    // 최대부하, 중부하, 경부하만 표시..
                    return true;
                }

                let $spanStatusColor = $("<span></span>");
                $spanStatusColor.css('background-color', "rgb(" + statusColorValues[index] + ")");

                let $spanRatePercentUnit = $("<span></span>").attr({
                    'class' : 'label_percent_unit'
                }).html('%');

                pStatusRateTags.push("<p>" + $spanStatusColor[0].outerHTML + "" + labelStatus[index] + "(" + $spanRatePercentUnit[0].outerHTML +")</p>");
            });

            pStatusRateString = pStatusRateTags.join('');
            $divChartLegendStatusRate.html(pStatusRateString);
        },
    };

    $btnSearch.on("click", function(){
        control.onSearchBtnClicked();
    });

    $selectBuildingDong.on("change", function() {
       control.onSelectedBuildingInfoChanged('dong');
    });

    $selectBuildingFloor.on("change", function() {
        control.onSelectedBuildingInfoChanged('floor');
    });

    /*
        $selectBuildingRoom.on("change", function() {
            control.onSelectedBuildingInfoChanged('room');
        });
    */

    $radioPeriod.on("click", function() {
        control.onPeriodRadioButtonClicked($(this));
    });

    $standardSetting.on("click", function() {
        control.energyStandardPopup();
    });

    $btnButtonSave.on("click", function() {
        control.energyStandardPopupSave();
    });

    $btnButtonClose.on("click", function() {
        standardFormPopup.close();
    });

    return control;
}