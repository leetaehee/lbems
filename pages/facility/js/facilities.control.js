let control;

$(document).ready(function() {
    createDatepicker();

    control = createControl();
    control.requestChartLegend();
    control.requestFacilityTable();
    if (IS_AUTO_SEARCH === true) {
        control.request();
    }
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
        selectedDateType: DEFAULT_PERIOD_STATUS,
        selectedDate: $dateSelect.val(),
        request: function()
        {
            let self = control;
            let params = [];
            let data = [];

            if (self.selectedDate === undefined) {
                self.selectedDate = module.utility.getBaseDate();
            }

            data.push({name: 'date_type', value: self.selectedDateType});
            data.push({name: 'date', value: self.selectedDate});

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: command},
                {name: "params", value: JSON.stringify(data)}
            );

            let requestParams =  {
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

            if (data === null) {
                return;
            }

            self.clearLabel();
            self.clearChart();
            self.updateChart();
        },
        updateChart: function()
        {
            let self = control;

            let facilityData = self.data;
            let dateType = self.selectedDateType;

            let graphs = [];
            let dates = [];

            /*
                if (RESULT_POWER_FACTOR === true) {
                    DEFAULT_UNIT = '%';
                }
             */

            $.each(facilityData, function(key, items){
                const list = items['list'];
                const total = parseFloat(items['total']);

                dates = Object.keys(list);
                let values = Object.values(list);

                // 사용량
                $("#label_" + key + "_usage").html(module.utility.addComma(parseFloat(total.toFixed(0))));

                // 효율
                //$("#label_" + key + "_eff_percent").html('-');

                // 그래프에 보여줄 설비 추가
                if (jQuery.inArray(key, GRAPH_DATA) >= 0) {
                    graphs.push(values);
                }
            });

            let tempDate = dates;
            let charts = self.getChartLabels(dates, dateType);

            // 차트 소수점 조회
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            // 그래프 출력
            chartEfficiency.clear();
            //chartEfficiency.setUnit(DEFAULT_UNIT);
            chartEfficiency.update(charts['labels'], tempDate, graphs, CHART_LABELS, CHART_COLORS, decimalPoint);
        },
        clearLabel: function()
        {
            $.each(USAGE_LABELS, function(index, item) {
                item.text(0);
            });
        },
        clearChart: function()
        {
            chartEfficiency.clear();
            chartEfficiency.update();
        },
        onCalendarChange: function($this)
        {
            let self = control;

            self.selectedDate = $this.val();
        },
        onSearchBtnClicked: function()
        {
            let self = control;

            self.request();
        },
        onPeriodTypeClicked: function($this)
        {
            let self = control;

            self.selectedDateType = parseInt($this.val());
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
        getDate: function(year, month, day)
        {
            if (month < 10) {
                month = '0' + month;
            }

            if (day < 10) {
                day = '0' + day;
            }

            return year + '-' + month + '-' + day;
        },
        requestFacilityTable: function()
        {
            const FACILITY_DATA = CONFIGS['facility_item'];

            let trTags = [];
            let trString = '';

            /*
                let TH_TITLE = TABLE_TH_TITLES[0];
                if (RESULT_POWER_FACTOR === true) {
                    TH_TITLE = TABLE_TH_TITLES[1];
                    DEFAULT_UNIT = '%';
                }

                $("#power-factor-title").html(`${TH_TITLE}<br>(${DEFAULT_UNIT})`);
             */

            $.each(FACILITY_DATA, function(key, facilityName){
                let $spanFacilityStatus = $("<span></span>").attr({
                    "class" : "bl_lnb_grn"
                });

                let $spanFacilityUsed = $("<span></span>").attr({
                    "id" : "label_" + key+ "_usage"
                }).html(0);

                let $spanFacilityEffPercent = $("<span></span>").attr({
                    "id" : "label_" + key+ "_eff_percent"
                }).html("-");

                trTags.push("<tr>");
                trTags.push("<td>" + facilityName + "</td>");
                trTags.push("<td>" + $spanFacilityStatus[0].outerHTML + "</td>");
                trTags.push("<td>" + $spanFacilityUsed[0].outerHTML + "</td>");

                /*
                    if (RESULT_POWER_FACTOR === true) {
                        trTags.push("<td>" + $spanFacilityEffPercent[0].outerHTML + "</td>");
                    } else {
                    }
                */

                trTags.push("</tr>");

                let $facilitySelector = $("#label_" + key + "_usage");
                USAGE_LABELS.push($facilitySelector);
            });

            // 배열을 문자열로 변환
            trString = trTags.join('');

            // 추가
            $("#tbody_facility > tr").remove();
            $tbodyFacility.append(trString);
        },
        requestChartLegend: function()
        {
            let pTags = [];
            let pString = '';

            /*
                if (RESULT_POWER_FACTOR === true) {
                    DEFAULT_UNIT = '%';
                }
             */

            // 범주 동적 추가
            $.each(GRAPH_DATA, function (index, value) {
                let $spanColor = $("<span></span>");
                $spanColor.css('background-color', "rgb(" + CHART_COLORS[index] + ")");

                let $spanUnit = $("<span></span>").attr({
                    'class' : 'label_unit'
                }).html(DEFAULT_UNIT);

                pTags.push("<p>" + $spanColor[0].outerHTML + "" + CHART_LABELS[index] + "("+ $spanUnit[0].outerHTML +")</p>")
            });

            pString = pTags.join('');
            $divChartLegendStatus.html(pString);
        }
    };

    $dateSelect.on("change", function() {
        control.onCalendarChange($(this))
    });

    $btnSearch.on("click", function() {
        control.onSearchBtnClicked();
    });

    $periodType.on("click", function() {
        control.onPeriodTypeClicked($(this));
    });

    return control;
}