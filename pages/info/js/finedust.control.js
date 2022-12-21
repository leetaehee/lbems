let control;

$(document).ready(function() {
    control = createControl();
    control.requestChartLegend();
    control.request();
});

function createControl()
{
    let control = {
        periodPM10 : periodStatusPM10,
        periodPM25 : periodStatusPM25,
        selectedChartOption: chartOption,
        selectedMenu: menuType,
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({
                'period_pm_10': self.periodPM10,
                'period_pm_25': self.periodPM25
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

            self.clearChart();

            module.request(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;
            self.data = data;

            // 처음 로딩 시 실행 (금일로 설정)
            self.updateFinedust();
        },
        clearChart: function()
        {
            let self = control;
            let len = charts.length;

            let chartOption = self.selectedChartOption;

            for (i = chartOption; i < len; i++) {
                charts[i].clear();
                charts[i].update();
            }
        },
        updateFinedust: function()
        {
            let self   = control;
            let dailyFinedust = self.data.dailyFinedust.fd;

            let startHour = 0;
            let endHour = 23;

            let fmLabel = new Array();

            let fm10Data = new Array();
            let fm10Standard = new Array();

            let fm25Data = new Array();
            let fm25Standard = new Array();

            let hour, i;

            for (hour = startHour, i = 0; hour <= endHour; hour++, i++){
                if (hour < 10) {
                    hour = '0' + hour;
                }
                let hourColumn = 'hour' + hour;

                fmLabel[i] = dailyFinedust[hourColumn]['hour'];
                fm10Data[i] = dailyFinedust[hourColumn]['pm10'];
                fm10Standard[i] = (dailyFinedust[hourColumn]['fs']);

                fm25Data[i] = dailyFinedust[hourColumn]['pm25'];
                fm25Standard[i] = (dailyFinedust[hourColumn]['fsu']);
            }

            // 미세먼지(pm10)
            charts[0].update(fmLabel, fmLabel, fm10Data, fm10Standard, []);
            // 미세먼지(pm25)
            charts[1].update(fmLabel, fmLabel, fm25Data, fm25Standard, []);

            // 실시간 미세먼지 표기
            self.updateRealfinedustStatus();
        },
        updateDaily: function(index)
        {
            let self = control;
            let dailyFinedust = self.data.dailyFinedust.fd;

            let startHour = 0;
            let endHour = 23;

            let label = new Array();
            let data = new Array();
            let standard = new Array();

            let hour, i;
            let sum = 0;

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
            let monthFinedust = self.data.monthFinedust;

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
            let yearFinedust = self.data.yearFinedust;

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
            // 사용자가 선택한 주기에 대해 함수명을 리턴한다.
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
            let todayFinedust = self.data.dailyFinedust.today;

            // 적용 배경 색상
            let bg10Class = todayFinedust.bg10;
            let bg25Class = todayFinedust.bg25;
            let imoticon10Class = todayFinedust.imoticon10;
            let imoticon25Class = todayFinedust.imoticon25;

            // 이미 적용된 배경색상 클래스명
            let oldBgPM10Class = $dustPM10.prop("class");
            let oldBgPM25Class = $dustPM25.prop("class");

            // 이미 적용된 이모티콘 이미지 클래스명
            let oldImPM10Class = $dustPM10Imoticon.prop("class");
            let oldImPM25Class = $dustPM25Imoticon.prop("class");

            // 미세먼지 상태에 대해서 배경색상 변경
            $dustPM10.removeClass(oldBgPM10Class).addClass(bg10Class);
            $dustPM25.removeClass(oldBgPM25Class).addClass(bg25Class);

            // 미세먼지 상태에 대해서 이미지 변경
            $dustPM10Imoticon.removeClass(oldImPM10Class).addClass(imoticon10Class);
            $dustPM25Imoticon.removeClass(oldImPM25Class).addClass(imoticon25Class);

            $totalPM10.html(todayFinedust.pm10);
            $totalPM25.html(todayFinedust.pm25);
        },
        finedustStandardPopup: function()
        {
            let self = control;

            let params = [];
            let data = [];

            // 팝업오픈
            standardFormPopup.open();

            data.push({name: 'mode', value: 'getStandards'});
            data.push({name: 'option', value: 0 });
            data.push({name: 'menu', value: self.selectedMenu});

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

            $inputPopupPM10.val(standards['pm10']);
            $inputPopupPM25.val(standards['pm25']);
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
            data.push({ name: 'pm10', value: $inputPopupPM10.val() });
            data.push({ name: 'pm25', value: $inputPopupPM25.val() });

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: "info_popup"},
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
            let pm10 = $inputPopupPM10.val()*1;
            let pm25 = $inputPopupPM25.val()*1;

            let message = '';

            if ($.isNumeric(pm10) == false) {
                message = "미세먼지 입력란에는 숫자만 입력하세요.";
                return message;
            }

            if ($.isNumeric(pm25) == false) {
                message = "초미세먼지 입력란에는 숫자만 입력하세요.";
                return message;
            }

            if (pm10 < pm25) {
                message = "미세먼지 기준 값은 초미세먼지 기준값 보다 작을 수 없습니다.";
                return message;
            }

            return message;
        },
        requestChartLegend: function()
        {
            // finedust 정보 조회 하여 범주 동적 추가
            const finedustKeys = Object.keys(colorData['finedust_color']);
            const finedustColorValues = Object.values(colorData['finedust_color']);

            let pFinedustTags = [];
            let pFinedustString = '';

            // finedust 정보 범주 추가
            $.each(finedustKeys, function (index, value){
                let $spanColor = $("<span></span>");
                $spanColor.css("background-color", "rgb(" + finedustColorValues[index] + ")");

                let $spanUnit = $("<span></span>").html(FINEDUST_UNIT);

                pFinedustTags.push(`<p> ${$spanColor[0].outerHTML}${CHART_LABELS[index]}(${$spanUnit[0].outerHTML})`);
            });

            pFinedustString = pFinedustTags.join('');
            $divChartLegendFinedust.html(pFinedustString);
        },
    };

    $selectYearPM10.on("change", function() {
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