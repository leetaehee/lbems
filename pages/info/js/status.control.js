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
    const lToday = module.utility.getBaseDate();
    const lYesterDay = new Date(lToday.setDate(lToday.getDate() - 1));

    $dateSelect.datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        showMonthAfterYear: true,
        maxDate: 0,
    });

    let d = $.datepicker.formatDate('yy-mm-dd', lYesterDay);

    $dateSelect.val(d);
}

function createControl() {
    let control = {
        selectedOption: btnStartIndex,
        selectedBuildingDong: defaultDong,
        selectedBuildingFloor: defaultFloor,
        selectedBuildingRoom: defaultRoom,
        selectedEnergyKey: defaultEnergyKey,
        selectedDateType: PERIOD_STATUS,
        selectedMenu: '',
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'option', value: self.selectedOption});
            data.push({ name: 'date', value: $dateSelect.val()});
            data.push({ name: 'date_type', value: self.selectedDateType});
            data.push({ name: 'floor_type', value: self.selectedBuildingFloor});
            data.push({ name: 'room_type', value: self.selectedBuildingRoom});
            data.push({ name: 'energy_key', value: self.selectedEnergyKey});
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

            if (data['error'] === 'today') {
                alert(VALIDATE_SEARCH_TODAY);
                return;
            }

            self.data = data;
            self.updateChart();
        },
        updateChart: function()
        {
            let self = control;
            let data = self.data;

            // 주요지표 출력
            self.updateIndicator(data['indicator_data']);

            // 부하현황 그래프 생성
            self.updateStatusGraph(data['status_data']);
        },
        updateIndicator: function(indicators)
        {
            let self = control;

            const currents = indicators['current'];
            const lasts = indicators['last'];

            let currentLowStatus = parseFloat(currents['low_status']);
            let currentMidStatus = parseFloat(currents['mid_status']);
            let currentMaxStatus = parseFloat(currents['max_status']);

            let lastLowStatus = parseFloat(lasts['low_status']);
            let lastMidStatus = parseFloat(lasts['mid_status']);
            let lastMaxStatus = parseFloat(lasts['max_status']);

            const lowDifferPercent = self.getDifferPercent(currentLowStatus, lastLowStatus);
            const midDifferPercent = self.getDifferPercent(currentMidStatus, lastMidStatus);
            const maxDifferPercent = self.getDifferPercent(currentMaxStatus, lastMaxStatus);

            let $lowRemoveClass = self.getPercentClass($labelPercentLowTop);
            let $lowTextColorRemoveClass = self.getPercentColorClass($labelPercentColorLowTop);
            let $lowValueRemoveClass= self.getPercentColorClass($labelPercentValueLowTop);

            let $midRemoveClass = self.getPercentClass($labelPercentMidTop);
            let $midTextColorRemoveClass = self.getPercentColorClass($labelPercentColorMidTop);
            let $midValueRemoveClass= self.getPercentColorClass($labelPercentValueMidTop);

            let $maxRemoveClass = self.getPercentClass($labelPercentMaxTop);
            let $maxTextColorRemoveClass = self.getPercentColorClass($labelPercentColorMaxTop);
            let $maxValueRemoveClass= self.getPercentColorClass($labelPercentValueMaxTop);

            $labelUsageNow.html(module.utility.addComma(currentLowStatus.toFixed(0)));
            $labelUsageLast.html(module.utility.addComma(currentMidStatus.toFixed(0)));
            $labelDiff.html(module.utility.addComma(currentMaxStatus.toFixed(0)));

            $labelPrevLowSum.html(module.utility.addComma(lastLowStatus.toFixed(0)));
            $labelPrevMidSum.html(module.utility.addComma(lastMidStatus.toFixed(0)));
            $labelPrevMaxSum.html(module.utility.addComma(lastMaxStatus.toFixed(0)));

            $labelPercentValueLowTop.html(module.utility.addComma(lowDifferPercent.toFixed(0)));
            $labelPercentValueMidTop.html(module.utility.addComma(midDifferPercent.toFixed(0)));
            $labelPercentValueMaxTop.html(module.utility.addComma(maxDifferPercent.toFixed(0)));

            // 경부하
            self.updateIndicatorState(
                currentLowStatus, lastMidStatus,
                $labelPercentLowTop, $labelPercentColorLowTop, $labelPercentValueLowTop,
                $lowRemoveClass, $lowTextColorRemoveClass, $lowValueRemoveClass
            );

            // 중부하
            self.updateIndicatorState(
                currentMidStatus, lastMidStatus,
                $labelPercentMidTop, $labelPercentColorMidTop, $labelPercentValueMidTop,
                $midRemoveClass, $midTextColorRemoveClass, $midValueRemoveClass
            );

            // 최대부하
            self.updateIndicatorState(
                currentMaxStatus, lastMaxStatus,
                $labelPercentMaxTop, $labelPercentColorMaxTop, $labelPercentValueMaxTop,
                $maxRemoveClass, $maxTextColorRemoveClass, $maxValueRemoveClass
            );
        },
        updateStatusGraph: function(data)
        {
            let self = control;

            // 부하 현황 그래프는 시간대별만 보여줌.. 월,년을 보여주기 위해서는 기획 필요
            const dateType = self.selectedDateType;
            //const dateType = 2;

            const labels = Object.keys(data);
            const values = Object.values(data);

            const dates = self.getChartLabels(labels, dateType);
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            // 부하현황 그래프
            statusChart.update(dates['labels'], dates['labels'], values, decimalPoint);
        },
        getChartLabels: function(d, chart)
        {
            let labels = [];
            let tooltips = [];

            chart = parseInt(chart);

            switch (chart)
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
        updateIndicatorState: function(current, last, $top, $colorTop, $valueTop, removeClass, textRemoveClass, valueRemoveClass)
        {
            removeClass = removeClass === '' ? 'percent_zero' : removeClass;
            textRemoveClass = textRemoveClass === '' ? 'fcGray' : textRemoveClass;
            valueRemoveClass = valueRemoveClass === '' ? 'fcGray' : valueRemoveClass;

            if (current <= last) {
                if (current === 0 && (current === last)) {
                    $top.removeClass(removeClass).addClass($percentCSS[2]);
                    $colorTop.removeClass(textRemoveClass).addClass($percentColorCSS[2]);
                    $valueTop.removeClass(valueRemoveClass).addClass($percentColorCSS[2]);
                } else {
                    $top.removeClass(removeClass).addClass($percentCSS[1]);
                    $colorTop.removeClass(textRemoveClass).addClass($percentColorCSS[1]);
                    $valueTop.removeClass(valueRemoveClass).addClass($percentColorCSS[1]);
                }
            } else {
                $top.removeClass(removeClass).addClass($percentCSS[0]);
                $colorTop.removeClass(textRemoveClass).addClass($percentColorCSS[0]);
                $valueTop.removeClass(valueRemoveClass).addClass($percentColorCSS[0]);
            }
        },
        getDifferPercent: function(current, last)
        {
            let result = 0;

            if (last > 0) {
                result = ((current/last) * 100) - 100;
            }

            if (last === 0 && current > 0) {
                // 이전이 0이면서, 현재가 증가한 경우
                result = 100;
            }

            return result;
        },
        getPercentClass: function($selector) {
            const removeCls = $selector.prop('class');
            const tmpArray = removeCls.split(' ');

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
                        addCls = tmpArray[i]
                        return addCls;
                    }
                }
            }

            return addCls;
        },
        onPeriodRadioButtonClicked: function($this)
        {
            let self = control;
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
            $("input[name='radio_date1']").prop('checked', true);

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

            if (period === 2 && date === today) {
                alert(VALIDATE_SEARCH_TODAY);
                return;
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
        clearChart: function()
        {
            let self = control;

            statusChart.clear();
            statusChart.update();
        },
        changeUnit: function()
        {
            let self = control;
            let selectedOption = self.selectedOption;

            let units = module.utility.getBemsUnits2();
            let unit  = units[selectedOption];

            $(".label_unit").html(unit);
        },
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
        },
        requestChartLegend: function()
        {
            const pStatusTags = [];
            let pStatusString = '';

            // 부하현황 범주 동적 추가
            let $spanStatusColor = $("<span></span>");
            $spanStatusColor.css('background-color', "rgb(" + STATUS_NORMAL_COLOR + ")");

            let $spanStatusUnit = $("<span></span>").attr({
                'class' : 'label_unit'
            }).html('kWh');

            pStatusTags.push("<p>" + $spanStatusColor[0].outerHTML + "사용량" + "("+ $spanStatusUnit[0].outerHTML +")</p>");

            pStatusString = pStatusTags.join('');
            $divChartLegend.html(pStatusString);
        },
    };

    $btnSearch.on("click", function(){
        control.onSearchBtnClicked();
    });

    $selectBuildingDong.on("change", function(){
       control.onSelectedBuildingInfoChanged('dong');
    });

    $selectBuildingFloor.on("change", function() {
        control.onSelectedBuildingInfoChanged('floor');
    });

    $selectBuildingRoom.on("change", function() {
        control.onSelectedBuildingInfoChanged('room');
    });

    $radioPeriod.on("click", function() {
        control.onPeriodRadioButtonClicked($(this));
    });

    return control;
}