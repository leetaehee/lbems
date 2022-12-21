let control;

$(document).ready(function() {
    control = createControl();
    control.requestChartLegend();
    control.requestFloorTable();
    control.requestUsageTable();
    control.requestCSSChange();
    control.request();
    setInterval(function() {
        control.request();
    }, 1000 * 60 * 5);
});

function createControl()
{
    let control = {
        selectedOption: defaultOption,
        selectedPopupEnergy: defaultOption,
        selectedDateType: defaultDateType,
        selectedFloorType: defaultFloorType,
        selectedRoomType: defaultRoomType,
        selectedPredictDateType: defaultPredictDateType,
        selectedPredictLoading: defaultPredictLoading,
        request: function()
        {
            let self = control;
            let params = [];
            let data = [];

            data.push({ name: 'date_type', value: self.selectedDateType });
            data.push({ name: 'floor', value: self.selectedFloorType });
            data.push({ name: 'room', value: self.selectedRoomType });
            data.push({ name: 'predict_date_type', value: self.selectedPredictDateType });
            data.push({ name: 'predict_loading', value: self.selectedPredictLoading });

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
                showAlert: true
            };

            module.request(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;

            self.data = data;

            // 전체 에너지 사용현황
            self.updateEnergyUsageInfo();
            // 용도별 사용현황
            self.updateUsageInfo();
            // 예측그래프
            self.updatePredictInfo();
            // 단위면적당 사용현황
            self.updateAreaInfo();
            // 층별 사용현황
            self.updateFloorInfo();
        },
        updateEnergyUsageInfo: function()
        {
            let self = control;

            let energyData = self.data['energy_data'];

            let dateType = self.selectedDateType;
            let option = self.selectedOption;

            let dates = Object.keys(energyData['now_data']['electric']['data']);
            let lastDates = Object.keys(energyData['last_data']['electric']['data']);
            let nowVals = Object.values(energyData['now_data']['electric']['data']);
            let lastVals = Object.values(energyData['last_data']['electric']['data']);
            let newPrices = Object.values(energyData['now_data']['electric']['price']);
            let homeData = energyData['home_data'];

            let electricPrice = 0;

            // 그래프 상에서 보여줄 기준값
            let standards = self.getGraphTargetValue('electric', dates);

            // 주기별 기준값
            let periodStandard = self.getTargetValueByPeriod('electric');

            // 30일과 31일에 대한 예외처리
            let results = self.updateLabelDaysCheck(lastDates, dates, lastVals, nowVals, standards);
            let newLabels = self.getChartLabels(results['labels'], dateType);

            // 단위
            let units = module.utility.getBemsUnits2();
            let unit = units[option];

            // 그래프 소수점 표시
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            /** 요금제 처리 방식 개선 */
            if (dateType === 0) {
                // 금년의 경우 월별 요금제를 모두 더하는 방식
                electricPrice = module.utility.getSumOfValues(newPrices);
            } else {
                // 금일, 금월은 전체 사용량을 기준으로 요금 계산
                electricPrice = homeData['now']['price'];
            }

            // 주기에 대한 기준값
            $labelTargetElec.html(module.utility.addComma(periodStandard));

            // 사용량 요금 정보 출력
            $labelUsageElec.html(module.utility.addComma(homeData['now']['used'].toFixed(0)));
            $labelBeforeElec.html(module.utility.addComma(homeData['last']['used'].toFixed(0)));
            $labelPriceElec.html(module.utility.addComma(electricPrice.toFixed(0)));
            $labelUnit.html(unit);

            // 그래프 출력
            chartEnergy.update(newLabels['labels'], nowVals, lastVals, unit, standards, decimalPoint);
        },
        updateUsageInfo: function()
        {
            let self = control;

            let usageData = self.data['usage_data'];
            if (usageData === undefined || usageData === '' || usageData === null) {
                return;
            }

            let usages = usageData['usage'];
            let distributions = usageData['distribution'];

            let valueLength = Object.keys(usages).length;

            // 데이터없음 필드 합쳐서 생성
            let arraySize = valueLength + 1;
            let arrayIndex = 1;
            let pieDistributions = Array.from({length:arraySize}, () => 0);

            $.each(distributions, function(key, values) {
                // 분포도
                $("#label_" + key + "_percent").html(module.utility.addComma(values));

                pieDistributions[arrayIndex] = values;
                arrayIndex++;
            });

            $.each(usages, function(key, values) {
                // 사용량
                $("#label_" + key + "_used").html(module.utility.addComma(values.toFixed(0)));
            });

            let useSum = module.utility.getSumOfValues(pieDistributions);
            if (useSum < 1) {
                pieDistributions[0] = 100;
            }

            chartPieUse.setUnit('%');
            chartPieUse.update(LABEL_USAGES, COLOR_USAGES, pieDistributions);
        },
        updatePredictInfo: function()
        {
            let self = control;
            let option = self.selectedOption;
            let dateType = self.selectedPredictDateType;

            // 소수점 조회
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            dateType = self.getUseSumArrayIndex(dateType);

            self.selectedPredictLoading = false;

            // 초기화
            $labelPredictionPeriod.html('로딩중..');

            let predictData = self.data['predict_data'];
            if (predictData === undefined || predictData === '') {
                return;
            }

            let units = module.utility.getBemsUnits2();
            let unit = units[option];

            let currentValue = parseFloat(predictData['current']);
            let predictValue = parseFloat(predictData['predict']);
            let periodMessage = predictData['period_message'];
            if (currentValue === undefined && predictValue === undefined && periodMessage === undefined) {
                return;
            }

            let tempColumns = [currentNames[dateType], expectNames[dateType]];
            let tempUsed = [currentValue, predictValue];

            // 예측 기간 출력
            $labelPredictionPeriod.html(periodMessage);

            // 예측 그래프 출력
            chartPredict.update(tempColumns, tempUsed, unit, decimalPoint);
        },
        updateAreaInfo: function()
        {
            let self = control;

            let areaData = self.data['area_data'];
            if (areaData === undefined || areaData === '') {
                return;
            }

            // 평균값 조회
            let nowData = module.utility.makeZeroArray(areaData['now']['data']);
            //let nowData = Object.values(areaData['now']['data']);
            //let nowPrices = Object.values(areaData['now']['price']);
            let nowAverageUsed = module.utility.getArrayAverage(nowData);
            //let nowAveragePrice = module.utility.getArrayAverage(nowPrices);

            let lastData = module.utility.makeZeroArray(areaData['last']['data']);
            //let lastData = Object.values(areaData['last']['data']);
            //let lastPrices = Object.values(areaData['last']['price']);
            let lastAverageUsed = module.utility.getArrayAverage(lastData);
            //let lastAveragePrice = module.utility.getArrayAverage(lastPrices);

            // 그래프 비율 조회
            //let lastPriceRate = (lastAveragePrice/nowAveragePrice) * 100;
            //let nowPriceRate = (nowAveragePrice/lastAveragePrice) * 100;

            let lastUsedRate = (lastAverageUsed/nowAverageUsed)*100;
            let nowUsedRate = (nowAverageUsed/lastAverageUsed)*100;

            // 사용량, 요금 출력
            $labelPrevAreaUsed.html(module.utility.addComma(lastAverageUsed));
            $labelCurrentAreaUsed.html(module.utility.addComma(nowAverageUsed));
            //$labelPrevAreaPrice.html(module.utility.addComma(lastAveragePrice));
            //$labelCurrentAreaPrice.html(module.utility.addComma(nowAveragePrice));

            // 사용량, 요금 그래프로 표현
            //$graphPrevAreaPrice.css("width", module.utility.getValidPercent(lastPriceRate) + "%");
            //$graphCurrentAreaPrice.css("width", module.utility.getValidPercent(nowPriceRate) + "%");

            $graphPrevAreaUsed.css("width", module.utility.getValidPercent(lastUsedRate) + "%");
            $graphCurrentAreaUsed.css("width", module.utility.getValidPercent(nowUsedRate) + "%")
        },
        updateFloorInfo: function()
        {
            let self = control;
            let floorData = self.data['floor_data'];

            $.each(floorData, function(floor, value) {
                $("#label_" + floor + "_total_used").html(module.utility.addComma(value.toFixed(0)));
            });
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
        updateLabelDaysCheck: function(lasts, nows, vals1, vals2, standards)
        {
            let self = control;
            let period = self.selectedDateType;
            let type = "";

            let priority = [];
            let compareLabels = [];
            let priorityVals = [];
            let compareVals = [];

            let compareIndex = 0;

            let lastLength = lasts.length;
            let nowLength = nows.length;

            if (lastLength >= nowLength) {
                priority = lasts;
                compareLabels = nows;
                priorityVals = vals1;
                compareVals = vals2;
                type = "prev";
            }

            if (nowLength >= lastLength) {
                priority = nows;
                compareLabels = lasts;
                priorityVals = vals2;
                compareVals = vals1;
                type = "current";
            }

            if (nowLength === lastLength && period !== 1) {
                return {
                    "labels" : lasts,
                    "vals1" : vals1,
                    "vals2" : vals2
                };
            }

            for (let i = 0; i < priority.length; i++) {
                let tempVal1 = ''+priority[i];
                let tempVal2 = ''+compareLabels[i];

                tempVal1 = tempVal1.substring(6, 8);
                tempVal2 = tempVal2.substring(6, 8);

                if (tempVal1 !== tempVal2) {
                    compareIndex = i;

                    compareVals.splice(compareIndex, 0, 0);
                    compareLabels.splice(compareIndex, 0, priority[compareIndex]);
                    standards.splice(compareIndex, 0, standards[0]);
                }
            }

            return {
                "labels" : compareLabels,
                "vals1" : type === "prev" ?  priorityVals : compareVals,
                "vals2" : type === "current" ? priorityVals : compareVals
            };
        },
        getChartLabels: function(d, chart)
        {
            let labels = [];
            let tooltips = [];

            switch(chart)
            {
                case 0:
                    // year
                    d.forEach(x => {
                        let label = x.substring(4, 6);
                        labels.push(label + "월");
                    });
                    break;
                case 1:
                    // month
                    d.forEach(x => {
                        let day = x.substring(6, 8);
                        let label = day + "일";
                        labels.push(label);
                    });
                    break;
                case 2:
                    // day
                    d.forEach(x => {
                        let tempX = ''+x;
                        let hour  = tempX.substring(8, 10);
                        let label = hour + "시";
                        labels.push(label);
                    });
                    break;
                case 3:
                    // minute
                    d.forEach(x => {
                        let minute = x.substring(10, 12);
                        let label  = minute + "분";
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
        getGraphTargetValue: function(energyType, dates)
        {
            let self = control;

            let energyData = self.data['energy_data'];
            let target = energyData['target_data'][energyType];
            let dateType = self.selectedDateType;

            if (target === undefined || target === '') {
                return;
            }

            let graphTargets = new Array();
            let temp;

            let targets = target.split("/");

            /*
             * 그래프에 기준값 표시할 때는 금일-> 시, 금월-> 일, 금년-> 월에 대한 기준값 출력
             */

            switch(dateType)
            {
                case 0:
                    temp = targets[2];
                    break;
                case 1:
                    temp = targets[1];
                    break;
                case 2:
                    temp = targets[0];
                    break;
            }

            $.each(dates, function(index, item){
                graphTargets[index] = temp;
            });

            return graphTargets;
        },
        getTargetValueByPeriod: function(energyType, isNotSplite = true)
        {
            let self = control;

            let energyData = self.data['energy_data'];
            let targets = energyData['target_data'][energyType];
            let dateType = self.selectedDateType;

            let target = '';
            let $sd = targets.split('/');

            if (targets === undefined || targets === '') {
                return;
            }

            if (isNotSplite == false) {
                // splite를 하지 않고 배열 전체를 리턴함
                target = $sd;
            } else {
                if (dateType === 2) {
                    // 금일
                    target = $sd[1];
                } else if (dateType === 1) {
                    // 금월
                    target = $sd[2];
                } else if (dateType === 0) {
                    // 금년
                    target = $sd[3];
                }
            }

            return target;
        },
        onSelectDateTypeChanged: function($this)
        {
            let self = control;
            self.selectedDateType = parseInt($this.val());

            let dateType = self.selectedDateType;

            // 건물 단위 면적당 평균 사용 요금 시  주기별 단위 변경
            $labelPeriodTimeUnit.html(periodTimeUnits[dateType]);

            self.clearCheckbox();
            self.request();
        },
        onSettingButtonClicked: function(energy)
        {
            let self = control;
            self.selectedPopupEnergy = energy;

            let units = module.utility.getBemsUnits2();
            let unit = units[energy];

            let names = module.utility.getBemsUnits2Names();
            let name  = names[energy];

            let targets = self.getTargetValueByPeriod('electric', false);

            // 에너지원에 대한 명칭, 단위 설정
            $popupEnergyName.text(name);
            $popupUnit1.text(unit);
            $popupUnit2.text(unit);
            $popupUnit3.text(unit);
            $popupUnit4.text(unit);

            // 기준값 설정
            $inputPopupHour.val(targets[0]);
            $inputPopupDay.val(targets[1]);
            $inputPopupMonth.val(targets[2]);
            $inputPopupYear.val(targets[3]);

            formPopup.open();
        },
        onButtonSaveClicked: function()
        {
            let self = control;

            self.requestSave();
        },
        requestSave: function()
        {
            let self = control;
            let params = [];

            let hour = $inputPopupHour.val()*1;
            let day = $inputPopupDay.val()*1;
            let month = $inputPopupMonth.val()*1;
            let year = $inputPopupYear.val()*1;

            // 유효성 검사
            if (hour === '') {
                alert(VALIDATE_HOUR_VALUE_EMPTY);
                return;
            } else {
                if ($.isNumeric(hour) == false) {
                    alert(VALIDATE_HOUR_VALUE_ONLY_INTEGER);
                    return;
                }
            }

            if (day == "") {
                alert(VALIDATE_DAY_VALUE_EMPTY);
                return;
            } else {
                if ($.isNumeric(day) == false) {
                    alert(VALIDATE_DAY_VALUE_ONLY_INTEGER);
                    return;
                }
            }

            if (month == "") {
                alert(VALIDATE_MONTH_VALUE_EMPTY);
                return;
            } else {
                if ($.isNumeric(month) == false) {
                    alert(VALIDATE_MONTH_VALUE_ONLY_INTEGER);
                    return;
                }
            }

            if (year == "") {
                alert(VALIDATE_YEAR_VALUE_EMPTY);
                return;
            } else {
                if ($.isNumeric(year) == false) {
                    alert(VALIDATE_YEAR_VALUE_ONLY_INTEGER);
                    return;
                }
            }

            if (hour > day) {
                alert(VALIDATE_HOUR_VALUE_OVER);
                return
            }

            if (day > month) {
                alert(VALIDATE_DAY_VALUE_OVER);
                return;
            }

            if (month > year) {
                alert(VALIDATE_MONTH_VALUE_OVER);
                return;
            }

            let data = [];

            data.push({ name: 'option', value: self.selectedPopupEnergy });
            data.push({ name: 'year', value: year });
            data.push({ name: 'month', value: month });
            data.push({ name: 'day', value: day });
            data.push({ name: 'hour', value: hour });

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: "dashboard_reference_save"},
                {name: "params", value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestSaveCallback,
                callbackParams: data,
                showAlert: true,
                showLoading: false
            };

            module.request(requestParams);
        },
        requestSaveCallback: function(data, params)
        {
            let self = control;

            // 팝업닫기
            formPopup.close();
            // 리로딩
            self.request();
        },
        onClickedPopupClosed: function()
        {
            formPopup.close();
        },
        onButtonMoreClicked: function($this, groupPage = '')
        {
            const FILE_GROUP = $this.data('group');
            const FILE_TYPE = $this.data("file_type");
            const MENU_NAME = $this.data("menu");
            const FILE_NAME = FILE_TYPE + ".html";

            // 메뉴 이동
            let menuManager = module.MenuModule();
            menuManager.requestPageLocation(FILE_GROUP, FILE_NAME, MENU_NAME, groupPage, group, menu);
        },
        onButtonPredictClicked: function($this, dateType)
        {
            let self = control;

            let params = [];
            let data = [];

            self.selectedPredictDateType = dateType;
            self.selectedPredictLoading = true;

            // 버튼상태변경
            $buttons.forEach(function(item, index) {
                item.removeClass("on");
            });
            $this.addClass('on');

            data.push({ name: 'date_type', value: self.selectedDateType });
            data.push({ name: 'floor', value: self.selectedFloorType });
            data.push({ name: 'room', value: self.selectedRoomType });
            data.push({ name: 'predict_date_type', value: self.selectedPredictDateType });
            data.push({ name: 'predict_loading', value: self.selectedPredictLoading });

            params.push(
                {name: 'requester', value: requester},
                {name: 'request', value: command},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestPredictCallback,
                callbackParams: null,
                showAlert: true
            };

            module.request(requestParams);
        },
        requestPredictCallback: function(data, params)
        {
            let self = control;

            self.data['predict_data'] = data['predict_data'];

            self.updatePredictInfo();
        },
        onCheckboxFloorClicked: function($this)
        {
            let self = control;

            let floor = $this.val();
            let labelFloorName= $this.closest("tr").find(".label_floor_name").html();

            const $roomGroups = [];
            const $checkboxFloor = $(".checkbox_floor");

            $.each(floors, function(index, key) {
                const $divFloorGroup = $("#div_" + key + "_room_group");

                $roomGroups.push($divFloorGroup);

                // 층별 체크박스 해제
                $divFloorGroup.css("display", "none");
            });

            let checked = $this.prop('checked');
            if (checked === false) {
                // 체크박스를 해제한 경우
                self.selectedFloorType = 'all';
                self.selectedRoomType = 'all';
            }

            if (checked === true) {
                // 체크박스를 선택 한경우
                $("#div_" + floor + "_room_group").css('display', 'block');
                $("#radio_room_" + floor).prop('checked', true);

                self.selectedFloorType = floor;
                self.selectedRoomType = 'all';

                $checkboxFloor.prop('checked', false);
                $this.prop('checked', true);
            }

            $labelCurrentFloorName.html(labelFloorName);

            self.request();
        },
        clearCheckbox: function()
        {
            let self = control;
            const $checkboxFloor = $(".checkbox_floor");

            self.selectedFloorType = defaultFloorType;
            self.selectedRoomType = defaultRoomType;
            self.selectedPredictDateType = defaultPredictDateType;
            self.selectedPredictLoading = defaultPredictLoading;

            $checkboxFloor.prop('checked', false);

            $buttons.forEach(function(item, index) {
                item.removeClass("on");
            });
            $btnDaily.addClass('on');
        },
        requestFloorTable: function()
        {
            const FLOOR_INFOS = CONFIGS['floor_key_data'];

            let trTags = [];
            let trString = '';

            $.each(FLOOR_INFOS, function(floor, floorName){
                if (floor === '0M' || floor === 'ALL') {
                    return true;
                }

                let $checkboxFloor = $("<input>").attr({
                    type: 'checkbox',
                    id: 'checkbox_floor_' + floor,
                    class: 'checkbox_floor',
                    value: floor,
                }).html(floorName);
                let $spanFloorUsed = $("<span></span>").attr({
                    id: 'label_' + floor + '_total_used',
                    class: 'total_use checkbox_floor',
                }).html(0);

                trTags.push(`<tr>`);
                trTags.push(`<td>${$checkboxFloor[0].outerHTML}</td>`);
                trTags.push(`<td class="label_floor_name">${floorName}</td>`);
                trTags.push(`<td>${$spanFloorUsed[0].outerHTML}</td>`);
                trTags.push(`</tr>`);
            });

            // 배열을 문자열로 변환
            trString = trTags.join('');

            // 추가
            $("#tbody_floor > tr").remove();
            $tbodyFloor.append(trString);
        },
        requestUsageTable: function()
        {
            const USAGE_LABELS = CONFIGS['usage_labels'];
            const USAGE_COLORS = CONFIGS['usage_colors'];
            const USAGE_KEYS = CONFIGS['usage_key'];

            let trTags = [];
            let trString = '';

            $.each(USAGE_LABELS, function(index, value) {
                if (index === 0) {
                    return true;
                }

                let $spanItemName = $("<span></span>").attr({
                    'class' : 'colorchip mr05',
                });
                $spanItemName.css('background-color', "rgb(" + USAGE_COLORS[index] + ")");

                let $spanUsed = $("<span></span>").attr({
                    'id' : 'label_' + USAGE_KEYS[index] + '_used'
                }).html(0);

                let $spanLabelUnit = $("<span></span>").attr({
                    'id' : 'label_' + USAGE_KEYS[index] + '_unit'
                }).html('kWh');

                let $spanLabelPercent = $("<span></span>").attr({
                    'id' : 'label_' + USAGE_KEYS[index] + '_percent'
                }).html(0);

                trTags.push("<tr>");
                trTags.push("<th>" + $spanItemName[0].outerHTML + value + "</th>");
                trTags.push("<td> " + $spanUsed[0].outerHTML + " " + $spanLabelUnit[0].outerHTML + " (" + $spanLabelPercent[0].outerHTML + "%)</td>");
                trTags.push("</tr>");
            });

            // 배열을 문자열로 변환
            trString = trTags.join('');

            // 추가
            $("#tbody_usage > tr").remove();
            $tbodyUsage.append(trString);

            $("#tbody_usage > tr > th").addClass("ac");
        },
        requestChartLegend: function()
        {
            const USAGE_LABELS = CONFIGS['usage_labels'];
            const USAGE_COLORS = CONFIGS['usage_colors'];

            const pTags = [];
            const pLegendTags = [];

            let pString = '';
            let pLegendString = '';

            const units = module.utility.getBemsUnits2();
            const CHART_LEGEND_UNIT = units[defaultOption];

            $.each(USAGE_LABELS, function (index, value) {
                if (index === 0) {
                    return true;
                }

                let $spanItemColor = $("<span></span>")
                    .css("background-color", "rgb(" + USAGE_COLORS[index] + ")");

                let $pItemTag = $("<p></p>").html(`${$spanItemColor[0].outerHTML}${USAGE_LABELS[index]}`);

                pTags.push($pItemTag[0].outerHTML);
            });

            $.each(CHART_LEGENDS, function (name, value){
                let $spanItemColor = $("<span></span>")
                    .css('background-color', "rgb(" + value + ")");

                let $pItemTag = $("<p></p>")
                    .html(`${$spanItemColor[0].outerHTML} ${name}(${CHART_LEGEND_UNIT})`);

                pLegendTags.push($pItemTag[0].outerHTML);
            });

            pString = pTags.join('');
            $divChartLegendUsage.append(pString);

            pLegendString = pLegendTags.join('');
            $divChartLegendEnergy.append(pLegendString);
        },
        requestCSSChange: function()
        {
            // 대시보드 이미지 변경
            const FILE_PATH = UPPER_DIR + CONFIGS['dashboard']['building_image'];
            $birdeye.css({
                "background" : "rgb(" + floorBackgroundCSS +") url(" + FILE_PATH + ") no-repeat center 20px",
            });
        },
    };

    $.each(floors, function(index, floor) {
        // 층 검색 체크박스 이벤트 동적 등록
        $(document).on("click", "#checkbox_floor_" + floor,  function(){
            control.onCheckboxFloorClicked($(this));
        });
    });

    $selectEnergy.on("change", function() {
        control.onSelectDateTypeChanged($(this));
    });

    $btnDashboardAll.on("click", function(){
        control.onButtonMoreClicked($(this));
    });

    $btnSetElec.on("click", function() {
        control.onSettingButtonClicked(0);
    });

    $btnButtonSave.on("click", function() {
        control.onButtonSaveClicked();
    });

    $btnButtonClose.on("click", function() {
        control.onClickedPopupClosed();
    });

    $btnDaily.on("click", function(){
        control.onButtonPredictClicked($(this), 2);
    });

    $btnWeekly.on("click", function(){
        control.onButtonPredictClicked($(this), 5);
    });

    $btnMonth.on("click", function(){
        control.onButtonPredictClicked($(this), 1);
    });

    return control;
}