let frameControl;
let errorPopup;

$(document).ready(function() {
    const $errorPopupSetting = $(".error_popup_setting");
    let errorPopupParams = {
        beforeCallback: null,
        openCallback: null,
        closeCallback: null,
        $link: $errorPopupSetting
    };
    errorPopup = module.popup(errorPopupParams);

    frameControl = createFrameControl();
    frameControl.setChangeFrameByConfig(); // frame 파일내에서 업체에 맞게 정보 변경
	frameControl.initializeCalendar(); // 장애 팝업창에 날짜 초기화
    frameControl.getCurrentDate(); // 현재 날짜 정보 조회
    frameControl.setMenuClickEvent(); // 메뉴 이벤트 정의
    frameControl.loadingProgress(); // 로딩바
    frameControl.requestSessionData(); // 세션 정보 조회
    frameControl.requestWeather(); // 날씨 정보 초기화
    frameControl.requestHindranceInfo(); // 위치 및 에너지원 조회
    frameControl.requestHindranceStatus(); // 장애 요청
    frameControl.requestFinedustStatus(); // 환경부미세먼지

    setInterval(function() {
        frameControl.requestWeather(); // 날씨 정보 초기화
        frameControl.requestHindranceStatus(); // 장애 요청
        frameControl.requestFinedustStatus(); // 환경부미세먼지
    }, 1000 * 60 * 5);
});

