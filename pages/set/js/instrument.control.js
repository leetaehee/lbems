let control;

$(document).ready(function() {
    control = createControl();
    control.requestMonitoringEvent();
    control.request();
});

function createControl()
{
    let control = {
        selectedFloor: defaultFloor,
        selectedStartPage: startPage,
        selectedTotalPage: 0,
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'floor', value: self.selectedFloor });
            data.push({ name: 'start_page', value: self.selectedStartPage });
            data.push({ name: 'view_page_count', value: viewPageCount });

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
            self.data = data;

            // 계측기 모니터링
            self.updateInstrumentMonitor();
            // 계측기 세부 정보
            self.updateInstrumentDetailStatus();
        },
        updateInstrumentMonitor: function()
        {
            let self = control;

            const monitoringData = self.data['monitoring_status'];
            const floors = monitoringData['floors'];

            const totalCount = parseInt(monitoringData['total_count']);
            const defectCount = parseInt(monitoringData['defect_count']);

            $labelTotalInstrumentCount.html(module.utility.addComma(totalCount));
            $labelInstrumentNomalCount.html(module.utility.addComma(totalCount - defectCount));
            $labelInstrumentDefectCount.html(module.utility.addComma(defectCount));

            $.each(floors, function(key, value) {
                let btnId = `${MONITORING_BTN_PREFIX}${key}`;

                if (value > 0) {
                    // 장애 내역이 존재 할 경우 표시..
                    $("#" + btnId + "> .icon_alarm")
                        .removeClass(MONITORING_NORMAL_CLASS)
                        .addClass(MONITORING_ERROR_CLASS);
                }
            });
        },
        updateInstrumentDetailStatus: function()
        {
            let self = control;

            const detailData = self.data['detail_status'];
            const sensorAlias = self.data['sensor_alias'];

            const list = detailData['list'];
            const dataCount = detailData['count'];

            let $tables = [];
            let $tableString = '';

            let $trTags = [];

            $("#tbody_instrument > tr").remove();

            if (dataCount < 1) {
                let emptyTd = $("<td></td>")
                    .prop('colspan', 7)
                    .prop('id', 'empty-table-td')
                    .html(EMPTY_TD_CONTENTS);

                $tables.push(`<tr>${emptyTd[0].outerHTML}</tr>`);
                $tableString = $tables.join('');
            }

            if (dataCount > 0) {
                $.each(list, function(index, items) {
                    $trTags = [];

                    let floor = items['home_grp_pk'];
                    let alarmOnOff = items['alarm_on_off'];

                    let alarmOnOffColorClass = alarmOnOff === '오류' ? WARN_COLOR_SELECTOR : '';
                    let warnMonitorBgClass = alarmOnOff === '오류' ? WARN_BG_SELECTOR : '';

                    let sensorNo = items['sensor_sn'];
                    if (sensorAlias[sensorNo] != undefined) {
                        sensorNo = sensorAlias[sensorNo];
                    }

                    $trTags.push(`<tr class="${warnMonitorBgClass} ${alarmOnOffColorClass}">`);
                    $trTags.push(`<td>${floorKeyData[floor]}</td>`);
                    $trTags.push(`<td>${items['memo']}</td>`);
                    $trTags.push(`<td>${items['energy_type']}</td>`);
                    $trTags.push(`<td>${sensorNo}</td>`);
                    $trTags.push(`<td>${items['installed_date']}</td>`);
                    $trTags.push(`<td>${alarmOnOff}</td>`);
                    $trTags.push(`<td>${items['alarm_msg']}</td>`);
                    $trTags.push(`</tr>`);

                    $tables.push($trTags.join(''));
                });
                $tableString = $tables.join('');
            }

            $tbodyInstrument.html($tableString);

            // 페이징처리
            let pageParam = {
                total: dataCount,
                currentPage: self.selectedStartPage,
                pageCount: pageCount,
                viewPageCount: viewPageCount,
                id: 'instrument_paging',
                key: 'instrument'
            };

            module.page(pageParam);

            // 마지막페이지
            self.selectedTotalPage = Math.ceil(dataCount/viewPageCount);
        },
        onClickMonitoringFloor: function(floor)
        {
            let self = control;

            self.selectedFloor = floor;
            self.selectedStartPage = startPage;
            self.request();
        },
        requestMonitoringEvent: function()
        {
            let self = control;

            let params = [];
            let data = [];

            params.push(
                {name: 'requester', value: requester},
                {name: 'request', value: 'monitoring_info'},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestMonitoringEventCallback,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestMonitoringEventCallback: function(data, params)
        {
            let self = control;

            let liTags = [];

            const MONITOR_SENSORS = data['monitor_sensors'];
            if (MONITOR_SENSORS.length < 1) {
                return;
            }

            $.each(MONITOR_SENSORS, function(index, items) {
                let floorKey = items['home_grp_pk'];
                let btnId = `${MONITORING_BTN_PREFIX}${floorKey}`;

                let floorName = floorKeyData[floorKey];

                let pClass = $("<p></p>")
                    .prop("class", 'icon_alarm')
                    .addClass(MONITORING_NORMAL_CLASS);

                let pText = $("<p></p>").html(floorName);

                let li = $("<li></li>")
                    .css('cursor', 'pointer')
                    .prop('id', btnId)
                    .html(`${pClass[0].outerHTML} ${pText[0].outerHTML}`);

                liTags.push(`${li[0].outerHTML}`);
            });

            // 아이콘 추가
            $divInstrumentConnectionGroup.html(liTags.join(''));
        },
    };

    $.each(floorKeyData, function(floor, value) {
        $(document).on('click', `#${MONITORING_BTN_PREFIX}${floor}`,function(){
            control.onClickMonitoringFloor(floor);
       });
    });

    $btnInstrumentFirstPage.on('click', function(){
        control.selectedStartPage = 1;
        control.request();
    });

    $btnInstrumentPrevPage.on('click', function(){
        control.selectedStartPage = Number(control.selectedStartPage - 1);
        control.request();
    });

    $btnInstrumentNextPage.on('click', function(){
        control.selectedStartPage = Number(control.selectedStartPage + 1);
        control.request();
    });

    $btnInstrumentLastPage.on('click', function(){
        control.selectedStartPage = Number(control.selectedTotalPage);
        control.request();
    });

    $(document).on('click', '.paging_click', function (e) {
        // 페이징번호 클릭시 해당 데이터 조회
        e.preventDefault();

        let self = control;
        let $id = $(this).prop('id');

        let tmp = $id.split('_');

        tmp[2] = Number(tmp[2]);

        self.selectedStartPage = tmp[2];
        self.request();
    });

    return control;
}