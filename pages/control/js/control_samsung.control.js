let control;

const modeCSSTypes = ['cool', 'moisture', 'fan2', 'auto', 'hot'];
const powerOnOFFs = ['on', 'off'];

const FILE_TYPE = CONFIGS['control']['file_type'];
const SET_MAX_TEMPERATURE = 30;
const SET_MIN_TEMPERATURE = 18;
const SET_CONTROL_AUTO_TIME_OUT = 1000 * 45;
const SET_CONTROL_TIME_OUT = 1000 * 7;

const AIR_CON_INFO = CONFIGS['control']['air_con_info'][currentFloor];
const DEFAULT_FLOOR_KEY = CONFIGS['control']['default_floor_key'][currentFloor];

$(document).ready(function() {
    control = createControl();

    if (gIsDevMode === 0 && isReady === true) {
        control.request();

        // 제어 상태 가져올 때 너무 오래 걸림
        setInterval(function(){
            // 제어 상태 체크 5초마다 주기적으로 실행..
            control.setRequestControlStatus();
        }, SET_CONTROL_AUTO_TIME_OUT);
    }
});

function createControl()
{
    let control = {
        selectedRoomName: DEFAULT_ROOM_NAME, // 장소명칭
        selectedFloorKey: DEFAULT_FLOOR_KEY,
        selectedPowerOnOff: '', // 전원여부
        selectedFanSpeed: '', //  풍량 단계
        selectedMode: '', // 모드
        selectedTemperature: 0, // 온도
        selectedOperation: 0, // 기능수행정보
        selectedStatus: 0, // 변경값
        selectedFloor: currentFloor, // 현재 층
        selectedChangedLoading: false, // 로딩 진행여부
        request: function(showLoadingBar = true)
        {let self = control;
            let params = [];
            let data = [];

            data.push({ name: 'current_floor', value: self.selectedFloor });
            data.push({ name: 'is_ready', value: isReady });

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

            let statusData = self.data['status'];
            let floorKey = self.selectedFloorKey;

            let roomName = AIR_CON_INFO[floorKey];

            let powerOnOff = statusData[roomName]['power'];
            let temperature = statusData[roomName]['set_temperature'];
            let fanSpeed = statusData[roomName]['fan_speed'];
            let mode = statusData[roomName]['op_mode'];

            let $fanTargetSelector = (fanSpeed === 'auto') ? 'btn_fan_4' : 'btn_fan_' + fanSpeed;
            let $modeTargetSelector = (mode === 'auto') ? 'btn_mode_4' :  'btn_mode_' + mode;

            // 층에 있는 룸에 대해 상태값 표시
            self.updateAllPowerChangePublish(statusData);

            // Device 전원 퍼블리싱 변경
            self.updatePowerChangePublish(powerOnOff);

            // 온도 퍼블리싱 변경
            self.updateTemperaturePublish(temperature);

            // 풍량 초기화
            self.updateFanSpeedPublish($fanTargetSelector);

            // 모드 초기화
            self.updateModePublish($modeTargetSelector);

            // 사용자가 선택한 장소 부여
            $labelDeviceLocation.html(self.selectedRoomName);

            self.selectedTemperature = temperature;
            self.selectedPowerOnOff = powerOnOff;
            self.selectedFanSpeed = fanSpeed;
            self.selectedMode = mode;
        },
        updateAllPowerChangePublish: function(data)
        {
            $.each($floorDevices, function(index, item) {
                let $selector = $('#btn_show_' + item);
                let roomName = AIR_CON_INFO[item];

                let $childrenSpan = $selector.children('span');
                let powerOnOff = data[roomName]['power'] === 'on' ? true : false;

                if ($childrenSpan.prop("class") === 'off' && powerOnOff === true) {
                    $childrenSpan.removeClass('off').addClass('on');
                }

                if ($childrenSpan.prop("class") === 'on' && powerOnOff === false) {
                    $childrenSpan.removeClass('on').addClass('off');
                }
            });
        },
        updatePowerChangePublish: function(status)
        {
            if (status === 'on') {
                // 전원이 켜져 있는 경우 css 변경
                $btnPower.removeClass(powerOnOFFs[1]).addClass(powerOnOFFs[0]);
                $btnPower.html(powerOnOFFs[0].toUpperCase());
            } else {
                // 전원이 꺼져있는 경우 css 변경
                $btnPower.removeClass(powerOnOFFs[0]).addClass(powerOnOFFs[1]);
                $btnPower.html(powerOnOFFs[1].toUpperCase());
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

            $roomTemp.html(temperature);
        },
        getDeviceStatus: function($this)
        {
            let self = control;

            const $parent = $this.closest('li');
            let roomName = $this.data('room');

            if (gIsDevMode === 1 && isReady === true) {
                return;
            }

            // 모든 이벤트 제거
            //$("#aircon_spot > li").removeClass("active");
            $("#spot_name > li").removeClass("active");

            // 클릭한 요소에 이벤트 부여
            $parent.addClass("active");

            self.selectedRoomName = roomName;
            self.selectedFloorKey = $parent.data("room_key");
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

            if (mode != 'power' && powerOnOff !== 'on') {
                alert("전원이 OFF일 때는 기능 변경을 할 수 없습니다.");
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
                        alert("설정 최소 온도 보다 낮을 수없습니다.");
                        return;
                    }
                    self.selectedStatus = temperature;
                    break;
                case 'upper_temperature':
                    // 온도 상승
                    temperature+= 1;
                    if (temperature > SET_MAX_TEMPERATURE) {
                        alert("설정 최대 온도 보다 높을 수없습니다.");
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

            data.push({ name: 'floor', value: self.selectedFloor });
            data.push({ name: 'room_name',  value: self.selectedRoomName });
            data.push({ name: 'status', value: self.selectedStatus });
            data.push({ name: 'power_on_off', value: self.selectedPowerOnOff });
            data.push({ name: 'function', value: self.selectedOperation });
            data.push({ name: 'is_ready', value: isReady });

            params.push(
                {name: 'requester', value: requester},
                {name: 'request', value: setCommand},
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
                loadingWindow.hide();
                self.selectedChangedLoading = false;
                alert("관리자에게 문의하세요!");
                return;
            }

            let result = data['result'];

            switch (result['operation'])
            {
                case 'power':
                    let powerOnOff = result['status'];
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
                if (self.selectedChangedLoading == true) {
                    // 로딩바 종료
                    loadingWindow.hide();
                    self.selectedChangedLoading = false;
                }
                self.request(); // 새로고침
            }, SET_CONTROL_TIME_OUT);
        },
        updateFanSpeedPublish: function($targetSelector)
        {
            $.each($fans, function(index, item){
                let $cls = item.prop("class");
                cls = $cls.split(' ');

                let $id = item.prop("id");

                if ($id === "btn_fan_4") {
                    // auto인 경우
                    if (jQuery.inArray('btn_auto_on',cls) > -1) {
                        item.removeClass("btn_auto_on").addClass("btn_auto_off");
                    }

                    if ($id === $targetSelector) {
                        $("#" + $targetSelector).removeClass('btn_auto_off').addClass('btn_auto_on');
                    }
                } else {
                    // auto가 아니고 풍량 1,2,3인 경우
                    if (jQuery.inArray('btn_fan_on',cls) > -1) {
                        item.removeClass("btn_fan_on").addClass("btn_fan_off")
                    }

                    if ($id === $targetSelector) {
                        $("#" + $targetSelector).removeClass('btn_fan_off').addClass('btn_fan_on');
                    }
                }
            });
        },
        updateModePublish: function($targetSelector)
        {
            let self = control;
            
            $.each($modes, function(index, item) {
                let $cls = item.prop("class");
                cls = $cls.split(' ');

                let $id = item.prop("id");

                let part = modeCSSTypes[index];
                let btnOnCss = "btn_" + part + "_on";
                let btnOffCSS = "btn_" + part + "_off";

                if (jQuery.inArray(btnOnCss,cls) > -1) {
                    item.removeClass(btnOnCss).addClass(btnOffCSS);
                }

                if ($id === $targetSelector) {
                    $("#" + $targetSelector).removeClass(btnOffCSS).addClass(btnOnCss);
                }
            });
        },
        onClickedFloorButton: function($this, groupPage = '')
        {
            const FILE_GROUP = 'control';
            const FLOOR_TYPE = $this.data('floor');
            const MENU_NAME = $this.data("menu");
            const FLOOR_FILE_NAME = "control_" + FLOOR_TYPE + "_" + FILE_TYPE + ".html";

            $("#btn_floor_group > button").removeClass('on');
            $this.addClass('on');

            // 다른 층으로 이동
            let menuManager = module.MenuModule();
            menuManager.requestPageLocation(FILE_GROUP, FLOOR_FILE_NAME, MENU_NAME, groupPage, group, menu);
        },
        setRequestControlStatus: function()
        {
            let self = control;

            if (self.selectedChangedLoading === false) {
                control.request(false);
            }
        },
    };

    $.each(BTN_AIR_CONT_INFO, function(index, value) {
        $("#btn_show_" + value).on("click", function() {
            control.getDeviceStatus($(this));
        });

        $floorDevices.push(value);
    });

    $.each(FLOOR_KEYS, function(index, value) {
        $("#btn_floor_" + value).on("click", function() {
            control.onClickedFloorButton($(this));
        });
    });

    $btnPower.on("click", function(){
        // 전원
        control.updateStatus($(this),'power');
    });

    $btnFanGroup.on("click", function(){
        // 풍량
        control.updateStatus($(this), 'fan_speed');
    });

    $btnModeGroup.on("click", function(){
        // MODE
        control.updateStatus($(this), 'mode');
    });

    $btnTemperatureDown.on("click", function() {
        // 온도 하락
        control.updateStatus($(this), 'lower_temperature');
    });

    $btnTemperatureUp.on("click", function() {
        // 온도상승
        control.updateStatus($(this), 'upper_temperature');
    });

    return control;
}