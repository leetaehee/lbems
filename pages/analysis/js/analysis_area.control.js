let control;

$(document).ready(function() {
    createDatepicker();

    control = createControl();
    control.requestChartLegend();
    control.clearLabels();
    control.setLabelUnits();

    let buildingManager = module.BuildingManager();
    buildingManager.setEnergyKey(defaultEnergyKey);
    buildingManager.request(defaultOption);

    let makeEnergyButton = module.makeEnergyButton(CONFIGS['auto_loading']);
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
        selectedBuildingFloor: defaultFloor,
        selectedBuildingRoom: defaultRoom,
        selectedEnergyKey: defaultEnergyKey,
        clearLabels: function()
        {
            let self = control;

            $labelUsageNow.text("0");
            $labelUsageLast.text("0");
            $labelDiff.text("0");
            $labelPercentValueTop.text("0");
            $canvasChartMiddle.text("0");
            $labelDiffUsageLast.text("0");
            $labelDiffUsageNow.text("0");
            $labelDiffMiddle.text("0");
            $labelPercentValueMiddle.text("0");
            //$canvasChartBottom.text("0");
            //$labelDiffPriceLast.text("0");
            //$labelDiffPriceNow.text("0");
            //$labelDiffBottom.text("0");
            //$labelPercentValueBottom.text("0");

            self.updateButtonClass($btnUsedTransitionGraph, "used");
            self.updateButtonClass($btnPriceTransitionGraph, "price");
        },
        setLabelUnits: function()
        {
            let self  = control;
            let units = module.utility.getBemsUnits();
            let unit  = units[self.selectedOption];

            $labelUnitLast.text(unit);

            $labelUnitNow.text(unit);
            $labelUnitDiff.text(unit);

            $labelDiffUnitLastMiddle.text(unit);
            $labelDiffUnitNowMiddle.text(unit);
            $labelDiffUnitMiddle.text(unit);
        },
        request: function()
        {
            let self = control;
            let params = [];
            let data = [];
            let dateType = $(radioCheckedName).val();

            data.push({name: 'option', value: self.selectedOption});
            data.push({name: 'datetype', value: dateType});
            data.push({name: 'date', value: $dateSelect.val()});
            data.push({name: 'floor_type', value: self.selectedBuildingFloor});
            data.push({name: 'room_type', value: self.selectedBuildingRoom});
            data.push({name: 'energy_key', value: self.selectedEnergyKey });

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

            self.clearLabels();
            self.clearChart();
            module.request(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;

            self.data = data;

            self.updateLabels(data, params);
            // ???????????? ?????? ?????????
            self.updateUsedChart(data, params);
            // ??????????????? ?????? ?????????
            //self.updatePriceChart(data, params);
        },
        updateLabels: function(data, params)
        {
            let self = control;
            let dateType = parseInt(params);

            let now = data.now.data;
            let nowPrice = data.now.price;

            let last = data.last.data;
            let lastPrice = data.last.price;

            let vals1 = module.utility.makeZeroArray(last);
            let vals2 = module.utility.makeZeroArray(now);

            //?????? ????????? ?????? ????????? ??????
            lastSum = module.utility.getArrayAverage(vals1);
            nowSum = module.utility.getArrayAverage(vals2);

            diff = nowSum - lastSum;
            percent = module.utility.getDiffPercent(nowSum, lastSum);

            self.updatePercentMark($labelPercentColorMiddle, $labelPercentMiddle, $labelDiffMiddle, $labelPercentValueMiddle, diff, percent);

            $labelDiffUsageLast.text(module.utility.addComma(lastSum));
            $labelDiffUsageNow.text(module.utility.addComma(nowSum));

            self.updateDivChart($barChartUsageLast, $barChartUsageNow, lastSum, nowSum, percent);

            //?????? ????????? ?????? ???????????? ??????
            //lastSum = module.utility.getArrayAverage(lastPrice);
            //nowSum = module.utility.getArrayAverage(nowPrice);
            //diff = nowSum - lastSum;
            //percent = module.utility.getDiffPercent(nowSum, lastSum);

            //self.updatePercentMark($labelPercentColorBottom, $labelPercentBottom, $labelDiffBottom, $labelPercentValueBottom, diff, percent);

            //$labelDiffPriceLast.text(module.utility.addComma(lastSum));
            //$labelDiffPriceNow.text(module.utility.addComma(nowSum));

            //self.updateDivChart($barChartPriceLast, $barChartPriceNow, lastSum, nowSum, percent);
        },
        updatePercentMark: function($color, $marker, $diff, $val, diff, percent)
        {
            if (diff < 0) {
                $color.removeClass("fcRed");
                $color.addClass("fcprimary");

                $marker.removeClass("percent_up");
                $marker.addClass("percent_down");
                diff = Math.abs(diff);
                $diff.text("- " + module.utility.addComma(diff));
            } else {
                $color.removeClass("fcprimary");
                $color.addClass("fcRed");

                $marker.removeClass("percent_down");
                $marker.addClass("percent_up");

                $diff.text("+ " + module.utility.addComma(diff));
            }

            $val.text(percent);
        },
        updateDivChart($last, $now, val1, val2, percent)
        {
            let max = val1 > val2 ? val1 : val2;

            if (max == 0) {
                max = 1;
            }

            if (percent == 999) {
                //return;
            }

            val1 = parseInt(val1 / max * 100);
            va11 = module.utility.getValidPercent(val1);

            val2 = parseInt(val2 / max * 100);
            va12 = module.utility.getValidPercent(val2);

            $last.css("height", val1 + "%");
            $now.css("height", val2 + "%");
        },
        updateUsedChart: function(data, params)
        {
            let self = control;
            let option = self.selectedOption;
            let dateType = $(radioCheckedName).val();

            let units = module.utility.getBemsUnits();
            let unit = units[option];

            let graphs = self.getChartData(params);

            // 30?????? 31?????? ?????? ????????????
            let useds = self.updateLabelDaysCheck(graphs['labels1'], graphs['labels'], graphs['vals1'], graphs['vals2']);

            // ?????? ????????? ??????
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            transitionCharts[0].update(useds['labels'], useds['vals1'], useds['vals2'], unit, CHART_USED_LABEL, decimalPoint);
        },
        updatePriceChart: function(data, params)
        {
            let self = control;
            let graphs = self.getChartData(params);

            // 30?????? 31?????? ?????? ????????????
            let prices = self.updateLabelDaysCheck(graphs['labels1'], graphs['labels'], graphs['last_price'], graphs['now_price']);

            transitionCharts[1].update(prices['labels'], prices['vals1'], prices['vals2'], "???", CHART_PRICE_LABEL, 0);
        },
        updateLabelDaysCheck: function(lasts, nows, vals1, vals2)
        {
            let self = control;
            let period = $(radioCheckedName).val();

            let type = '';

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
                if (priority[i] !== compareLabels[i]) {
                    compareIndex = i;

                    compareVals.splice(compareIndex, 0, 0);
                    compareLabels.splice(compareIndex, 0, priority[compareIndex]);
                }
            }
            return {
                'labels' : compareLabels,
                'vals1' : type === 'prev' ?  priorityVals : compareVals,
                'vals2' : type === 'current' ? priorityVals : compareVals
            };
        },
        getChartData: function(params)
        {
            let self = control;
            let data = self.data;

            if (data === undefined) {
                return;
            }

            let dateType = parseInt(params);

            let now = data.now.data;
            let nowPrice = data.now.price;

            let last = data.last.data;
            let lastPrice = data.last.price;

            let dates1 = Object.keys(last);
            let vals1 = Object.values(last);
            let dates2 = Object.keys(now);
            let vals2 = Object.values(now);
            let temp = self.getChartLabels(dates1, dateType);
            let temp2 = self.getChartLabels(dates2, dateType);

            return {
                'tooltip1' : temp.tooltips,
                'tooltip2' : temp2.tooltips,
                'labels1' : temp.labels,
                'labels' : temp2.labels,
                'vals1' : vals1,
                'vals2' : vals2,
                'now_price' : nowPrice,
                'last_price' : lastPrice,
            };
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
                        labels.push(label + "???");
                    });
                    break;
                case 1:
                    //month
                    d.forEach(x => {
                        let day = x.substring(6, 8);
                        let label = day + "???";
                        labels.push(label);
                    });
                    break;
                case 2:
                    //day
                    d.forEach(x => {
                        let hour = x.substring(8, 10);
                        let label = hour + "???";
                        labels.push(label);
                    });
                    break;
                case 3:
                    //day
                    d.forEach(x => {
                        let minute = x.substring(10, 12);
                        let label = minute + "???";
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
        onBarLineChartClicked: function(params)
        {
            let self  = control;
            let chart = params.mode;
            let date  = params.date;

            if (chart >= 4) {
                return;
            }

            self.date = date;
            self.request();
        },
        onSearchButtonClicked: function()
        {
            let self = control;

            self.request();
        },
        onMainButtonClicked: function($this, index)
        {
            let buildingManager = module.BuildingManager();

            // ???????????? ????????? ??????????????? .define?????? ?????? ??? ?????? ????????? ??????.
            const $buttonGroup = $("#energy_btn_group > button");

            let buttons = new Array();
            let arrayIndex = $this.index();

            let self = control;

            let $id = $this.prop('id');
            let energyKey = self.getEnergyKeyName($id);

            self.selectedOption = index;
            self.selectedBuildingFloor = defaultFloor;
            self.selectedBuildingRoom = defaultRoom;
            self.selectedEnergyKey = energyKey;

            // ?????? ?????? ??????
            $.each($buttonGroup, function(index, item){
                let btnId =  $(this).prop('id');
                let $id = $('#' + btnId);

                buttons[index] = $id;
            });

            buttons.forEach(function(item, index) {
                item.removeClass("on");
            });

            buttons[arrayIndex].addClass("on");

            let units = module.utility.getBemsUnits();
            let unit  = units[index];

            charts.forEach(function(c, index) {
                c.setUnit(unit);
            });

            buildingManager.setEnergyKey(energyKey);
            buildingManager.request(index);

            self.clearLabels();
            self.setLabelUnits();
            self.clearChart();

            if (CONFIGS['auto_loading'] === true) {
                self.request();
            }
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
        clearChart: function()
        {
            let self = control;
            let len  = charts.length;

            for (let i = 0; i < len; i++) {
                transitionCharts[i].clear();
                transitionCharts[i].clear();
                charts[i].update();
                charts[i].update();
            }

            self.updateDivChart($barChartUsageLast, $barChartUsageNow, 0, 0, 0);
            self.updateDivChart($barChartPriceLast, $barChartPriceNow, 0, 0, 0);
        },
        updateButtonClass: function($this, $buttonGroup)
        {
            let $id = $this.prop("id");

            // ?????? ?????? ?????????  css ??????
            $("." + $buttonGroup + "_graph").removeClass("on");
            // ????????? ????????? css ????????? ??????
            $("#" + $id).addClass("on");
        },
        onSelectedFloorChanged: function(type)
        {
            let self = control;

            if (type === 'floor') {
                self.selectedBuildingFloor = $selectBuildingFloor.val();
                self.selectedBuildingRoom = defaultRoom;
            }

            if (type === 'room') {
                self.selectedBuildingFloor = $selectBuildingFloor.val();
                self.selectedBuildingRoom = $selectBuildingRoom.val();
            }
        },
        onBarGraphClicked: function($this, $buttonGroup, chartNo)
        {
            let self = control;
            let option = self.selectedOption;

            let units = module.utility.getBemsUnits();
            let unit  = units[option];

            let dateType = $(radioCheckedName).val();

            // ???????????? ??????
            self.updateButtonClass($this, $buttonGroup);

            // ??????????????? ?????????
            charts[chartNo].clear();
            // ??????????????? ?????????
            transitionCharts[chartNo].clear();

            // ????????? ????????? ??????
            let graphs = self.getChartData(dateType);
            if (graphs === undefined) {
                self.clearChart();
                return;
            }

            // ?????? ????????? ??????
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            // ???????????????- ?????????
            if (chartNo === 0) {
                // 30?????? 31?????? ?????? ????????????
                let useds = self.updateLabelDaysCheck(graphs['labels1'], graphs['labels'], graphs['vals1'], graphs['vals2']);

                charts[chartNo].setUnit(unit);
                charts[chartNo].update(useds['labels'], useds['vals1'], useds['vals2'], graphs['tooltip1'], graphs['tooltip2'], CHART_USED_LABEL, decimalPoint);
            }

            // ???????????????- ??????
            if (chartNo === 1) {
                // 30?????? 31?????? ?????? ????????????
                //let prices = self.updateLabelDaysCheck(graphs['labels1'], graphs['labels'], graphs['last_price'], graphs['now_price']);
                //charts[chartNo].update(prices['labels'], prices['vals1'], prices['vals2'], graphs['tooltip1'], graphs['tooltip2'], CHART_PRICE_LABEL, 0);
            }
        },
        onTransitionGraphClicked: function($this, $buttonGroup, chartNo)
        {
            let self = control;
            let option = self.selectedOption;

            let dateType = $(radioCheckedName).val();

            // ???????????? ??????
            self.updateButtonClass($this, $buttonGroup);

            // ??????????????? ?????????
            transitionCharts[chartNo].clear();
            // ??????????????? ?????????
            charts[chartNo].clear();

            let units = module.utility.getBemsUnits();
            let unit  = units[option];

            // ????????? ????????? ??????
            let graphs = self.getChartData(dateType);
            if (graphs === undefined) {
                self.clearChart();
                return;
            }

            // ?????? ????????? ??????
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            // ???????????????- ?????????
            if (chartNo == 0) {
                // 30?????? 31?????? ?????? ????????????
                let useds = self.updateLabelDaysCheck(graphs['labels1'], graphs['labels'], graphs['vals1'], graphs['vals2']);
                transitionCharts[0].update(useds['labels'], useds['vals1'], useds['vals2'], unit, CHART_USED_LABEL, decimalPoint);
            }

            // ???????????????- ??????
            if (chartNo == 1) {
                // 30?????? 31?????? ?????? ????????????
                //let prices = self.updateLabelDaysCheck(graphs['labels1'], graphs['labels'], graphs['last_price'], graphs['now_price']);
                //transitionCharts[1].update(prices['labels'], prices['vals1'], prices['vals2'], "???", CHART_PRICE_LABEL, 0);
            }
        },
        requestChartLegend: function()
        {
            // ????????? ?????? ?????? ?????? ?????? ?????? ??????
            const usedKeys = Object.keys(colorData['used']);
            const usedValues = Object.values(colorData['used']);

            // ???????????? ?????? ?????? ?????? ?????? ?????? ??????
            const priceKeys = Object.keys(colorData['price']);
            const priceValues = Object.values(colorData['price']);

            let pUsedTags = [];
            let pUsedString = '';
            let pPriceTags = [];
            let pPriceString = '';

            // ?????? ?????? ?????? ??????????????? ????????? ?????? ??????
            $.each(usedKeys, function (index, value){
                let spanUsed = $("<span></span>");
                spanUsed.css('background-color', "rgb(" + usedValues[index] + ")");

                let spanUnit = $("<span></span>").attr({
                    'class' : 'label_unit_now'
                }).html(USED_UNIT);

                pUsedTags.push(`<p>${spanUsed[0].outerHTML} ${CHART_USED_LABEL[index]}(${spanUnit[0].outerHTML})</p>`);
            });

            pUsedString = pUsedTags.join('');
            $divChartLegendUsed.html(pUsedString);

            // ??????????????? ?????? ????????? ?????? ?????? ??????
            $barChartUsageLast.css('background-color', "rgb(" + usedValues[0] + ")");
            $barChartUsageNow.css('background-color', "rgb(" + usedValues[1] + ")");

            // ?????? ?????? ?????? ??????????????? ???????????? ?????? ??????
            $.each(priceKeys, function (index, value){
                let spanPrice = $("<span></span>");
                spanPrice.css('background-color', "rgb(" + priceValues[index] + ")");

                let spanUnit = $("<span></span>").attr({
                    'class' : 'label_unit_now'
                }).html(PRICE_UNIT);

                pPriceTags.push(`<p>${spanPrice[0].outerHTML} ${CHART_PRICE_LABEL[index]}(${spanUnit[0].outerHTML})</p>`);
            });

            pPriceString = pPriceTags.join('');
            $divChartLegendPrice.html(pPriceString);

            // ??????????????? ?????? ???????????? ?????? ?????? ??????
            $barChartPriceLast.css('background-color', "rgb(" + priceValues[0] + ")");
            $barChartPriceNow.css('background-color', "rgb(" + priceValues[1] + ")");
        },
    };

    $btnSearch.on("click", function() {
        control.onSearchButtonClicked();
    });

    /*
        $selectBuildingFloor.on("change", function() {
            control.onSelectedFloorChanged('floor');
        });

        $selectBuildingRoom.on("change", function() {
            control.onSelectedFloorChanged('room');
        });
     */

    $btnUsedTransitionGraph.on("click", function(){
        control.onTransitionGraphClicked($(this), "used", 0);
    });

    $btnUsedBarGraph.on("click", function(){
        control.onBarGraphClicked($(this), "used", 0);
    });

    $btnPriceTransitionGraph.on("click", function(){
        control.onTransitionGraphClicked($(this), "price", 1);
    });

    $btnPriceBarGraph.on("click", function(){
        control.onBarGraphClicked($(this), "price", 1);
    });

    return control;
}