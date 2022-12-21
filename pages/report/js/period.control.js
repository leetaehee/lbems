let control;
let buildingManager = module.BuildingManager();

$(document).ready(function(){
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
    control.requestEnergyCode();
    control.requestChartLegend();
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
        selectedEnergyCode: {},
        selectedDateType: DEFAULT_DATE_TYPE,
        selectedOption: defaultEmpty,
        selectedEnergyKey: defaultEmpty,
        selectedBuildingDong: defaultDong,
        selectedBuildingFloor: defaultFloor,
        selectedBuildingRoom: defaultRoom,
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];

            let dates = self.getPeriodDateRange();

            data.push({ name: 'start', value: dates['start'] });
            data.push({ name: 'end', value: dates['end'] });
            data.push({ name: 'date_type', value: $(".radio_period:checked").val() });
            data.push({ name: 'option', value: self.selectedOption });
            data.push({ name: 'floor_type', value: self.selectedBuildingFloor });
            data.push({ name: 'room_type', value: self.selectedBuildingRoom });
            data.push({ name: 'energy_key', value: self.selectedEnergyKey });
            data.push({ name: 'dong_type', value: self.selectedBuildingDong });

            params.push(
                {name: 'requester', value: requester},
                {name: 'request', value: command},
                {name: 'params', value: JSON.stringify(data)}
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

            self.updatePeriodChart();
        },
        updatePeriodChart: function()
        {
            let self = control;
            let data = self.data;

            let d = data['data'];
            let price =  isDisplayPrice === false ? [] : data['price'];

            if (d === undefined && price === undefined) {
                return;
            }

            let dates = Object.keys(d);
            let vals  = Object.values(d);
            let dateType = self.selectedDateType;
            let option = self.selectedOption;

            let startDate = new Date($startDate.val());
            let endDate = new Date($endDate.val());

            let differDay = module.utility.getDateSpan(startDate, endDate);
            if (dateType === 2 && differDay > 0) {
                // 금일이면서 1일 이상 검색 하는 경우.
                dateType = 1;
            }

            let temp = self.getChartLabels(dates, option, dateType);
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            chartPeriod.clear();
            chartPeriod.update(temp.labels, dates, vals, price, temp.tooltips, decimalPoint);
        },
        onSearchClicked: function()
        {
            let self = control;
            let dateType = self.selectedDateType;

            if (dateType === 2) {
                // 금일
                let startDate = $startDate.val();
                let endDate = $endDate.val();

                let differDay = module.utility.getDateSpan(startDate, endDate);

                if (differDay > 31) {
                    // 28일, 30일, 31일 어떻게 대처할것인가? 우선 31일로..
                    alert("주기 '일'로 검색 할 경우 31일까지 검색 가능합니다.");
                    return;
                }

                if (startDate > endDate) {
                    // 시작일은 종료일보다 늦을 수 없다.
                    alert("시작일은 종료일보다 이전 또는 같은 일이어야 합니다.");
                    return;
                }
            }

            if (dateType === 5) {
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
        getOption: function(energyKey)
        {
            let self = control;

            const energyCodeData = self.selectedEnergyCode;

            let fcOption = '';

            $.each(energyCodeData, function(groupKey, groupItems) {
                $.each(groupItems, function(k, items) {
                    if (k === energyKey) {
                        fcOption = items['option'];
                        return false;
                    }
                });

                if (fcOption !== '') {
                    return false;
                }
            });

            return fcOption;
        },
        onEnergyTypeChanged: function($this)
        {
            let self = control;

            const fcEnergyKey = $this.val();

            if (fcEnergyKey != '') {
                buildingManager.setEnergyKey(fcEnergyKey);

                let option = self.getOption(fcEnergyKey);
                if (option === '') {
                    return;
                }
                self.selectedOption = option;
                self.selectedBuildingDong = defaultDong;
                self.selectedBuildingFloor = defaultFloor;
                self.selectedBuildingRoom = defaultRoom;
                self.selectedEnergyKey = fcEnergyKey;

                const units = module.utility.getBemsUnits2();
                const unit = units[option];

                const usedLabel = option === 11 ? chartLabels[1] : chartLabels[0];
                const priceLabel = option === 11 ? chartPriceLabels[1] : chartPriceLabels[0];

                buildingManager.request(option);
                chartPeriod.setUnit(unit);
                chartPeriod.setLegend(usedLabel, priceLabel);

                $("#label_unit").html(unit);
                $("#label_used_name").html(usedLabel);
                $("#label_price_name").html(priceLabel);

                self.request();
            }
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

            self.selectedDateType = parseInt(val);
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

            let dateType = self.selectedDateType;
            let start, end;

            if (dateType == 0) {
                // 년도 검색
                start = end = $startYearYm.val();
            }

            if (dateType == 5) {
                // 기간별로 월 검색
                let lStartMonth = $startMonth.val();
                let lEndMonth = $endMonth.val();

                if (lStartMonth < 10) {
                    lStartMonth = '0' + lStartMonth;
                }

                if (lEndMonth < 10) {
                    lEndMonth = '0' + lEndMonth;
                }

                start = $startMonthYm.val() + "" + lStartMonth;
                end = $endMonthYm.val() + "" + lEndMonth;
            }

            if (dateType == 2) {
                // 일 검색
                start = $startDate.val();
                end = $endDate.val();
            }

            return {
                'start' : start,
                'end' : end
            }
        },
        getChartLabels: function(d, option, chart)
        {
            let labels = [];
            let tooltips = [];

            const txt = option === 11 ? '발전' : '사용'

            switch(chart) {
                case 0:
                    //year
                    d.forEach(x => {
                        let label = x.substring(4, 6);
                        labels.push(label + "월");
                        let year  = x.substring(0, 4);
                        tooltips.push(year + "년 " + label + "월 " + txt);
                    });
                    break;
                case 1:
                    //month
                    d.forEach(x => {
                        let year = x.substring(0, 4);
                        let month = x.substring(4, 6);
                        let day = x.substring(6, 8);
                        let label = month + "/" + day;
                        labels.push(label);
                        tooltips.push(year + "년 " + month + "월 " + day + "일 " + txt);
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

                        tooltips.push(year + "년 " + month + "월 " + day + "일 " + hour + "시 " + txt);
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

                        tooltips.push(year + "년 " + month + "월 " + day + "일 " + hour + "시 " + minute + "분 " + txt);
                    });
                    break;
                case 5:
                    // 기간별 월 검색
                    d.forEach(x => {
                        let year = x.substring(0, 4);
                        let month = x.substring(4, 6);
                        let label = year + "-" + month;
                        labels.push(label);

                        tooltips.push(year + "년 " + month + "월 " + txt);
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
        onExcelButtonClicked: function()
        {
            let d = chartPeriod.getData();

            let time = module.utility.getCurrentTime();
            let name = excelFileName + "_" + time + ".xlsx";

            module.excel().exportExcel(d, name);
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
        requestEnergyCode:  function()
        {
            let self = control;
            let params = [];
            let data = [];

            params.push(
                {name: 'requester', value: 'common' },
                {name: 'request', value: 'energy_code' },
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestEnergyCodeCallback,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestEnergyCodeCallback: function(data, params)
        {
            let self = control;

            let energyCode = data['energy_code'];
            energyCode['etc'] = solarObj;

            const options = [];

            let defaultOption = '';
            let defaultKey = ''

            $.each(energyCode, function(groupData, groupItems) {
                $.each(groupItems, function(k, v) {
                    if (defaultOption === '' && defaultKey === '') {
                        defaultOption = v['option'];
                        defaultKey = k;
                    }
                    options.push(`<option value=${k}>${v['label']}</option>`);
                });
            });

            $selectEnergyType.append(options.join(''));

            if (defaultOption !== '' && defaultKey !== '') {
                buildingManager.setEnergyKey(defaultKey);
                buildingManager.request(defaultOption);

                self.selectedEnergyCode = energyCode;
                self.selectedOption = defaultOption;
                self.selectedEnergyKey = defaultKey;
                self.request();
            }
        },
        requestChartLegend: function()
        {
            const keys = Object.keys(chartData['bar']);
            const barColorValues = Object.values(chartData['bar']);
            const lineColorValues = Object.values(chartData['line']);

            $.each(keys, function (index, value){
                let pTags = [];
                let pString = '';

                let $spanUsedColor = $("<span></span>");
                $spanUsedColor.css('background-color', "rgb(" + barColorValues[index] + ")");

                let $spanUsedUnit = $("<span></span>").attr({
                    'id' : 'label_unit'
                }).html('kWh');

                let $spanPriceColor = $("<span></span>");
                $spanPriceColor.css('background-color', "rgb(" + lineColorValues[index] + ")");

                let $spanPriceUnit = $("<span></span>").html('원');

                pTags.push("<p>" + $spanUsedColor[0].outerHTML + "<span id='label_used_name'>" + chartLabels[0] + "</span>(" + $spanUsedUnit[0].outerHTML + ")</p>");

                if (isDisplayPrice === true) {
                    pTags.push("<p>" + $spanPriceColor[0].outerHTML + "<span id='label_price_name'>" + chartPriceLabels[0] + "</span>(" + $spanPriceUnit[0].outerHTML + ")</p>");
                }

                pString = pTags.join('');

                $divChartLegend.html(pString);
            });
        },
    };

    $btnSearch.on("click", function () {
        control.onSearchClicked();
    });

    $btnRadioPeriod.on("click", function () {
        // 주기 변경 버튼
        control.onPeriodChangeClicked($(this));
    });

    $btnExcelPeriod.on("click", function () {
        // 엑셀 버튼
        control.onExcelButtonClicked();
    });

    $selectEnergyType.on("change", function() {
       // 에너지원 selectBox
       control.onEnergyTypeChanged($(this));
    });

    $selectBuildingFloor.on("change", function() {
        // 층 selectBox
        control.onSelectedBuildingInfoChanged('floor');
    });

    /*
        $selectBuildingRoom.on("change", function() {
            control.onSelectedBuildingInfoChanged('room');
        });
     */

    return control;
}