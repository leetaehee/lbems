let control;

$(document).ready(function() {
    control = createControl();
    control.requestChartLegend();
    control.request();
});

function createControl()
{
    let control = {
        selectedDateTypeCo2 : defaultDateTypeCo2,
        selectedDateTypePm25 : defaultDateTypePM25,
        selectedChartOption: chartOption,
        selectedMenu: menuType,
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'co2_date_type', value: self.selectedDateTypeCo2 });
            data.push({ name: 'pm25_date_type', value: self.selectedDateTypePm25 });

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

            self.clearChart();

            module.request(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;

            if (data === undefined || data === null || data === '') {
                return;
            }
            self.data = data;

            // 처음 로딩 시 실행 (금일로 설정)
            self.updateFinedust();
        },
        clearChart: function()
        {
            let self = control;
            let len = charts.length;

            let chartOption = self.selectedChartOption;

            for (let i = chartOption; i < len; i++) {
                charts[i].clear();
                charts[i].update();
            }
        },
        updateFinedust: function()
        {
            let self = control;

            let dailyFinedust = self.data['daily_finedust']['data'];
            if (dailyFinedust === undefined || dailyFinedust === null || dailyFinedust === '') {
                return;
            }

            let startHour = 0;
            let endHour = 23;

            let label = new Array();

            let fm25Data = new Array();
            let fm25StandardData = new Array();

            let co2Data = new Array();
            let co2StandardData = new Array();

            let hour, i;

            for (hour = startHour, i = 0; hour <= endHour; hour++, i++){
                if (hour < 10) {
                    hour = '0' + hour;
                }
                let hourColumn = 'hour' + hour;

                let fm25Standard  = dailyFinedust[hourColumn]['pm25_standard'];
                let co2Standard = dailyFinedust[hourColumn]['co2_standard'];

                label.push(dailyFinedust[hourColumn]['hour']);
                fm25Data.push(parseInt(dailyFinedust[hourColumn]['pm25']));
                fm25StandardData.push(parseInt(fm25Standard));

                co2Data.push(parseInt(dailyFinedust[hourColumn]['co2']));
                co2StandardData.push(parseInt(co2Standard));
            }

            // CO2
            charts[0].setUnit('ppm');
            charts[0].update(label, label, co2Data, co2StandardData, []);
            // 미세먼지(pm25)
            charts[1].update(label, label, fm25Data, fm25StandardData, []);

            // 실시간 미세먼지 표기
            self.updateRealfinedustStatus();
        },
        updateDaily: function(index)
        {
            let self = control;
            let dailyFinedust = self.data['daily_finedust']['data'];

            let startHour = 0;
            let endHour = 23;

            let label = new Array();
            let data = new Array();
            let standard = new Array();

            let hour, i;

            let key = monthType[index];
            let alias = standardAlias[index];

            for (hour = startHour, i = 0; hour <= endHour; hour++, i++){
                if (hour < 10) {
                    hour = '0' + hour;
                }
                let hourColumn = 'hour' + hour;

                label[i] = dailyFinedust[hourColumn]['hour'];
                data[i] = dailyFinedust[hourColumn][key];
                standard[i] = dailyFinedust[hourColumn][alias];
            }

            // 그래프 업데이트
            charts[index].update(label, label, data, standard, []);
        },
        updateMonth: function(index)
        {
            let self = control;
            let monthFinedust = self.data['month_finedust'];

            let startDate = monthFinedust['start_date'];
            let endDate = monthFinedust['end_date'];

            let label = new Array();
            let data = new Array();
            let standard = new Array();

            let date, i;

            let key = monthType[index];

            for (date = startDate, i = 0; date <= endDate; date++, i++){
                let $month = monthFinedust[key][i];

                label[i] = $month['date'];
                data[i] = $month['val'];
                standard[i] = $month['standard'];
            }

            // 그래프 업데이트
            charts[index].update(label, label, data, standard, []);
        },
        updateYear: function(index)
        {
            let self = control;
            let yearFinedust = self.data['year_finedust'];

            let startYear = yearFinedust['start_year'];
            let endYear = yearFinedust['end_year'];

            let label = new Array();
            let data = new Array();
            let standard = new Array();

            let year, i;

            let key = monthType[index];

            for (year = startYear, i = 0; year <= endYear; year++, i++){
                let $year = yearFinedust[key][i];

                label[i] = $year['date'];
                data[i] = $year['val'];
                standard[i] = $year['standard']*30;
            }

            // 그래프 업데이트
            charts[index].update(label, label, data, standard, []);
        },
        updatePeriod: function(period, index)
        {
            // 사용자가 선택한 주기에 대해 해당 함수를 호출한다.
            let self = control;

            switch (period) {
                case "daily" :
                    self.updateDaily(index);
                    break;
                case "month" :
                    self.updateMonth(index);
                    break;
                case "year" :
                    self.updateYear(index);
                    break;
            }
        },
        updateRealfinedustStatus: function()
        {
            let self = control;

            let data = self.data;
            let todayFinedust = data['daily_finedust']['today'];

            // 현재 기준값 출력
            $totalPM25.html(module.utility.addComma(parseInt(todayFinedust['pm_25'])));
            $totalCo2.html(module.utility.addComma(parseInt(todayFinedust['co2'])));

            // 적용 배경 색상
            let bgCo2Class = todayFinedust['bg_co2'];
            let bg25Class = todayFinedust['bg_25'];
            let imoticonCo2Class = todayFinedust['imoticon_co2'];
            let imoticonPM25Class = todayFinedust['imoticon_25'];

            // 이미 적용된 배경색상 클래스명
            let oldBgCo2Class = $dustCo2.prop("class");
            let oldBgPM25Class = $dustPM25.prop("class");

            // 이미 적용된 이모티콘 이미지 클래스명
            let oldImCo2Class = $dustCo2Imoticon.prop("class");
            let oldImPM25Class = $dustPM25Imoticon.prop("class");

            // 미세먼지 상태에 대해서 배경색상 변경
            $dustCo2.removeClass(oldBgCo2Class).addClass(bgCo2Class);
            $dustPM25.removeClass(oldBgPM25Class).addClass(bg25Class);

            // 미세먼지 상태에 대해서 이미지 변경
            $dustCo2Imoticon.removeClass(oldImCo2Class).addClass(imoticonCo2Class);
            $dustPM25Imoticon.removeClass(oldImPM25Class).addClass(imoticonPM25Class);
        },
        finedustStandardPopup: function()
        {
            let self = control;

            let params = [];
            let data = [];

            // 팝업오픈
            standardFormPopup.open();

            data.push({ name: 'mode', value: 'getStandards' });
            data.push({ name: 'option', value: 0 });
            data.push({ name: 'menu', value: self.selectedMenu });

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: setCommand},
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

            $inputPopupCo2.val(standards['limit_val_co2'][0]);
            $inputPopupPM25.val(standards['limit_val_finedust'][1]);
        },
        finedustStandardPopupSave: function()
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
            data.push({ name: 'option', value: 0 });
            data.push({ name: 'menu', value: self.selectedMenu });
            data.push({ name: 'pm10', value: '' });
            data.push({ name: 'pm25', value: $inputPopupPM25.val() });
            data.push({ name: 'co2', value: $inputPopupCo2.val() });

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: setCommand},
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
            self.request();
        },
        isValidStandard: function()
        {
            let co2 = $inputPopupCo2.val()*1;
            let pm25 = $inputPopupPM25.val()*1;

            let message = '';

            if ($.isNumeric(co2) == false) {
                message = "CO2 입력란에는 숫자만 입력하세요.";
                return message;
            }

            if ($.isNumeric(pm25) == false) {
                message = "초미세먼지 입력란에는 숫자만 입력하세요.";
                return message;
            }

            return message;
        },
        requestChartLegend: function()
        {
            // co2 정보 조회 하여 범주 동적 추가
            const co2Keys = Object.keys(colorData['co2_color']);
            const co2ColorValues = Object.values(colorData['co2_color']);

            // finedust 정보 조회 하여 범주 동적 추가
            const finedustKeys = Object.keys(colorData['finedust_color']);
            const finedustColorValues = Object.values(colorData['finedust_color']);

            let pCo2Tags = [];
            let pCo2String = '';
            let pFinedustTags = [];
            let pFinedustString = '';

            // co2 정보 범주 추가
            $.each(co2Keys, function (index, value){
                let $spanColor = $("<span></span>");
                $spanColor.css("background-color", "rgb(" + co2ColorValues[index] + ")");

                let $spanUnit = $("<span></span>").html(CHART_UNITS[0]);

                pCo2Tags.push(`<p> ${$spanColor[0].outerHTML}${CHART_LABELS[index]}(${$spanUnit[0].outerHTML})`);
            });

            pCo2String = pCo2Tags.join('');
            $divChartLegendCo2.html(pCo2String);

            // finedust 정보 범주 추가
            $.each(finedustKeys, function (index, value){
                let $spanColor = $("<span></span>");
                $spanColor.css("background-color", "rgb(" + finedustColorValues[index] + ")");

                let $spanUnit = $("<span></span>").html(CHART_UNITS[1]);

                pFinedustTags.push(`<p> ${$spanColor[0].outerHTML}${CHART_LABELS[index]}(${$spanUnit[0].outerHTML})`);
            });

            pFinedustString = pFinedustTags.join('');
            $divChartLegendFinedust.html(pFinedustString);

        },
    };

    $selectYearCo2.on("change", function() {
        control.updatePeriod($(this).val(), 0);
    });

    $selectYearPM25.on("change", function() {
        control.updatePeriod($(this).val(), 1);
    });

    $btnSettingFinedust.on("click", function() {
        control.finedustStandardPopup();
    });

    $btnButtonSave.on("click", function() {
        control.finedustStandardPopupSave();
    });

    $btnButtonClose.on("click", function() {
        standardFormPopup.close();
    });

    return control;
}