let control;
const facilityItemLength = Object.keys(CONFIGS['facility_item']).length;

$(document).ready(function() {
    createDatepicker();

    let buildingManager = module.BuildingManager();
    buildingManager.request();

    $divFindustGraphSection.css('display',isUseFinedustSensor === true ? 'block' : 'none');

    control = createControl();
    control.initialize();
    control.requestUsageByTable();
    control.requestFacilityByTable();
    control.request();
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
        dateType: 0,
        selectedBuildingDong: defaultDong,
        selectedBuildingFloor: defaultFloor,
        selectedBuildingRoom: defaultRoom,
        clearLabels: function()
        {
            $.each(LABELS_SELECTORS, function(index, item) {
                item.text(0);
            });
        },
        request: function()
        {
            let self = control;
            let params = [];
            let data = [];
            let dateType = $(radioCheckedName).val();

            data.push({ name: 'datetype', value: dateType });
            data.push({ name: 'date', value: $dateSelect.val() });
            data.push({ name: 'floor_type', value: self.selectedBuildingFloor });
            data.push({ name: 'room_type', value: self.selectedBuildingRoom });
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
                callbackParams: dateType,
                showAlert: true
            };

            self.dateType = parseInt(dateType);

            self.clearLabels();
            self.clearChart();

            module.request(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;
            self.data = data;

            if (self.data['Error'] === 'Error') {
                return;
            }

            if (isUseFinedustSensor === true) {
                self.updateHTLineChart(0);
            }

            self.updateEnergyLabel();
            self.updateUsageChart();

            if (facilityItemLength > 0) {
                self.updateFacilityChart();
            }
        },
        updateEnergyLabel: function()
        {
            let self = control;
            let energyMap = {};

            const floor = self.selectedBuildingFloor === 'all' ? 'all' : self.selectedBuildingFloor;

            const data = self.data['energy_group'];
            if (data === undefined) {
                return;
            }

            if (data['energy'] === undefined) {
                return;
            }

            const solarData = data['solar'];
            const units = module.utility.getBemsUnits2();

            $labelSolarUsed.html(module.utility.addComma(solarData['in']['data'].toFixed(0)));
            $labelSolarPrice.html(module.utility.addComma(solarData['in']['price']));

            $.each(data['energy'], function(key, items) {

                let used = items['data'];
                let price = items['price'];
                let option = items['option'];

                /*
                let fUsed = 0;
                if (items['current']['data'][floor] === undefined) {
                    fUsed = module.utility.getSumOfValues(items['current']['data']);
                } else {
                    fUsed = items['current']['data'][floor];
                }
                 */

                $("#label_" + key + "_used").html(module.utility.addComma(used.toFixed(0)));
                $("#label_" + key + "_price").html(module.utility.addComma(price));
                $("#label_" + key + "_unit" ).html(units[option]);

                energyMap[key] = used;
            });

            energyMap['solar'] = solarData['in']['data'];

            self.updateEnergyPercent(energyMap);
        },
        updateEnergyPercent: function(data)
        {
            let fcSum = 0;
            let fcData = data;

            if (isDisPlayEnergyPercent === undefined || isDisPlayEnergyPercent === false) {
                return;
            }

            $.each(data, function(key, value) {
                fcData[key] = module.utility.transToElectric(key, value);
                fcSum += fcData[key];
            });

            if (fcSum > 0) {
                $.each(data, function(key, value) {
                    let fcPercent = module.utility.getValidPercent((value/fcSum) * 100);
                    $("#label_" + key + "_percent").html(module.utility.addComma(fcPercent.toFixed(0)) + " %");
                });
            }
        },
        updateUsageChart: function()
        {
            let self = control;
            const floor = self.selectedBuildingFloor === 'all' ? 'all' : self.selectedBuildingFloor;

            const data = self.data['energy_group']['usage'];
            const percents = self.data['usages'];
            const usages = [];

            if (data === undefined) {
                return;
            }

            if (percents === undefined) {
                return;
            }

            const dateType = self.dateType;
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            let arrayInitIndex = 1;

            let usageLength = Object.keys(data).length + 1;
            let usageDistributions = Array.from({length:usageLength}, () => 0);

            $.each(data, function(k, items) {
                let used = items['data'];

                $('#label_' + k + '_used').html(module.utility.addComma(used.toFixed(0)));

                usages.push(parseInt(percents[k]));
            });

            const usageSum = module.utility.getSumOfValues(usages);
            $.each(percents, function(key, value) {
                usageDistributions[arrayInitIndex] = value.toFixed(decimalPoint);
                arrayInitIndex++;
            });

            if (usageSum < 1) {
                usageDistributions[0] = 100;
            }

            chartPieUsage.update(LABEL_USAGES, COLOR_USAGES, usageDistributions);
        },
        updateFacilityChart: function()
        {
            let self = control;
            const floor = self.selectedBuildingFloor === 'all' ? 'all' : self.selectedBuildingFloor;

            const data = self.data['energy_group']['facility'];
            let facilities = [];

            if (data === undefined) {
                return;
            }

            let facilityLength = Object.keys(data).length + 1;
            let facilityDistributions = Array.from({length:facilityLength}, () => 0);

            const dateType = self.dateType;
            const decimalPoint = module.utility.getDecimalPointFromDateType(dateType);

            let arrayInitIndex = 1;
            $.each(data, function(k, items) {
                let used = items['data'];

                $('#label_' + k + '_used').html(module.utility.addComma(used.toFixed(0)));

                facilities.push(used);
            });

            const facilitySum = module.utility.getSumOfValues(facilities);

            $.each(facilities, function (index, value) {
                let percent = module.utility.getValidPercent(value/facilitySum * 100);
                value = percent.toFixed(decimalPoint);

                if (value < 0) {
                    value = 0;
                }

                facilityDistributions[arrayInitIndex] = value;
                arrayInitIndex++;
            });

            if (facilitySum < 1) {
                facilityDistributions[0] = 100;
            }

            chartPieFacilities.update(LABEL_FACILITIES, COLOR_FACILITIES, facilityDistributions);
        },
        updateLineChart: function()
        {
            let self = control;

            let dateType = self.dateType;

            if (self.data['finedusts'] === undefined) {
                return;
            }

            let finedusts = self.data['finedusts'];
            let temperatures = finedusts['temperatures'];
            let humiditys = finedusts['humiditys'];

            let dates = Object.keys(temperatures);
            let temperatureData = Object.values(temperatures);
            let humidityData = Object.values(humiditys);
            let temp = self.getChartLabels(dates, dateType);

            chartLine.update(temp.labels, humidityData, temperatureData);
        },
        getChartLabels: function(d, chart)
        {
            let labels = [];
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
                        let day = x.substring(6, 8);
                        let label = day + "일";
                        labels.push(label);
                    });
                    break;
                case 2:
                    //day
                    d.forEach(x => {
                        let hour  = x.substring(8, 10);
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
        onSearchButtonClicked: function()
        {
            let self = control;
            
            self.request();
        },
        updateHTLineChart: function(index)
        {
            // 온,습도 그래프 조회
            let self = control;

            self.energyType = index;
            self.updateLineChart();
        },
        clearChart: function()
        {
            chartLine.clear();
            chartLine.update();

            chartPieUsage.clear();
            chartPieUsage.update(LABEL_USAGES, COLOR_USAGES, []);

            chartPieFacilities.clear();
            chartPieFacilities.update(LABEL_FACILITIES, COLOR_FACILITIES, []);
        },
        initialize: function()
        {
            if (isDisPlayEnergyPercent === undefined || isDisPlayEnergyPercent === false) {
                $(".spanPercentGroup").css('display', 'none');
            }

            if (selectorGasItemName !== undefined
                && jQuery.inArray(selectorGasItemName, GAS_TYPES) >= 0) {
                $("#div_gas_info").css('display', 'block');
            }
        },
        requestUsageByTable: function()
        {
            const USAGE_LABELS = CONFIGS['usage_labels'];
            const USAGE_COLORS = CONFIGS['usage_colors'];
            const USAGE_KEYS = CONFIGS['usage_key'];

            let trTags = [];
            let trString = '';

            $.each(USAGE_LABELS, function(index, value) {
                if (index === 0) {
                    return true;
                }

                let $spanItemName = $("<span></span>").attr({
                    'class' : 'colorchip mr05',
                });
                $spanItemName.css('background-color', "rgb(" + USAGE_COLORS[index] + ")");

                let $spanUsed = $("<span></span>").attr({
                    'id' : 'label_' + USAGE_KEYS[index] + '_used'
                }).html(0);

                let $spanLabelUnit = $("<span></span>").attr({
                    'id' : 'label_' + USAGE_KEYS[index] + '_unit'
                }).html('kWh');

                trTags.push("<tr>");
                trTags.push("<th>" + $spanItemName[0].outerHTML + " " + value + "</th>");
                trTags.push("<td>" + $spanUsed[0].outerHTML + " " + $spanLabelUnit[0].outerHTML +"</td>");
                trTags.push("</tr>");

                let $usageSelector = $("#label_" + USAGE_KEYS[index] + "_usage");
                LABELS_SELECTORS.push($usageSelector);
            });

            // 배열을 문자열로 변환
            trString = trTags.join('');

            // 추가
            $("#tbody_usage > tr").remove();
            $tbodyUsage.append(trString);
        },
        requestFacilityByTable: function()
        {
            const FACILITY_LABELS = CONFIGS['facility_labels'];
            const FACILITY_COLORS = CONFIGS['facility_colors'];
            const FACILITY_KEYS = CONFIGS['facility_key'];

            if (facilityItemLength < 1) {
                return;
            }

            let trTags = [];
            let trString = '';

            $.each(FACILITY_LABELS, function(index, value) {
                if (index === 0) {
                    return true;
                }

                let $spanItemName = $("<span></span>").attr({
                    'class' : 'colorchip mr05',
                });
                $spanItemName.css('background-color', "rgb(" + FACILITY_COLORS[index] + ")");

                let $spanUsed = $("<span></span>").attr({
                    'id' : 'label_' + FACILITY_KEYS[index] + '_used'
                }).html(0);

                let $spanLabelUnit = $("<span></span>").attr({
                    'id' : 'label_' + FACILITY_KEYS[index] + '_unit'
                }).html('kWh');

                trTags.push("<tr>");
                trTags.push("<th>" + $spanItemName[0].outerHTML + " " + value + "</th>");
                trTags.push("<td>" + $spanUsed[0].outerHTML + " " + $spanLabelUnit[0].outerHTML +"</td>");
                trTags.push("</tr>");

                let $facilitySelector = $("#label_" + FACILITY_LABELS[index] + "_usage");
                LABELS_SELECTORS.push($facilitySelector);
            });

            // 배열을 문자열로 변환
            trString = trTags.join('');

            // 추가
            $("#tbody_facility > tr").remove();
            $tbodyFacility.append(trString);
        },
    };

    $btnSearch.on("click", function() {
        control.onSearchButtonClicked();
    });

    $selectBuildingDong.on("change", function() {
       control.onSelectedBuildingInfoChanged('dong');
    });

    $selectBuildingFloor.on("change", function() {
        control.onSelectedBuildingInfoChanged('floor');
    });

    /*
        $selectBuildingRoom.on("change", function() {
            control.onSelectedBuildingInfoChanged('room');
        });
     */

    return control;
}
