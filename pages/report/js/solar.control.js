let control;

$(document).ready(function() {
    module.utility.initYearSelect($selectYear, gServiceStartYm);

    control = createControl();
    control.requestChartLegend();
    control.request();
});

function createControl()
{
    let control = {
        selectedOption: defaultOption,
        date: $selectYear.val(),
        chartOption: 0,
        request: function()
        {
            let self = control;
            let params = [];
            let data = [];

            data.push({name: 'option', value: self.selectedOption});
            data.push({name: 'chart', value: self.chartOption});
            data.push({name: 'date', value: self.date});

            params.push(
                {name: 'requester', value: requester},
                {name: 'request', value: command},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestCallback,
                callbackParams: self.chartOption,
                showAlert: true
            };

            self.clearChart();
            module.request(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;
            self.updateBarLineChart(data, params);
        },
        updateBarLineChart(data, chart)
        {
            let self = control;
            let d = data.data;
            let price = data.price;
            let dates = Object.keys(d);
            let vals  = Object.values(d);
            let temp = self.getChartLabels(dates, chart);
            const decimalPoint = module.utility.getDecimalPointFromDateType(chart);

            charts[chart].update(temp.labels, dates, vals, price, temp.tooltips, decimalPoint);
        },
        getChartLabels: function(d, chart)
        {
            let labels = [];
            let tooltips = [];

            switch(chart) {
                case 0: //year
                    d.forEach(x => {
                        let label = x.substring(4, 6);
                        labels.push(label + "월");
                        let year  = x.substring(0, 4);
                        tooltips.push(year + "년 " + label + "월 발전");
                    });
                    break;
                case 1: //month
                    d.forEach(x => {
                        let year = x.substring(0, 4);
                        let month = x.substring(4, 6);
                        let day = x.substring(6, 8);
                        let label = month + "/" + day;
                        labels.push(label);

                        tooltips.push(year + "년 " + month + "월 " + day + "일 발전");
                    });
                    break;
                case 2: //day
                    d.forEach(x => {
                        let year = x.substring(0, 4);
                        let month = x.substring(4, 6);
                        let day = x.substring(6, 8);
                        let hour = x.substring(8, 10);
                        let label = hour + "시";
                        labels.push(label);

                        tooltips.push(year + "년 " + month + "월 " + day + "일 " + hour + "시 발전");
                    });
                    break;
                case 3: //day
                    d.forEach(x => {
                        let year = x.substring(0, 4);
                        let month = x.substring(4, 6);
                        let day = x.substring(6, 8);
                        let hour = x.substring(8, 10);
                        let minute = x.substring(10, 12);
                        let label = minute + "분";
                        labels.push(label);

                        tooltips.push(year + "년 " + month + "월 " + day + "일 " + hour + "시 " + minute + "분 발전");
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
        onBarLineChartClicked: function(params)
        {
            let self = control;
            let chart = params.mode;
            let date = params.date;

            if (chart >= 4) {
                return;
            }

            self.chartOption = chart;
            self.date = date;

            self.request();
        },
        onSearchButtonClicked: function()
        {
            let self = control;
            let select = $selectYear.val();
            self.chartOption = 0;
            self.date = select;

            if (select == "") {
                alert(ErrNotSelectedYear);
                return;
            }

            self.request();
        },
        onSelectYearChanged: function($this)
        {
            let self = control;

            self.chartOption = 0;
            self.date = $this.val();

            self.clearChart();
            self.request();
        },
        onMainButtonClicked: function($this, index)
        {
            // 동적으로 생성한 버튼이므로 .define에서 정의 할 경우 인식을 못함.
            const $buttonGroup = $("#energy_btn_group > button");

            let buttons = new Array();
            let arrayIndex = $this.index();

            let self = control;
            self.selectedOption = index;
            self.chartOption = 0;
            self.date = $selectYear.val();

            // 동적 버튼 생성
            $.each($buttonGroup, function(index, item){
                let btnId = $(this).prop("id");
                let $id = $("#" + btnId);

                buttons[index] = $id;
            });

            buttons.forEach(function(item, index) {
                item.removeClass("on");
            });

            buttons[arrayIndex].addClass("on");

            let units = module.utility.getBemsUnits2();
            let unit  = units[index];

            charts.forEach(function(c, index) {
                c.setUnit(unit);
            });
			$(".label_unit").html(unit);
            
            self.clearChart();
            self.request();
        },
        onExcelButtonClicked: function(index)
        {
            let self = control;

            let d = charts[index].getData();

            let time = module.utility.getCurrentTime();
            let name = excelFileName + "_" + time + ".xlsx";

            module.excel().exportExcel(d, name);
        },
        clearChart: function()
        {
            let self = control;
            let len  = charts.length;

            for (let i = self.chartOption; i < len; i++) {
                charts[i].clear();
                charts[i].update();
            }
        },
        requestChartLegend: function()
        {
            const keys = Object.keys(chartData['bar']);
            const barColorValues = Object.values(chartData['bar']);
            const lineColorValues = Object.values(chartData['line']);

            $.each(keys, function (index, value) {
                let pTags = [];
                let pString = '';

                let $spanUsedColor = $("<span></span>");
                $spanUsedColor.css('background-color', "rgb(" + barColorValues[index] + ")");

                let $spanUsedUnit = $("<span></span>").attr({
                    'class': 'label_unit'
                }).html('kWh');

                let $spanPriceColor = $("<span></span>");
                $spanPriceColor.css('background-color', "rgb(" + lineColorValues[index] + ")");

                let $spanPriceUnit = $("<span></span>").html('원');

                pTags.push("<p>" + $spanUsedColor[0].outerHTML + "" + chartSolarUsedLabel +"(" + $spanUsedUnit[0].outerHTML + ")</p>");
                pTags.push("<p>" + $spanPriceColor[0].outerHTML + "" + chartSolarPriceLabel +"(" + $spanPriceUnit[0].outerHTML + ")</p>");

                pString = pTags.join('');

                divChartLegends[index].html(pString);
            });
        },
    };

    let units = module.utility.getBemsUnits2();
    let unit  = units[btnStartIndex];
    charts.forEach(function(item, index, array) {
        item._callback = control.onBarLineChartClicked;
        item.setUnit(unit);
    });

    $btnSearch.on("click", function() {
        control.onSearchButtonClicked();
    });

    $selectYear.on("change", function() {
        control.onSelectYearChanged($(this));
    });

    excelButtons.forEach(function(item, index) {
        item.on("click", function() {
            control.onExcelButtonClicked(index);
        })
    });

    return control;
}
