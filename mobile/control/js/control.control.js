let control;

$(document).ready(function(){
    control = createControl();

    if (gIsDevMode === 0 && isReady === true) {
        control.requestRoomInfo();
        /*
        setInterval(function () {
            // 제어 상태 체크 5초마다 주기적으로 실행..
            control.setRequestControlStatus();
        }, SET_CONTROL_TIME_OUT);
         */
    }
});

function createControl()
{
    let control = {
        selectedRoomName: '', // 장소명칭
        selectedPowerOnOff: '', // 전원여부
        selectedFanSpeed: '', //  풍량 단계
        selectedMode: '', // 모드
        selectedTemperature: 0, // 온도
        selectedOperation: 0, // 기능수행정보
        selectedStatus: 0, // 변경값
        selectedChangedLoading: false, // 로딩 진행여부
        selectedFloorData: DEFAULT_EMPTY_ARRAY, // 층과 관련된 데이터
        selectedCompany : company,
        request: function(showLoadingBar = true)
        {
            let self = control;
            let params = [];
            let data = [];

            data.push({ name: 'room_name',  value: self.selectedRoomName });
            data.push({ name: 'current_floor', value: $selectFloorType.val() });
            data.push({ name: 'is_ready', value: isReady });
            data.push({ name: 'on_off_display', value: false });
            data.push({ name: 'company', value: self.selectedCompany });

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

            if (showLoadingBar === true) {
                module.request(requestParams);
            } else {
                module.subRequest(requestParams);
            }
        },
        requestCallback: function(data, params)
        {
            let self = control;

            if (data['control_error'] === 'Error') {
                return;
            }

            self.data = data;
            self.updateDeviceStatus();
        },
        updateDeviceStatus: function()
        {
            let self = control;

            let powerOnOffData = self.data['power_on_off'];
            let functionData = self.data['function_status'];

            let powerOnOffStatus = self.getRealBoolean(powerOnOffData[0]); // 전원
            let fanSpeed = parseInt(functionData[1]); // 풍량
            let mode = parseInt(functionData[0]); // 모드

            let $fanTargetSelector = "btn_fan_" + fanSpeed;
            let $modeTargetSelector = "btn_mode_" + mode;
            let temperature = parseInt(functionData[2]);

            // Device 전원 퍼블리싱 변경
            self.updatePowerChangePublish(powerOnOffStatus);

            // 온도 퍼블리싱 변경
            self.updateTemperaturePublish(temperature);

            // 풍량 초기화
            self.updateFanSpeedPublish($fanTargetSelector);

            // 모드 초기화
            self.updateModePublish($modeTargetSelector);

            self.selectedTemperature = temperature;
            self.selectedPowerOnOff = powerOnOffStatus;
            self.selectedFanSpeed = fanSpeed;
            self.selectedMode = mode;
        },
        updatePowerChangePublish: function(status)
        {
            if (status === true) {
                // 전원이 켜져 있는 경우 true 변경
                $checkboxPowerOnOff.prop("checked", true);
            } else {
                // 전원이 꺼져있는 경우 false 변경
                $checkboxPowerOnOff.prop("checked", false);
            }
        },
        updateTemperaturePublish: function(temperature)
        {
            if (temperature > SET_MAX_TEMPERATURE) {
                temperature = SET_MAX_TEMPERATURE;
            }

            if (temperature < SET_MIN_TEMPERATURE) {
                temperature = SET_MIN_TEMPERATURE
            }

            $labelTemperature.html(temperature);
        },
        updateFanSpeedPublish: function($targetSelector)
        {
            $.each($fans, function(index, item){
                let $cls = item.prop("class");
                cls = $cls.split(' ');

                let $id = item.prop("id");

                if ($id === "btn_fan_4") {
                    // auto인 경우
                    item.removeClass("on").addClass("off");

                    if ($id === $targetSelector) {
                        $("#" + $targetSelector).removeClass('off').addClass('on');
                    }
                } else {
                    // auto가 아니고 풍량 1,2,3인 경우
                    item.removeClass("wind_on").addClass("wind_off")

                    if ($id === $targetSelector) {
                        $("#" + $targetSelector).removeClass('wind_off').addClass('wind_on');
                    }
                }
            });
        },
        updateModePublish: function($targetSelector)
        {
            $.each($modes, function(index, item) {
                let $cls = item.prop("class");
                cls = $cls.split(' ');

                let $id = item.prop("id");
                let sequence = item.data("mode");

                let btnOnCss = "on";
                let btnOffCSS = "off";

                if (sequence === 5) {
                    // 난방인 경우 예외처리..
                    btnOnCss = "heat_on";
                }

                item.removeClass(btnOnCss).addClass(btnOffCSS);

                if ($id === $targetSelector) {
                    $("#" + $targetSelector).removeClass(btnOffCSS).addClass(btnOnCss);
                }
            });
        },
        requestRoomInfo: function()
        {
            let self = control;
            let params = [];
            let data = [];

            params.push(
                {name: 'requester', value: FLOOR_REQUESTER},
                {name: 'request', value: FLOOR_REQUEST},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestRoomInfoCallback,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestRoomInfoCallback: function(data, params)
        {
            let self = control;

            if (data['control_error'] === 'Error') {
                return;
            }

            const fLOOR_DATA = data['floor_data'];
            const floorKeyData = CONFIGS['floor_key_data'];
            const floors = Object.keys(fLOOR_DATA);

            const defaultFloor = floors[0];

            $.each(floors, function(index, item) {
                // selectbox에 층을 추가하기
                let $option = $("<option></option>").attr({
                    value: item
                }).html(floorKeyData[item]);
                $selectFloorType.append($option);
            });

            self.selectedFloorData = fLOOR_DATA;

            // 처음 로딩시에 룸 정보 보여주기
            self.updateFloorSelectBox(defaultFloor);
        },
        getRealBoolean: function(boolean)
        {
            let result;

            switch(boolean)
            {
                case 'True':
                    result = true;
                    break;
                case 'False':
                    result = false;
                    break;
            }

            return result;
        },
        updateFloorSelectBox: function(floor)
        {
            let self = control;
            let buildings = self.selectedFloorData;

            let rooms = buildings[floor];
            let defaultRoomName = rooms[0];

            // selectbox 초기화
            $selectRoomType.empty();

            $.each(rooms, function(index, item) {
                // selectbox에 룸 추가하기
                let $option = $("<option></option>").attr({
                    value: item
                }).html(item);
                $selectRoomType.append($option);
            });

            self.selectedRoomName = defaultRoomName;

            // 장소 출력
            $labelDeviceLocation.html(defaultRoomName);

            self.request(true);
        },
        updateRoomSelectBox: function(room)
        {
            let self = control;

            // 장소 출력
            $labelDeviceLocation.html(room);

            self.selectedRoomName = room;
            self.request();
        },
        updateStatus: function($this, mode)
        {
            let self = control;
            let powerOnOff = self.selectedPowerOnOff;
            let temperature = parseInt(self.selectedTemperature);

            if (gIsDevMode === 1) {
                return;
            }

            // 기능 동작
            self.selectedOperation = mode;

            if (mode !== 'power' && powerOnOff === false) {
                return;
            }

            switch (mode)
            {
                case 'power' :
                    // 전원 조정
                    self.selectedStatus = powerOnOff;
                    self.selectedPowerOnOff = powerOnOff;
                    break;
                case 'fan_speed':
                    // 풍량 조절
                    self.selectedStatus = parseInt($this.data('fan'));
                    break;
                case 'mode':
                    // 모드
                    self.selectedStatus = parseInt($this.data('mode'));
                    break;
                case 'lower_temperature':
                    // 온도하락
                    temperature -= 1;
                    if (temperature < SET_MIN_TEMPERATURE) {
                        return;
                    }
                    self.selectedStatus = temperature;
                    break;
                case 'upper_temperature':
                    // 온도 상승
                    temperature+= 1;
                    if (temperature > SET_MAX_TEMPERATURE) {
                        return;
                    }
                    self.selectedStatus = temperature;
                    break;
            }

            // 상태가 변경이 완료될 때까지 화면 조작 금지
            loadingWindow.show();

            self.selectedChangedLoading = true;
            self.requestControlChange();
        },
        requestControlChange: function()
        {
            let self = control;
            let params = [];
            let data = [];

            data.push({ name: 'room_name',  value: self.selectedRoomName });
            data.push({ name: 'status', value: self.selectedStatus });
            data.push({ name: 'power_on_off', value: self.selectedPowerOnOff });
            data.push({ name: 'function', value: self.selectedOperation });
            data.push({ name: 'is_ready', value: isReady });
            data.push({ name: 'company', value: self.selectedCompany });

            params.push(
                {name: 'requester', value: requester},
                {name: 'request', value: command},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestControlChangeCallback,
                callbackParams: null,
                showAlert: true,
                showLoading: false
            };

            module.request(requestParams);
        },
        requestControlChangeCallback: function(data, params)
        {
            let self = control;

            if (data['control_error'] === 'Error') {
                $checkboxPowerOnOff.prop("checked", false);
                loadingWindow.hide();
                self.selectedChangedLoading = false;
                return;
            }

            let result = data['result'];

            switch (result['operation'])
            {
                case 'power':
                    let powerOnOff = Boolean(result['status']);
                    self.updatePowerChangePublish(powerOnOff);
                    break;
                case 'fan_speed':
                    let $fanSpeedTarget = "btn_fan_" + result['status'];
                    self.updateFanSpeedPublish($fanSpeedTarget);
                    break;
                case 'mode':
                    let $modeTargetSelector = "btn_mode_" + result['status'];
                    self.updateModePublish($modeTargetSelector);
                    break;
                case 'set_temperature':
                    let temperature = parseInt(result['status']);
                    self.updateTemperaturePublish(temperature);
                    break;
            }
            setTimeout(function(){
                if (self.selectedChangedLoading === true) {
                    // 로딩바 종료
                    loadingWindow.hide();
                    self.selectedChangedLoading = false;
                }
                self.request(); // 새로고침
            }, SET_CONTROL_TIME_OUT);
        },
        setRequestControlStatus: function()
        {
            let self = control;

            if (self.selectedChangedLoading === false) {
                control.request(false);
            }
        },
    };

    $selectFloorType.on("change", function() {
        // 층 선택
        control.updateFloorSelectBox($(this).val());
    });

    $selectRoomType.on("change", function() {
        // 룸 선택
        control.updateRoomSelectBox($(this).val());
    });

    $checkboxPowerOnOff.on("click", function(){
       // 전원 선택
       //control.updateStatus($(this), "power");
    });

    $btnModeGroup.on("click", function(){
        // 모드
        //control.updateStatus($(this), "mode");
    });

    $btnFanGroup.on("click", function(){
        // 풍량
        //control.updateStatus($(this), "fan_speed");
    });

    $btnTemperatureDown.on("click", function(){
        // 온도 하락
        //control.updateStatus($(this), "lower_temperature");
    });

    $btnTemperatureUp.on("click", function(){
        // 온도 상승
        //control.updateStatus($(this), "upper_temperature");
    });

    return control;
}