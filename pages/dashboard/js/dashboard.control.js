let control;

$(document).ready(function() {
    control = createControl();
    control.dashboardIntialize();
    control.requestChartLegend();
    control.requestFacilityTable();
    control.clearGraph();
    control.tooltipLoading();

    control.request();
    setInterval(function() {
        control.clearGraph();
        control.request();
    }, 1000 * 60 * 5);
});

function createControl()
{
    let control = {
        selectedOption: defaultOption,
        selectedPopupEnergy: defaultOption,
        selectedDateType: defaultDateType,
        clearGraph: function ()
        {
            $divDust.css("left", "0%");
            $divUltraDust.css("left", "0%");

            $graphDailyIndependence.css("width", "0%");
            $graphMonthIndependence.css("width", "0%");
            $graphYearIndependence.css("width", "0%");
        },
        request: function ()
        {
            let self = control;
            let params = [];
            let data = [];

            data.push({name: 'date_type', value: self.selectedDateType});

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
        requestCallback: function (data, params)
        {
            let self = control;

            self.data = data;

            let isEnabledWatchdog = parseInt(self.data['is_enabled_watchdog']);
            if (isEnabledWatchdog === 1) {
                // watchdog 버튼 활성화
                $("#btn_watchdog").css('display', 'block');
            }

            // 전체 에너지 사용현황
            self.updateEnergyUsageInfo();
            // 용도별 사용현황
            self.updateUsageInfo();
            // 설비 사용량 및 효율
            self.updateFacilityInfo();
            // 태양광 정보
            self.updateSolarInfo();
            // 에너지 자립률, 소비량, 생산량 정보
            self.updateIndependenceInfo();
            // 미세먼지 정보
            self.updateFinedustData();
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
        updateUsageInfo: function ()
        {
            let self = control;

            let usageData = self.data['usage_data'];
            if (usageData === undefined || usageData === '' || usageData === null) {
                return;
            }

            let valueLength = Object.keys(usageData).length;

            // 데이터없음 필드 합쳐서 생성
            let arraySize = valueLength;
            let arrayIndex = 1;
            let pieDistributions = Array.from({length: arraySize}, () => 0);

            $.each(usageData, function (key, values) {
                pieDistributions[arrayIndex] = values;
                arrayIndex++;
            });

            let useSum = module.utility.getSumOfValues(pieDistributions);
            if (useSum < 1) {
                pieDistributions[0] = 100;
            }

            chartPieUse.setUnit('%');
            chartPieUse.update(LABEL_USAGES, COLOR_USAGES, pieDistributions);
        },
        updateFacilityInfo: function ()
        {
            let self = control;

            let facilityData = self.data['facility_data'];
            if (facilityData === undefined || facilityData === '' || facilityData === null) {
                return;
            }

            $.each(facilityData, function(key, value) {
                $("#label_" + key + "_used").html(module.utility.addComma(value.toFixed(0)));
            });
        },
        updateSolarInfo: function ()
        {
            let self = control;

            let solarData = self.data['solar_data'];
            if (solarData === undefined || solarData === '') {
                return;
            }

            let solarInValue = parseInt(solarData['in']);
            let solarOutValue = parseInt(solarData['out']);

            let solarDiffUsed = solarInValue - solarOutValue;
            if (solarDiffUsed < 0) {
                solarDiffUsed = 0;
            }

            $labelCenterGen.html(module.utility.addComma(solarInValue));
            $labelCenterUse.html(module.utility.addComma(solarOutValue));
            $labelCenterDiff.html(module.utility.addComma(solarDiffUsed));
        },
        updateIndependenceInfo: function ()
        {
            let self = control;

            let independenceData = self.data['independence_data'];
            if (independenceData === undefined || independenceData === '') {
                return;
            }

            let dailyConsumptionUsed = independenceData['daily']['consumption'];
            let dailyProductionUsed = independenceData['daily']['production'];
            let monthConsumptionUsed = independenceData['month']['consumption'];
            let monthProductionUsed = independenceData['month']['production'];
            let yearConsumptionUsed = independenceData['year']['consumption'];
            let yearProductionUsed = independenceData['year']['production'];
            let dailyCo2Emission = independenceData['daily']['co2_emission'];

            let yearIndependenceGrade = independenceData['year']['independence_grade'];

            let dailyProgressSum = Math.floor((dailyProductionUsed / dailyConsumptionUsed) * 100);
            let monthProgressSum = Math.floor((monthProductionUsed / monthConsumptionUsed) * 100);
            let yearProgressSum = Math.floor((yearProductionUsed / yearConsumptionUsed) * 100);

            // 자립률
            let independenceRate = Math.floor(independenceData['year']['independence_rate']);
            let graphIndPercent = module.utility.getValidPercent(independenceRate);

            // 자립률 그래프
            chartPieEmission.update([graphIndPercent, 100 - graphIndPercent]);
            // 자립률 출력
            $labelIndPercent.html(independenceRate + "%");
            // 자립률에 의한 등급 출력
            $labelIndGrade.html(yearIndependenceGrade);
            // 온실가스 배출량 Co2 전일 출력
            $labelCo2Emission.html(dailyCo2Emission.toFixed(1));

            ENERGY_CHART_PERCENTS['graph_daily_independence'] = dailyProgressSum;
            ENERGY_CHART_PERCENTS['graph_month_independence'] = monthProgressSum;
            ENERGY_CHART_PERCENTS['graph_year_independence'] = yearProgressSum;

            // 에너지 소비량 대비 생산량
            // 금일
            $labelDailyConsumptionUsed.html(module.utility.addComma(dailyConsumptionUsed.toFixed(0)));
            $labelDailyProductionUsed.html(module.utility.addComma(dailyProductionUsed.toFixed(0)));
            $graphDailyIndependence.css("width", module.utility.getValidPercent(dailyProgressSum.toFixed(0)) + "%");

            // 금월
            $labelMonthConsumptionUsed.html(module.utility.addComma(monthConsumptionUsed.toFixed(0)));
            $labelMonthProductionUsed.html(module.utility.addComma(monthProductionUsed.toFixed(0)));
            $graphMonthIndependence.css("width", module.utility.getValidPercent(monthProgressSum.toFixed(0)) + "%");

            // 금년
            $labelYearConsumptionUsed.html(module.utility.addComma(yearConsumptionUsed.toFixed(0)));
            $labelYearProductionUsed.html(module.utility.addComma(yearProductionUsed.toFixed(0)));
            $graphYearIndependence.css("width", module.utility.getValidPercent(yearProgressSum).toFixed(0) + "%");
        },
        updateFinedustData: function ()
        {
            let self = control;

            let finedustData = self.data['finedust_data'];
            if (finedustData === undefined || finedustData === '') {
                return;
            }

            if (CONFIGS['is_use_environment'] === true) {
                self.updateFinedustCo2Data(finedustData);
                return;
            }

            let airPm10 = finedustData['air_pm10'];

            let finedustPM10 = (isUseFinedustSensor === false) ? finedustData['air_pm10'] : finedustData['pm10'];
            let finedustPM25 = (isUseFinedustSensor === false) ? finedustData['air_pm25'] : finedustData['pm25'];

            finedustPM10 = parseInt(finedustPM10).toFixed(0);
            finedustPM25 = parseInt(finedustPM25).toFixed(0);

            // 환경부 미세먼지
            $labelAirPm10.html(airPm10);

            // 미세먼지
            let temp = self.getFinedustLabelColors(finedustPM10);
            $labelDust.text(temp.label + " " + finedustPM10);
            self.setFinedustColor($divDust, temp.color);
            self.setFinedustTailColor($spanDust, temp.index);

            let pm10Proportion = self.getFiedustProportion(finedustPM10, temp.index);
            $divDust.css("left", pm10Proportion + "%");

            // 초미세먼지
            temp = self.getFinedustLabelColors(finedustPM25, true);
            $labelUltraDust.text(temp.label + " " + finedustPM25);
            self.setFinedustColor($divUltraDust, temp.color);
            self.setFinedustTailColor($spanUltraDust, temp.index);

            let pm25Proportion = self.getFiedustProportion(finedustPM25, temp.index, true);
            $divUltraDust.css("left", pm25Proportion + "%");
        },
        updateFinedustCo2Data: function (data)
        {
            let self = control;

            let temp;

            let airPm25 = data['air_pm25'];
            let finedustPM25 = parseInt(data['pm25']).toFixed(0);
            let finedustCO2 = parseInt(data['co2']).toFixed(0);

            temp = self.getCo2LabelColors(finedustCO2);
            $labelCo2.text(temp['label'] + " " + finedustCO2);
            self.setFinedustColor($divCo2, temp['color'], true);
            self.setFinedustTailColor($spanCo2, temp['index'], true);

            let co2Proportion = self.getCo2Proportion(finedustCO2, temp['index']);
            $divCo2.css("left", co2Proportion + "%");

            // CO2 농도 표기
            $labelCo2Value.html(finedustCO2);

            // 초미세먼지
            temp = self.getFinedustLabelColors(finedustPM25, true);
            $labelUltraByCo2Dust.text(temp['label'] + " " + finedustPM25);
            self.setFinedustColor($divUltraByCo2Dust, temp['color']);
            self.setFinedustTailColor($spanUltraByCo2Dust, temp['index']);

            let pm25Proportion = self.getFiedustProportion(finedustPM25, temp['index'], true);
            $divUltraByCo2Dust.css("left", pm25Proportion + "%");

            // 환경부 초미세먼지
            $labelAirPm25.html(airPm25);
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
                type = 'prev';
            }

            if (nowLength >= lastLength) {
                priority = nows;
                compareLabels = lasts;
                priorityVals = vals2;
                compareVals = vals1;
                type = 'current';
            }

            if (nowLength === lastLength && period !== 1) {
                return {
                    'labels' : lasts,
                    'vals1' : vals1,
                    'vals2' : vals2
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
                'labels' : compareLabels,
                'vals1' : type === 'prev' ?  priorityVals : compareVals,
                'vals2' : type === 'current' ? priorityVals : compareVals
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
                        let hour  = x.substring(8, 10);
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
        setFinedustColor: function($compo, colorClass, isCo2 = false)
        {
            if (isCo2 === true) {
                $compo.removeClass("dust1");
                $compo.removeClass("dust2");
                $compo.removeClass("dust3");
                $compo.removeClass("dust4");
                $compo.removeClass("dust6");
            } else {
                $compo.removeClass("dust1");
                $compo.removeClass("dust2");
                $compo.removeClass("dust3");
                $compo.removeClass("dust4");
            }

            $compo.addClass(colorClass);
        },
        setFinedustTailColor: function($compo, index, isCo2 = false)
        {
            let colorClass = "";

            if (isCo2 === true) {
                $compo.removeClass("dustb1");
                $compo.removeClass("dustb2");
                $compo.removeClass("dustb3");
                $compo.removeClass("dustb4");
                $compo.removeClass("dustb6");

                colorClass = colorCo2TailClasses[index];
            } else {
                $compo.removeClass("dustb1");
                $compo.removeClass("dustb2");
                $compo.removeClass("dustb3");
                $compo.removeClass("dustb4");

                colorClass = colorTailClasses[index];
            }

            $compo.addClass(colorClass);
        },
        getFiedustProportion(data, index, isultra = false)
        {
            let percentWeight = 25 * index;
            let level = fineDustLevel;

            if (isultra == true) {
                level = ultraDustLevel;
            }

            let divider = level[index];

            index = index - 1;

            let dataWeight = 0;

            if (index >= 0) {
                dataWeight = level[index];
            }

            let temp = divider - dataWeight;
            temp = temp == 0 ? 1 : temp;

            let proportion = (data - dataWeight) / temp * 100;
            proportion = percentWeight + proportion * 25 / 100;
            proportion = parseInt(proportion);

            if (isNaN(proportion)) {
                proportion = 0;
            }

            if (proportion < 0) {
                proportion = 0;
            }

            if (proportion > 100) {
                proportion = 100;
            }

            return proportion;
        },
        getCo2Proportion(data, index)
        {
            let percentWeight = 25 * index;
            let level = finedustCo2Level;

            let divider = level[index];

            index = index - 1;

            let dataWeight = 0;

            if (index >= 0) {
                dataWeight = level[index];
            }

            let temp = divider - dataWeight;
            temp = temp == 0 ? 1 : temp;

            let proportion = (data - dataWeight) / temp * 100;
            proportion = percentWeight + proportion * 25 / 100;
            proportion = parseInt(proportion);

            if (isNaN(proportion)) {
                proportion = 0;
            }

            if (proportion < 0) {
                proportion = 0;
            }

            if (proportion > 100) {
                proportion = 100;
            }

            return proportion;
        },
        getFinedustLabelColors: function(dust, isultra = false)
        {
            let level = -1;
            let levelArr = fineDustLevel;

            if (isultra == true) {
                levelArr = ultraDustLevel;
            }

            let len = levelArr.length;

            if (isultra == true) {
                levelArr = ultraDustLevel;
            }

            for (let i = 0; i < len; i++) {
                if (dust <= levelArr[i]) {
                    level = i;
                    break;
                }
            }

            len = fineDustLabel.length;

            if (level <= -1 || level > len - 1) {
                level = len - 1;
            }

            return {
                index: level,
                label:fineDustLabel[level],
                color:colorClasses[level]
            };
        },
        getCo2LabelColors: function(co2)
        {
            let level = -1;
            let levelArr = finedustCo2Level;

            let len = levelArr.length;
            co2 = parseInt(co2);

            for (let i = 0; i < len; i++) {
                if (co2 <= levelArr[i]) {
                    level = i;
                    break;
                }
            }

            len = finedustCo2Label.length;

            if (level <= -1 || level > len - 1) {
                level = len - 1;
            }

            return {
                index: level,
                label: finedustCo2Label[level],
                color: colorCo2Classes[level]
            };
        },
        onSelectDateTypeChanged: function($this)
        {
            let self = control;

            self.selectedDateType = parseInt($this.val());
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
        requestSave: function() {
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

            if (day === "") {
                alert(VALIDATE_DAY_VALUE_EMPTY);
                return;
            } else {
                if ($.isNumeric(day) == false) {
                    alert(VALIDATE_DAY_VALUE_ONLY_INTEGER);
                    return;
                }
            }

            if (month === "") {
                alert(VALIDATE_MONTH_VALUE_EMPTY);
                return;
            } else {
                if ($.isNumeric(month) == false) {
                    alert(VALIDATE_MONTH_VALUE_ONLY_INTEGER);
                    return;
                }
            }

            if (year === "") {
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
            data.push({ name: 'day', value: day })
            data.push({ name: 'hour', value: hour });

            params.push(
                { name: 'requester', value: requester },
                { name: 'request', value: 'dashboard_reference_save' },
                { name: 'params', value: JSON.stringify(data) }
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
        dashboardIntialize: function()
        {
            // watchdog 버튼 비활성화
            $("#btn_watchdog").css("display", "none");

            // 대시보드 세부 페이지 이동을 위한 값 설정
            $btnDashboardDetail.attr('data-file_type', CONFIGS['dashboard']['floor_page']);

            // Co2&초미세먼지와 미세먼지와 초미세먼지 그룹 결정
            const $finedustType = CONFIGS['is_use_environment'] === true ? "environment" : "finedust";
            $("#div_" + $finedustType + "_group").css("display", "block");
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

                let $pItemTag = $("<p></p>")
                    .html(`${$spanItemColor[0].outerHTML}${USAGE_LABELS[index]}`);

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
        requestFacilityTable: function()
        {
            const FACILITY_DATA = CONFIGS['facility_item'];

            if (Object.keys(FACILITY_DATA).length < 1) {
                return;
            }

            let trTags = [];
            let trString = '';

            $.each(FACILITY_DATA, function(key, facilityName){
                let $spanFacilityName = $("<span></span>").html(facilityName);
                let $spanFacilityStatus = $("<span></span>").attr({"class" : "bl_lnb_grn"});
                let $spanFacilityUsed = $("<span></span>").attr({"id" : "label_" + key+ "_used"}).html(0);
                //let $spanFacilityEffPercent = $("<span></span>").attr({"id" : "label_" + key+ "_eff_percent"}).html("-");

                trTags.push("<tr>");
                trTags.push("<td>" + $spanFacilityName[0].outerHTML + "</td>");
                trTags.push("<td>" + $spanFacilityStatus[0].outerHTML + "</td>");
                trTags.push("<td>" + $spanFacilityUsed[0].outerHTML + "</td>");
                //trTags.push("<td>" + $spanFacilityEffPercent[0].outerHTML + "</td>");
                trTags.push("</tr>");
            });

            // 타이틀 변경
            $labelFacilityTitle.html(CONFIGS['dashboard']['facility_title']);

            // 배열을 문자열로 변환
            trString = trTags.join('');

            // 추가
            $("#tbody_facility > tr").remove();
            $tbodyFacility.append(trString);
        },
        tooltipLoading: function()
        {
            $(document).tooltip({
                position: {
                    at: "left bottom"
                },
                content: function() {
                    const $this = $(this);

                    const title = $this.prop('title');
                    const id = $this.prop('id');

                    return `${title} ${ENERGY_CHART_PERCENTS[id]}%`;
                }
            });
        },
    };

    $selectEnergy.on("change", function() {
        control.onSelectDateTypeChanged($(this));
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

    $btnZero.on("click", function() {
        control.onButtonMoreClicked($(this));
    });

    $btnDashboardDetail.on("click", function(){
        control.onButtonMoreClicked($(this));
    });

    return control;
}