function createFrameControl()
{
    let frameControl = {
        rootPageCount: rootPageCount,
        rootViewPageCount: rootViewPageCount,
        rootStartPage: rootStartPage,
        currentPage: currentPage,
        totalPage: totalPage,
		initializeCalendar: function()
        {
            let self = frameControl;

            self.createDatepickerByFrame($("#frame_start_date"), true);
            self.createDatepickerByFrame($("#frame_end_date"));
        },
        requestLogout: function(showConfirmMessage = true)
        {
            let self = frameControl;

            let params  = [];
            let isLogout = false;

            if (showConfirmMessage === true) {
                if (confirm('로그아웃 하시겠습니까?') === true) {
                    isLogout = true;
                }
            }

            if (showConfirmMessage === false) {
                isLogout = true;
            }

            if (isLogout === true) {
                params.push(
                    { name: 'requester', value: 'login' },
                    { name: 'request', value: 'logout' }
                );

                let requestParams = {
                    url: requestUrl,
                    params: params,
                    callback: self.logoutCallback,
                    callbackParams: null,
                    showAlert: true
                };

                module.cookie().removeCookie('bems_autologin_id_', '');
                module.cookie().removeCookie('bems_autologin_', false);
                module.cookie().removeCookie('bems_autologin_key_', '');
                module.cookie().removeCookie('bems_device_key_', '');

                module.subRequest(requestParams);
            }
        },
        logoutCallback: function()
        {
            window.location.href = './';
        },
        requestWeather: function()
        {
            let self = frameControl;

            let params = [];

            params.push(
                { name: 'requester', value: 'weather' },
                { name: 'request', value: 'weather_temp_humi_cur' }
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.updateWeather,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        updateWeather: function(data, params)
        {
            let weatherData = data;

            let weatherNewClass = weatherOldClass = '';

            $("#temperature").html(weatherData['temp']);
            $("#humidity").html(weatherData['humi']);

            // 날짜클래스 생성
            weatherNewClass = 'w' + weatherData['weat'];
            weatherOldClass = $("#weather").attr('class');

            $("#weather").removeClass(weatherOldClass).addClass(weatherNewClass);
        },
        requestSessionData: function()
        {
            let self = frameControl;

            let params = [];

            params.push(
                { name: 'requester', value: 'common' },
                { name: 'request', value: 'tmp_session' }
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestSessionCallback,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestSessionCallback: function(data, params)
        {
            const dashboardInfo = data['dashboard_info'];
            if (dashboardInfo !== undefined) {
                $("#main_page_url").attr(
                    'href',
                    `./index.php?page=${dashboardInfo['url']}&group=${dashboardInfo['group_id']}&menu=${dashboardInfo['menu_id']}`
                );
            }
        },
        requestFinedustStatus: function()
        {
            let self = frameControl;
            let params = [];

            params.push(
                { name: 'requester', value: 'weather' },
                { name: 'request', value: 'weather_finedust' },
                { name: 'params', value: null }
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.updateFinedustCallback,
                callbackParams: null,
                showAlert: true,
                showLoading: false
            };

            module.subRequest(requestParams);
        },
        updateFinedustCallback: function(data, params)
        {
            let self = frameControl;

            let airPm10RemoveClass = $("#label_finedust_pm10_status").prop('class');
            let airPm25RemoveClass = $("#label_finedust_pm25_status").prop('class');

            let airPM10 = data['air_pm10'];
            let airPM25 = data['air_pm25'];

            let airPm10Data = self.getFinedustLabelColors(airPM10);
            let airPm25Data = self.getFinedustLabelColors(airPM25, true);

            $("#label_pm10_status")
                .removeClass(airPm10RemoveClass)
                .addClass(airPm10Data['color'])
                .html(airPm10Data['label']);

            $("#label_pm25_status")
                .removeClass(airPm25RemoveClass)
                .addClass(airPm25Data['color'])
                .html(airPm25Data['label']);
        },
        getFinedustLabelColors: function(dust, isultra = false)
        {
            let level = -1;
            let levelArr = rootFineDustLevelByFrame;

            if (isultra === true) {
                levelArr = rootUltraDustLevelByFrame;
            }

            let len = levelArr.length;

            for (let i = 0; i < len; i++) {
                if (dust <= levelArr[i]) {
                    level = i;
                    break;
                }
            }

            len = rootFineDustLabel.length;

            if (level <= -1 || level > len - 1) {
                level = len - 1;
            }

            return {
                index: level,
                label: rootFineDustLabel[level],
                color: rootColorClasses[level]
            }
        },
        requestHindranceStatus: function()
        {
            let self = frameControl;

            let params = [];
            let data = [];

            if(self.currentPage < 1){
                self.currentPage = self.rootStartPage;
            }

            data.push({
                'start_page' : self.currentPage,
                'view_page_count' : self.rootViewPageCount,
                'formData' : $("#error_popup_setting").serialize()
            });

            params.push(
                { name: 'requester', value: 'alarm' },
                { name: 'request', value: 'hindrance_alarm' },
                { name: 'params', value: JSON.stringify(data) }
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.updateHindranceAlarmCallback,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        updateHindranceAlarmCallback: function(data, params)
        {
            let self = frameControl;

            let isExistData = data['is_exist_data'];

            if (isExistData == false) {
                self.logoutCallback();
                return;
            }

            let count = data['hindrance_alram_count'];
            let alarmData = data['hindrance_alram_log'];
            let total = data['count'];
            let len = alarmData.length;

            let trStr = '';

            if (count > 0) {
                $("#error-icon").removeClass("icon_error0").addClass("icon_error");
            } else {
                $("#error-icon").removeClass("icon_error").addClass("icon_error0");
            }

            $("#hindrance-tbody > tr").remove();

            // 팝업창에 데이터 뿌려놓기
            if (len > 0) {

                for (let i = 0; i < len; i++) {
                    let homeGrpPk = alarmData[i]['home_grp_pk'];
                    let sensorType = alarmData[i]['sensor_type'];

                    trStr += `<tr id="hindrance-tbody">`;
                    trStr += `<td>${alarmData[i]['seq']}</td>`;

                    if (buildingManageType === true) {
                        let floor = floorkeyMappings[homeGrpPk];
                        if (sensorType === '전기' && homeGrpPk === '0M') {
                            floor = '전체전력';
                        }

                        trStr += `<td>${alarmData[i]['complex_name']}</td>`;
                        trStr += `<td>${floor}</td>`;
                    } else {
                        trStr += `<td>${alarmData[i]['sensor_type']}</td>`;
                    }

                    trStr += `<td>${alarmData[i]['sensor_sn']}</td>`;

                    if (buildingManageType === true) {
                        trStr += `<td>${sensorType}</td>`;
                    }

                    trStr += `<td>${alarmData[i]['alarm_on_time']}</td>`;
                    trStr += `<td>${alarmData[i]['alarm_msg']}</td>`;
                    trStr += `<td>${alarmData[i]['alarm_off_time']}</td>`;
                    trStr += `<td>${alarmData[i]['alarm_on_off']}</td>`;
                    trStr += `</tr>`;
                }
            } else {
                const conditionColspan = (buildingManageType === true) ? 9 : 7;

                trStr = `<tr><td colspan="${conditionColspan}">장애 조회 내용이 존재하지 않습니다.</td></tr>`;
            }

            $("#hindrance-tbody").append(trStr);

            // 페이징처리
            let pageParam = {
                total: total,
                currentPage: self.currentPage,
                pageCount: self.rootPageCount,
                viewPageCount: self.rootViewPageCount,
                id: 'hindrance_paging',
                key: 'hindrance'
            };

            module.page(pageParam);

            // 마지막페이지
            self.totalPage = Math.ceil(total/self.rootViewPageCount);
        },
        onHindranceAlarmClicked: function()
        {
            let self = frameControl;

            self.currentPage = 0;
			self.initializeCalendar();

            // 발생여부 디폴트 설정
            $("#frame_error_status").val("on");

            // 페이지 리로딩
            self.requestHindranceStatus();

            // 팝업출력
            errorPopup.open();
        },
        createDatepickerByFrame: function ($id, beforeMonth = false)
        {
            let dateStr = "";

            let date = module.utility.getBaseDate();

            if (beforeMonth == true) {
                // 1달전
                date.setMonth(date.getMonth()-1);
            }

            dateStr = $.datepicker.formatDate('yy-mm-dd', date);

            $id.datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                showMonthAfterYear: true
            });

            $id.val(dateStr);
        },
        requestFileDownload: function()
        {
            let CommonExcelData = [];

            const $formCommonExcel = $("#form-common-excel");
            const $formCommonParam = $("#form-common-param");

            CommonExcelData.push({ name: 'file_type', value: 'manual'});

            $formCommonParam.val(JSON.stringify(CommonExcelData));

            $formCommonExcel.attr("action", "../http/index.php");
            $formCommonExcel.submit();
        },
        requestHindranceExcel: function()
        {
            let self = frameControl;

            let params = [];
            let data = [];

            let limitMonth = self.getDifferMonth();
            if(limitMonth > 3){
                alert("일자는 3개월까지 검색 가능합니다.");
                return;
            }

            data.push({ 'formData' : $("#error_popup_setting").serialize() });

            params.push(
                { name: 'requester', value: 'alarm' },
                { name: 'request', value: 'hindrance_excel' },
                { name: 'params', value: JSON.stringify(data) }
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.updateHindranceExcelCallback,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        updateHindranceExcelCallback: function(data, params)
        {
			let d = [];
            let temp = data['hindrance_alram_log'];

            d[0] = ['No', '건물명', '층', '센서 아이디', '종류', '발생 일시', '장애 메시지', '처리 일시', '현재 상태'];

            if (buildingManageType === false) {
                d[0] = ['No', '부하명', '센서 아이디', '발생 일시', '장애 메시지', '처리 일시', '현재 상태'];
            }

            for (let i = 0; i < temp.length; i++) {
                let homeGrpPk = temp[i]['home_grp_pk'];
                let sensorType = temp[i]['sensor_type'];
                let floor = floorkeyMappings[homeGrpPk];
                if (sensorType === '전기' && homeGrpPk === '0M') {
                    floor = '전체전력';
                }

                let tAr = [
                    temp[i]['seq'], temp[i]['complex_name'], floor, temp[i]['sensor_sn'], sensorType,
                    temp[i]['alarm_on_time'], temp[i]['alarm_msg'], temp[i]['alarm_off_time'], temp[i]['alarm_on_off'],
                ];

                if (buildingManageType === false) {
                    tAr = [
                        temp[i]['seq'], sensorType, temp[i]['sensor_sn'], temp[i]['alarm_on_time'], temp[i]['alarm_msg'],
                        temp[i]['alarm_off_time'], temp[i]['alarm_on_off'],
                    ];
                }

                d[i+1] = tAr;
            }

            let time = module.utility.getCurrentTime();
            let name = rootExcelFileName + "_" + time + ".xlsx";

            module.excel().exportExcel(d, name);
        },
        getDifferMonth: function()
        {
            let startDate = $("#frame_start_date").val();
            let endDate = $("#frame_end_date").val();

            let stAr = startDate.split('-');
            let edAr = endDate.split('-');

            let newStartDate = new Date(stAr[0], stAr[1], stAr[2]);
            let newEndDate = new Date(edAr[0], edAr[1], edAr[2]);

            let cDay = 24 * 60 * 60 * 1000;
            let cMonth = cDay * 30;

            return parseInt((newEndDate - newStartDate)/cMonth);
        },
        onSearchClickedHindranceExcel: function()
        {
            let self = frameControl;

            self.currentPage = 0;

            let limitMonth = self.getDifferMonth();
            if(limitMonth > 3){
                alert("일자는 3개월까지 검색 가능합니다.");
                return;
            }

            self.requestHindranceStatus();
        },
        onPagingClicked: function($this)
        {
            let self = frameControl;
            let $id = $this.prop("id");

            let tmp = $id.split('_');

            tmp[2] = Number(tmp[2]);
            self.currentPage = tmp[2];

            self.requestHindranceStatus();
        },
        requestHindranceInfo: function() 
        {
            let self = frameControl;

            let params = [];
    
            params.push(
                { name: 'requester', value: 'alarm' },
                { name: 'request', value: 'hindrance_info' },
                { name: 'params', value: null }
            );
    
            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.hindranceInfoCallback,
                callbackParams: null,
                showAlert: true
            };
    
            // subRequest는 비동기 로딩바에 해당됨
            module.subRequest(requestParams);
        },
        hindranceInfoCallback: function(data) 
        {
            let hindranceData = data;

            let complexData = hindranceData['complex_data'];
            let energyData = hindranceData['energy_data'];

            let $selectFloorType = $("#frame_select_floor_type");
            let $selectEnergyType = $("#frame_select_energy_type");

            let defaultOption = "<option value=''>전체</option>";

            if (buildingManageType === true) {
                // 층 selectbox 추가
                $selectFloorType.append(defaultOption);
                $.each(complexData, function(key, value){
                    $selectFloorType.append($("<option></option>").attr("value", key).text(value));
                });

                // 에너지원 selectbox 추가
                $selectEnergyType.append(defaultOption);
                $.each(energyData, function(key, value){
                    $selectEnergyType.append($("<option></option>").attr("value", key).text(value['name']));
                });
            }

            if (buildingManageType === false) {
                // 부하명 selectbox 추가
                let $selectStatusType = $("#frame_select_status_type");
                $selectStatusType.append(defaultOption);
                $.each(energyData, function(key, value){
                    $selectStatusType.append($("<option></option>").attr("value", key).text(value['name']));
                });
            }
        },
        setMenuClickEvent: function()
        {
            $menu = $('.lnb > ul > li.dept > a');
            $menu.on('click', function() {
                if ($(this).hasClass('lnb_up')) {
                    $(this).parent().find('ul').slideDown('slow');
                    $(this).removeClass('lnb_up');
                    $(this).addClass('lnb_down');
                } else if($(this).hasClass('lnb_down')) {
                    $(this).parent().find('ul').slideUp('slow');
                    $(this).removeClass('lnb_down');
                    $(this).addClass('lnb_up');
                }
            });
        },
        getCurrentDate: function()
        {
            const $labelDate = $("#label_date");
            const $labelDay = $("#label_day");
            const $labelTime = $("#label_time");

            module.date($labelDate, $labelDay, $labelTime).start();
        },
        loadingProgress: function()
        {
            $loadingWindow = $(".loading_window");

            loadingWindow = $loadingWindow.dxLoadPanel({
                shadingColor : "rgba(255,255,255,0.4)",
                position : { of : ".tabs" },
                visible : false,
                showIndicator : true,
                showPane : true,
                shading : true,
                closeOnOutsideClick : false,
                onShown : function(){},
                onHidden : function(){}
            }).dxLoadPanel("instance");
        },
        setChangeFrameByConfig: function()
        {
            floorkeyMappings = CONFIGS['floor_key_data']; // 층별 정보

            if (CONFIGS['factory_mode'] !== undefined) {
                buildingManageType = false; // 공장 관련 정보
            }

            const LAYOUT_CSS_PATH = CONFIGS['layout_css_path'];
            const MANUAL_DISABLED = CONFIGS['is_use_manual'] === true ? 'block' : 'none';

            if (LAYOUT_CSS_PATH !== undefined) {
                $("#layout_css_path").prop('href', LAYOUT_CSS_PATH);
            }

            $("#download-manual").css('display', MANUAL_DISABLED);
        },
    };

    $("#btn_logout").on("click", function() {
        frameControl.requestLogout();
    });

    $("#btn_popup_search").on("click", function() {
        frameControl.onSearchClickedHindranceExcel();
    });

    $("#openErrorBtn").on("click", function() {
        frameControl.onHindranceAlarmClicked();
    });

    $(".closeErrorBtn").on("click", function() {
        errorPopup.close();
    });

    $("#btn_excel").on("click", function() {
        frameControl.requestHindranceExcel();
    });

    $("#btn_manual_download").on("click", function() {
        frameControl.requestFileDownload();
    });

    $("#btn_hindrance_first_page").on("click", function() {
        // 첫 페이지
        frameControl.currentPage = 1;
        frameControl.requestHindranceStatus();
    });

    $("#btn_hindrance_prev_page").on("click", function() {
        // 이전 페이지
        frameControl.currentPage -= 1;
        frameControl.requestHindranceStatus();
    });

    $("#btn_hindrance_next_page").on("click", function() {
        // 다음 페이지
        frameControl.currentPage += 1;
        frameControl.requestHindranceStatus();
    });

    $("#btn_hindrance_last_page").on("click", function() {
        // 마지막 페이지
        frameControl.currentPage = frameControl.totalPage;
        frameControl.requestHindranceStatus()
    });

    $(document).on("click", ".paging_click", function(e){
        e.preventDefault();
        frameControl.onPagingClicked($(this));
    });

    return frameControl;
}