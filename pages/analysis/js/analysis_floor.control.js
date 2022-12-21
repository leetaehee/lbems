let control;

$(document).ready(function() {
    createDatepicker();

    control = createControl();
    control.clearChart();
    control.requestUsedByFloorTable();
    control.requestPriceByFloorTable();
    control.requestChartLabel();

    let makeEnergyButton = module.makeEnergyButton(false);
    makeEnergyButton._callback = control.onMainButtonClicked;
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
        selectedOption: defaultOption,
        selectedDateType: $(radioCheckedName).val(),
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];
            let dateType = $(radioCheckedName).val();

            self.selectedDateType = parseInt(dateType);

            data.push({name: 'option', value: self.selectedOption});
            data.push({name: 'datetype', value: self.selectedDateType});
            data.push({name: 'date', value: $dateSelect.val()});

            params.push(
                {name: 'requester', value: requester},
                {name: 'request', value: command},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestCallback,
                callbackParams: dateType,
                showAlert: true
            };

            module.request(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;
            self.data = data;

            // 모든 그래프 초기화
            self.clearChart();
            // 사용량 정보 출력
            self.updateUsedChart();
            // 요금 정보 출력
            self.updatePriceChart();
            // 표로 데이터 보여주기
            self.updateFloorDataByTable();
        },
        updateUsedChart: function(chartNo = 0)
        {
            let self = control;

            let dateType = self.selectedDateType;
            let option = self.selectedOption;

            let units = module.utility.getBemsUnits2();
            let unit = units[option];

            let data = self.data;

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

            // 단위 변경
            usedCharts[chartNo].setUnit(unit);

            if (dates[0] !== undefined) {
                let tempLabels = self.getChartLabels(dates[0], dateType);
                dates = tempLabels['labels'];

                // 차트보여주기
                usedCharts[chartNo].update(dates, keys[0], saves, labels, colors);
            }

            if (dates[0] === undefined) {
                usedCharts[chartNo].update();
            }
        },
        updatePriceChart: function(chartNo = 0)
        {
            let self = control;

            let dateType = self.selectedDateType;
            let data = self.data;

            let dates = [];
            let keys = [];
            let saves = [];
            let labels = FLOOR_DATA;

            $.each(data, function(key, items){
                let dateData = items['data'];
                let data = items['price'];

                let tempKeys = Object.keys(dateData);
                let values = Object.values(data);

                keys.push(tempKeys);
                dates.push(tempKeys);
                saves.push(values);
            });

            // 단위를 금액으로 변경
            priceCharts[chartNo].setUnit('원');

            if (dates[0] !== undefined) {
                let tempLabels = self.getChartLabels(dates[0], dateType);
                dates = tempLabels['labels'];

                // 차트보여주기
                priceCharts[chartNo].update(dates, keys[0], saves, labels, colors);
            }

            if (dates[0] === undefined) {
                priceCharts[chartNo].update();
            }
        },
        updateFloorDataByTable: function()
        {
            let self = control;
            let data = self.data;
            let dateType = self.selectedDateType;

            $.each(data, function(key, items){
                let usedSum = items['total_info']['used'];

                let priceSum = items['total_info']['price'];
                if (dateType === 0) {
                    priceSum = module.utility.getSumOfValues(items['price']);
                }

                let usageValues = module.utility.makeZeroArray(items['data']);
                let priceValues = module.utility.makeZeroArray(items['price']);

                let usedAverage = module.utility.getArrayAverage(usageValues);
                let priceAverage = module.utility.getArrayAverage(priceValues);

                // 사용량 정보 출력
                $("#label_" + key + "_used_sum").html(module.utility.addComma(usedSum.toFixed(0)));
                $("#label_" + key + "_used_average").html(module.utility.addComma(usedAverage));

                // 요금 정보 출력
                $("#label_" + key + "_price_sum").html(module.utility.addComma(priceSum.toFixed(0)));
                $("#label_" + key + "_price_average").html(module.utility.addComma(priceAverage));
            });
        },
        getChartLabels: function(d, chart)
        {
            let labels   = [];
            let tooltips = [];

            switch(chart)
            {
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
                        let day = x.substring(6, 8);
                        let label = day + "일";
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
                default:
                    break;
            }

            return {
                labels: labels,
                tooltips: tooltips
            };
        },
        onSearchButtonClicked: function()
        {
            let self = control;

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

            let units = module.utility.getBemsUnits2();
            let unit = units[index];

            // 단위
            $labelUnitNow.html(unit);
            //self.request();
        },
        updateButtonClass: function($this, $buttonGroup)
        {
            let $id = $this.prop("id");

            // 모든 버튼 영역에  css 제거
            $("." + $buttonGroup + "_graph").removeClass("on");
            // 클릭한 버튼에 css 클래스 부여
            $("#" + $id).addClass("on");
        },
        onUsedGraphClicked: function($this, $buttonGroup, chartNo)
        {
            let self = control;
            let used = self.data;

            let dateType = $(radioCheckedName).val();

            // 클릭효과 부여
            self.updateButtonClass($this, $buttonGroup);

            if (used === undefined) {
                return;
            }

            // 그래프 초기화
            usedCharts[0].clear();
            usedCharts[1].clear();

            // 사용량 그래프
            self.updateUsedChart(chartNo);
        },
        onPriceGraphClicked: function($this, $buttonGroup, chartNo)
        {
            let self = control;
            let option = self.selectedOption;
            let used = self.data;

            let dateType = $(radioCheckedName).val();

            // 클릭효과 부여
            self.updateButtonClass($this, $buttonGroup);

            if (used === undefined) {
                return;
            }

            // 그래프 초기화
            priceCharts[0].clear();
            priceCharts[1].clear();

            // 선 그래프- 사용량
            self.updatePriceChart(chartNo);
        },
        clearChart: function()
        {
            let self = control;
            let chartLength = usedCharts.length;

            for (let i = 0; i < chartLength; i++) {
                priceCharts[i].clear();
                usedCharts[i].clear();
            }

            priceCharts[0].update();
            usedCharts[0].update();

            // 버튼은 추이 그래프에 디폴트가 잡히도록 한다.
            self.updateButtonClass($btnUsedLineGraph, 'used', 0);
            self.updateButtonClass($btnPriceLineGraph, 'price', 0);
        },
        requestUsedByFloorTable: function()
        {
            let self = control;
            const option = self.selectedOption;

            const FLOOR_INFOS = CONFIGS['floor_key_data'];
            const FLOORS = CONFIGS['floor'];
            const ELECTRIC_FLOOR_KEYS = Object.values(CONFIGS['electric_floor_key_data']);

            let trUsedTags = [];
            let trUsedString = '';

            $.each(FLOORS, function(index, floor){
                let floorName = FLOOR_INFOS[floor];
                if (jQuery.inArray(floorName, ELECTRIC_FLOOR_KEYS) === -1 && option === 0) {
                    return true;
                }

                let $spanLabelUsed = $("<span></span>").attr({
                   id: 'label_' + floor + "_used_sum"
                }).html(0);

                let $spanLabelUnit = $("<span></span>").attr({
                   class: 'label_unit_now'
                }).html('kWh');

                let $spanLabelAverage = $("<span></span>").attr({
                   id: 'label_' + floor + '_used_average'
                }).html(0);

                trUsedTags.push("<tr>");
                trUsedTags.push("<td>" + floorName +"</td>");
                trUsedTags.push("<td>" + $spanLabelUsed[0].outerHTML + " " + $spanLabelUnit[0].outerHTML + "</td>");
                trUsedTags.push("<td>" + $spanLabelAverage[0].outerHTML + " " + $spanLabelUnit[0].outerHTML + "</td>");
                trUsedTags.push("</tr>");
            });

            // 배열을 문자열로 변환
            trUsedString = trUsedTags.join('');

            // 추가
            $("#tbody_used > tr").remove();
            $tbodyUsed.append(trUsedString);
        },
        requestPriceByFloorTable: function()
        {
            let self = control;
            const option = self.selectedOption;

            const FLOOR_INFOS = CONFIGS['floor_key_data'];
            const FLOORS = CONFIGS['floor'];
            const ELECTRIC_FLOOR_KEYS = Object.values(CONFIGS['electric_floor_key_data']);

            let trPriceTags = [];
            let trPriceString = '';

            $.each(FLOORS, function(index, floor){
                let floorName = FLOOR_INFOS[floor];
                if (jQuery.inArray(floorName, ELECTRIC_FLOOR_KEYS) === -1 && option === 0) {
                    return true;
                }

                let $spanLabelPrice = $("<span></span>").attr({
                    id: 'label_' + floor + "_price_sum"
                }).html(0);

                let $spanLabelUnit = $("<span></span>").attr({
                    class: 'label_unit_now'
                }).html('원');

                let $spanLabelAverage = $("<span></span>").attr({
                    id: 'label_' + floor + '_price_average'
                }).html(0);

                trPriceTags.push("<tr>");
                trPriceTags.push("<td>" + floorName +"</td>");
                trPriceTags.push("<td>" + $spanLabelPrice[0].outerHTML + " " + $spanLabelUnit[0].outerHTML + "</td>");
                trPriceTags.push("<td>" + $spanLabelAverage[0].outerHTML + " " + $spanLabelUnit[0].outerHTML + "</td>");
                trPriceTags.push("</tr>");
            });

            // 배열을 문자열로 변환
            trPriceString = trPriceTags.join('');

            // 추가
            $("#tbody_price > tr").remove();
            $tbodyPrice.append(trPriceString);
        },
        requestChartLabel: function()
        {
            let self = control;
            const option = self.selectedOption;

            const floorInfo = CONFIGS['analysis']['floor_menu']['floor_color'];
            const floorKeyData = CONFIGS['floor_key_data'];
            const floorNames = CONFIGS['floor_name'];
            const ELECTRIC_FLOOR_VALUES = Object.values(CONFIGS['electric_floor_key_data']);

            let pUsedTags = [];
            let pPriceTags = [];
            let pUsedString = '';
            let pPriceString = '';

            let index = 0;

            $.each(floorInfo, function(floor, color){
                if (jQuery.inArray(floorNames[index], ELECTRIC_FLOOR_VALUES) === -1 && option === 0) {
                    return true;
                }
                let $spanFloorLabel = $("<span></span>")
                    .css("background-color", `rgba(${color})`);

                let $spanUsedUnitNow = $("<span></span>").attr({
                   class: 'label_unit_now',
                }).html('kWh');

                let $spanPriceUnitNow = $("<span></span>").html('원');

                let floorName = floorKeyData[floor];

                pUsedTags.push("<p>" + $spanFloorLabel[0].outerHTML + "" + floorName + "(" + $spanUsedUnitNow[0].outerHTML + ")</p>");
                pPriceTags.push("<p>" + $spanFloorLabel[0].outerHTML + "" + floorName + "(" + $spanPriceUnitNow[0].outerHTML + ")</p>");

                index++;
            });

            // 배열을 문자열로 변환
            pUsedString = pUsedTags.join('');
            pPriceString = pPriceTags.join('');

            $("#div_chart_used_label > p").remove();
            $divChartUsedLabel.append(pUsedString);

            $("#div_chart_price_label > p").remove();
            $divChartPriceLabel.append(pPriceString);
        },
    };

    $btnSearch.on("click", function() {
        control.onSearchButtonClicked();
    });

    $btnUsedLineGraph.on("click", function(){
        control.onUsedGraphClicked($(this), "used", 0);
    });

    $btnUsedBarStackGraph.on("click", function(){
        control.onUsedGraphClicked($(this), "used", 1);
    });

    $btnPriceLineGraph.on("click", function(){
        control.onPriceGraphClicked($(this), "price", 0);
    });

    $btnPriceBarStackGraph.on("click", function(){
        control.onPriceGraphClicked($(this), "price", 1);
    });

    return control;
}
