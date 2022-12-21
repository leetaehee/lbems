let control;

$(document).ready(function() {
    module.utility.initYearSelect($selectYear, gServiceStartYm);

    control = createControl();
    control.requestChartLabel();
    control.clearChart();
    control.request();
});

function createControl()
{
    let control = {
        selectedOption: defaultOption,
        selectedDate: $selectYear.val(),
        chartOption: 0,
        request: function()
        {
            let self = control;
            let params = [];
            let data = [];

            data.push({ name: 'option', value: self.selectedOption });
            data.push({ name: 'chart', value: self.chartOption });
            data.push({ name: 'date', value: self.selectedDate });

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: command},
                {name: "params", value: JSON.stringify(data)}
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
            control.updateFloorStackChart(data, params);
        },
        updateFloorStackChart(data, chart)
        {
            let self = control;

            let dates = [];
            let keys = [];
            let saves = [];
            let labels = FLOOR_DATA;

            $.each(data, function(key, items){
                let data = items['data'];

                let tempKeys = Object.keys(data);
                let values = Object.values(data);

                keys.push(tempKeys);
                dates.push(tempKeys);
                saves.push(values);
            });

            if (dates[0] !== undefined) {
                let tempLabels = self.getChartLabels(dates[0], chart);
                dates = tempLabels['labels'];
                const decimalPoint = module.utility.getDecimalPointFromDateType(chart);

                // 차트보여주기
                charts[chart].update(dates, keys[0], saves, labels, colors, decimalPoint);
            }

            if (dates[0] === undefined) {
                charts[chart].clear();
                charts[chart].update();
            }
        },
        getChartLabels: function(d, chart)
        {
            let labels = [];
            let tooltips = [];

            switch(chart)
            {
                case 0:
                    //year
                    d.forEach(x => {
                        let label = x.substring(4, 6);
                        labels.push(label + "월");
                        let year  = x.substring(0, 4);
                        tooltips.push(year + "년 " + label + "월 사용");
                    });
                    break;
                case 1:
                    //month
                    d.forEach(x => {
                        let year  = x.substring(0, 4);
                        let month = x.substring(4, 6);
                        let day = x.substring(6, 8);
                        let label = month + "/" + day;
                        labels.push(label);

                        tooltips.push(year + "년 " + month + "월 " + day + "일 사용");
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

                        tooltips.push(year + "년 " + month + "월 " + day + "일 " + hour + "시 사용");
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

                        tooltips.push(year + "년 " + month + "월 " + day + "일 " + hour + "시 " + minute + "분 사용");
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
            self.selectedDate = date;

            self.request();
        },
        onSelectYearChanged: function($this)
        {
            let self = control;

            self.chartOption = 0;
            self.selectedDate = $this.val();

            self.request();
        },
        onExcelButtonClicked: function(index)
        {
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
        requestChartLabel: function()
        {
            let self = control;

            const floorInfo = CONFIGS['report']['floor_menu']['floor_color'];
            const floorKeyData = CONFIGS['floor_key_data'];
            const electricFloorValues = Object.values(CONFIGS['electric_floor_key_data']);

            let pTags = [];
            let pString = '';

            const units = module.utility.getBemsUnits2();
            const unit = units[self.selectedOption];

            $.each(floorInfo, function(floor, color){
                let $spanFloorLabel = $("<span></span>")
                    .css("background-color", `rgba(${color})`);

                let floorName = floorKeyData[floor];
                if (jQuery.inArray(floorName, electricFloorValues) === -1) {
                    return true;
                }

                pTags.push(`<p>${$spanFloorLabel[0].outerHTML} ${floorName} (${unit})</p>`);
            });

            // 배열을 문자열로 변환
            pString = pTags.join('');

            $(".div_chart_label > p").remove();
            $divChartLabel.html(pString);
        },
    };

    let units = module.utility.getBemsUnits2();
    let unit  = units[defaultOption];
    charts.forEach(function(item, index, array) {
        item._callback = control.onBarLineChartClicked;
        item.setUnit(unit);
    });

    unitLabels.forEach(function(c, index) {
        c.text(unit);
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
