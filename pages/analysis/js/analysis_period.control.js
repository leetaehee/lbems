let control;

$(document).ready(function() {
    let buildingInfo = module.BuildingManager();
    buildingInfo.request();

    control = createControl();
    control.requestChartLegend();
    control.requestEnergyGroupData();
});

function createControl()
{
    let control = {
        selectedOption: 0,
        selectedBuildingDong: defaultDong,
        selectedBuildingFloor: defaultFloor,
        selectedBuildingRoom: defaultRoom,
        request: function()
        {
            let self = control;
            let params = [];
            let data = [];

            data.push({ name: 'date_type', value: dateType });
            data.push({ name: 'floor_type', value: self.selectedBuildingFloor });
            data.push({ name: 'room_type', value: self.selectedBuildingRoom });
            data.push({ name: 'select_energy', value: $selectEnergy.val() });
            data.push({ name: 'select_usage', value: $selectUsage.val() });
            data.push({ name: 'select_facility', value: $selectFacility.val() });
            data.push({ name: 'dong_type', value: this.selectedBuildingDong });

            params.push(
                { name: 'requester', value: requester },
                { name: 'request', value: command },
                { name: 'params', value: JSON.stringify(data) }
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

            self.onSelectBoxChanged(0, 'energy', $selectEnergy.val());
            self.onSelectBoxChanged(1, 'usage', $selectUsage.val());
            self.onSelectBoxChanged(2, 'facility', $selectFacility.val());
        },
        updateBarChart(groupType, energyType, chart)
        {
            let self = control;
            if (self.data[groupType] === null) {
                return;
            }

            if (self.data[groupType].length === 0) {
                return;
            }

            let option = self.data[groupType][energyType]['option'];

            if (self.data == undefined) {
                transitionChart[chart].clear();
                transitionChart[chart].update();
                return;
            }

            let graphs = self.getChartData(groupType, energyType);
            if (graphs == undefined) {
                return;
            }

            const customUnits = self.data['custom_units'];
            const units = module.utility.getBemsUnits2();
            let unit = customUnits[energyType] !== undefined ? customUnits[energyType] : units[option];

            // 30일과 31일에 대한 예외처리
            let labelData = self.updateLabelDaysCheck(graphs['labels1'], graphs['labels2'], graphs['vals1'], graphs['vals2']);

            // 차트 소수점 설정
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            // 범주에 단위 설정
            $(".span_" + groupType + "_unit").html(unit);

            transitionChart[chart].clear();
            transitionChart[chart].setUnit(unit);
            transitionChart[chart].update(labelData['labels'], labelData['vals1'], labelData['vals2'], unit, CHART_LEGEND_LABELS, decimalPoint);
        },
        updateLabelDaysCheck: function(lasts, nows, vals1, vals2)
        {
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

            if (nowLength === lastLength) {
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
        getChartData: function(groupType, energyType)
        {
            let self = control;
            if (self.data[groupType] === null) {
                return;
            }

            let data = self.data[groupType][energyType];

            if (data === undefined) {
                return;
            }

            let now = data['now']['data'];
            let last = data['last']['data'];

            if (now === undefined && last === undefined) {
                return;
            }

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
                'labels2' : temp2.labels,
                'vals1' : vals1,
                'vals2' : vals2
            };
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
                        //tooltips.push(year + "년 " + label + "월 사용현황");
                    });
                    break;
                case 1:
                    //month
                    d.forEach(x => {
                        let day = x.substring(6, 8);
                        let label = day + "일";
                        labels.push(label);
                        //tooltips.push(year + "년 " + month + "월 " + day + "일 사용현황");
                    });
                    break;
                case 2:
                    //day
                    d.forEach(x => {
                        let hour = x.substring(8, 10);
                        let label = hour + "시";
                        labels.push(label);
                        //tooltips.push(year + "년 " + month + "월 " + day + "일 " + hour + "시 사용현황");
                    });
                    break;
                case 3: //day
                    d.forEach(x => {
                        let minute = x.substring(10, 12);
                        let label  = minute + "분";
                        labels.push(label);
                        //tooltips.push(year + "년 " + month + "월 " + day + "일 " + hour + "시 " + minute + "분 사용현황");
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
        clearChart: function()
        {
            let self = control;

            let len = charts.length;

            for(let i = 0; i < len; i++) {
                charts[i].clear();
                charts[i].update();
            }

            // 버튼은 추이 그래프에 디폴트가 잡히도록 한다.
            self.updateButtonClass($btnEnergyTransitionGraph, 'energy');
            self.updateButtonClass($btnUsageTransitionGraph, 'usage');
            self.updateButtonClass($btnFacilityTransitionGraph, 'facility');
        },
        onSelectBoxChanged: function(chart, groupType, energyType)
        {
            control.updateBarChart(groupType, energyType, chart);
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
        updateButtonClass: function($this, $buttonGroup)
        {
            let $id = $this.prop("id");

            // 모든 버튼 영역에  css 제거
            $("." + $buttonGroup + "_graph").removeClass("on");
            // 클릭한 버튼에 css 클래스 부여
            $("#" + $id).addClass("on");
        },
        onBarGraphClicked: function($this, $buttonGroup, chartNo)
        {
            let self = control;

            // 클릭효과 부여
            self.updateButtonClass($this, $buttonGroup);

            let energyType = $("#select_" + $buttonGroup).val();

            let graphs = self.getChartData($buttonGroup, energyType);
            if (graphs === undefined) {
                return;
            }

            let option = self.data[$buttonGroup][energyType]['option'];

            const customUnits = self.data['custom_units'];
            const units = module.utility.getBemsUnits2();
            let unit = customUnits[energyType] !== undefined ? customUnits[energyType] : units[option];

            // 30일과 31일에 대한 예외처리
            let labelData = self.updateLabelDaysCheck(graphs['labels1'], graphs['labels2'], graphs['vals1'], graphs['vals2']);

            // 차트 소수점 설정
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            // 추이그래프 삭제
            transitionChart[chartNo].clear();

            // 추이그래프 초기화
            charts[chartNo].clear();
            charts[chartNo].setUnit(unit);
            charts[chartNo].update(labelData['labels'], labelData['vals1'], labelData['vals2'], graphs['tooltip1'], graphs['tooltip2'], CHART_LEGEND_LABELS, decimalPoint);
        },
        onTransitionGraphClicked: function($this, $buttonGroup, chartNo)
        {
            let self = control;

            // 클릭효과 부여
            self.updateButtonClass($this, $buttonGroup);

            let energyType = $("#select_" + $buttonGroup).val()

            let graphs = self.getChartData($buttonGroup, energyType);
            if (graphs === undefined) {
                return;
            }

            let option = self.data[$buttonGroup][energyType]['option'];

            let units = module.utility.getBemsUnits2();
            let unit = units[option];

            // 30일과 31일에 대한 예외처리
            let labelData = self.updateLabelDaysCheck(graphs['labels1'], graphs['labels2'], graphs['vals1'], graphs['vals2']);

            // 차트 소수점 설정
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            // 누적그래프 삭제
            charts[chartNo].clear();

            // 추이그래프 초기화
            transitionChart[chartNo].clear();
            transitionChart[chartNo].setUnit(unit);
            transitionChart[chartNo].update(labelData['labels'], labelData['vals1'], labelData['vals2'], unit, CHART_LEGEND_LABELS, decimalPoint);
        },
        requestEnergyGroupData: function()
        {
            let self = control;
            let params = [];
            let data = [];

            params.push(
                {name: "requester", value: "analysis"},
                {name: "request", value: "analysis_group_info"},
                {name: "params", value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestEnergyGroupCallback,
                callbackParams: null,
                showAlert: true
            };

            self.clearChart();
            module.subRequest(requestParams);
        },
        requestEnergyGroupCallback: function(data, params)
        {
            let self = control;
            let keyData = data;

            if (keyData['Error'] === 'Empty') {
                return;
            }

            $divEnergyGroup.css('display', 'none');
            $divUsageGroup.css('display', 'none');
            $divFacilityGroup.css('display', 'none');

            const sensorTypeNo = data['sensor_type_no'];
            const units = module.utility.getBemsUnits2();

            $.each(keyData, function(key, value){
                let groupData = value;

                if (groupData.length === 0) {
                    return true;
                }

                $("#div_" + key + "_group").css('display', 'block');

                $.each(groupData, function(selectBoxKey, selectBoxValue){
                    $("#select_" + key)
                        .append($("<option></option>")
                            .attr("value", selectBoxKey)
                            .text(selectBoxValue['label']));
                });

                let selectedKey = $("#select_" + key).val();
                let option = sensorTypeNo[selectedKey];
                let unit = units[option];

                $(".span_" + key + "_unit").html(unit);
            });

            if (Object.keys(floorKeyData).length <= 1) {
                self.request();
            }
        },
        requestChartLegend: function()
        {
            let self = control;
            const option = self.selectedOption;

            // 단위는 기본적으로 kwh (로렌하우스 처럼 에너지원 항목에 단위 다른 경우 각 파트별로 단위 지정해서 디폴트 지정해야함
            // selectbox로 변경 시 단위가 바뀌는 경우 이벤트에서 할때마다 변경 해야 함
            const units = module.utility.getBemsUnits2();
            const unit = units[option];

            $.each(colorData, function (key, items) {
                let pTags = [];
                let pString = '';

                let spanPreviousColor = $("<span></span>");
                spanPreviousColor.css('background-color', "rgb(" + items['previous'] + ")");

                let spanCurrentColor = $("<span></span>");
                spanCurrentColor.css('background-color', "rgb(" + items['current'] + ")");

                let spanUnit = $("<span></span>");
                spanUnit.prop('class', `span_${key}_unit`).html(unit);

                pTags.push(`<p>${spanPreviousColor[0].outerHTML} ${CHART_LEGEND_LABELS[0]} (${spanUnit[0].outerHTML})</p>`);
                pTags.push(`<p>${spanCurrentColor[0].outerHTML} ${CHART_LEGEND_LABELS[1]} (${spanUnit[0].outerHTML})</p>`);

                pString = pTags.join('');
                $("#div_chart_legend_" + key).html(pString);

            });
        },
    };

    $btnSearch.on("click", function() {
        control.onSearchButtonClicked();
    });

    $selectBuildingDong.on("change", function() {
        control.onSelectedBuildingInfoChanged('dong');
    })

    $selectBuildingFloor.on("change", function() {
        control.onSelectedBuildingInfoChanged('floor');
    });

    /*
        $selectBuildingRoom.on("change", function() {
            control.onSelectedBuildingInfoChanged('room');
        });
     */

    $btnEnergyTransitionGraph.on("click", function(){
        control.onTransitionGraphClicked($(this), "energy", 0);
    });

    $btnEnergyBarGraph.on("click", function(){
        control.onBarGraphClicked($(this), 'energy', 0);
    });

    $btnUsageTransitionGraph.on("click", function(){
        control.onTransitionGraphClicked($(this), "usage", 1);
    });

    $btnUsageBarGraph.on("click", function(){
        control.onBarGraphClicked($(this), "usage", 1);
    });

    $btnFacilityTransitionGraph.on("click", function(){
        control.onTransitionGraphClicked($(this), "facility", 2);
    });

    $btnFacilityBarGraph.on("click", function(){
        control.onBarGraphClicked($(this), "facility",2);
    });

    $(document).on("change", "#select_energy", function(){
        control.request();
    });

    $(document).on("change", "#select_usage", function(){
        control.request();
    });

    $(document).on("change", "#select_facility", function(){
        control.request();
    });

    return control;
}
