let control;

$(document).ready(function() {
    control = createControl();
    control.requestKeyInfo();
    control.requestShowDetail();
    control.request();
});

function createControl()
{
    let control = {
        selectedDateType: DEFAULT_DATE_TYPE,
        selectedFloor: DEFAULT_FLOOR,
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'date_type', value: self.selectedDateType });
            data.push({ name: 'floor', value: self.selectedFloor });

            params.push(
                {name: 'requester', value: requester},
                {name: 'request', value: command},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
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

            if (data['Error'] === 'Error') {
                return;
            }

            self.data = data;

            self.clearLabel();
            self.updateDiagram();
        },
        updateDiagram: function()
        {
            let self = control;
            let data = self.data;

            let usageData = data['usage_data'];
            let distributionData = data['distribution_data'];
            let floorData = data['floor_data'];
            let independenceData = data['independence_data'];

            // 사용량
            $.each(usageData, function(key, value) {
                $('#' + LABEL_PREFIX + key + USED_SUFFIX).html(module.utility.addComma(value.toFixed(0)));
            });

            // 분포도
            $.each(distributionData, function(key, value) {
                $('#' + LABEL_PREFIX + key + DISTRIBUTION_SUFFIX).html(module.utility.addComma(value));
            });

            // 층별
            $.each(floorData, function(key, value) {
                $('#' + LABEL_PREFIX + key + FLOOR_SUFFIX).html(module.utility.addComma(value.toFixed(0)));
            });

            // 에너지 자립률 및 등급 조회
            $labelBuildingName.html(`${CONFIGS['building_name']} BEMS`);
            $labelIndependenceGrade.html(independenceData['grade']);
            $labelIndependenceRate.html(independenceData['independence_rate']);
        },
        onPeriodButtonClicked: function($this, index)
        {
            let self = control;

            buttons.forEach(function(item, index) {
                item.removeClass("on");
            });
            $this.addClass("on")

            self.selectedDateType = index;
            self.request();
        },
        clearLabel: function()
        {
            $labelIndependenceGrade.html('-');
            $labelIndependenceRate.html(0);
            $labelAllFloorUsed.html(0);

            // 층
            $.each($labelFloors, function(index, item) {
                item.html(0);
            });

            // 사용량
            $.each($labelUseds, function(index, item) {
                item.html(0);
            });

            // 분포도
            $.each($labelDistributions, function(index, item) {
                item.html(0);
            });
        },
        requestShowDetail: function()
        {
            let self = control;

            if (IS_SHOW_DETAIL === false) {
                return;
            }

            $("#btn_show_detail_all").on("click", function(){
                self.selectedFloor = DEFAULT_FLOOR;
                self.request();
            });

            $.each(FLOOR_DATA, function(index, floor) {
                $("#btn_show_detail_" + floor).on("click", function(){
                    self.selectedFloor = floor;
                    self.request();
                });
            });
        },
        requestKeyInfo: function()
        {
            let self = control;

            let params = [];
            let data = [];

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: keyCommand},
                {name: "params", value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestKeyInfoCallback,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestKeyInfoCallback: function(data, params)
        {
            if (data['Error'] === 'Error') {
                return;
            }

            const USED_KEY_DATA = data['used_key_data'];
            const DISTRIBUTION_KEY_DATA = data['distribution_key_data'];
            const FLOOR_KEY_DATA = data['floor_key_data'];

            $.each(USED_KEY_DATA, function(index, item){
                let $labelUsed = $("#" + LABEL_PREFIX + item + USED_SUFFIX);
                $labelUseds.push($labelUsed);
            });

            $.each(DISTRIBUTION_KEY_DATA, function(index, item){
                let $labelDistribution = $("#" + LABEL_PREFIX + item + DISTRIBUTION_SUFFIX);
                $labelDistributions.push($labelDistribution);
            });

            $.each(FLOOR_KEY_DATA, function(index, item){
                let $labelFloor = $("#" + LABEL_PREFIX + item + FLOOR_SUFFIX);
                if ($labelFloor.val() === undefined) {
                    return true;
                }
                $labelFloors.push($labelFloor);
            });
        },
    };

    $btnDaily.on("click", function() {
        control.onPeriodButtonClicked($(this), DAILY_DATE_TYPE);
    });

    $btnMonth.on("click", function() {
        control.onPeriodButtonClicked($(this), MONTH_DATE_TYPE);
    });

    $btnYear.on("click", function() {
        control.onPeriodButtonClicked($(this), YEAR_DATE_TYPE);
    });

    return control;
}