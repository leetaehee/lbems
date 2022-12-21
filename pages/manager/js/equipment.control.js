let control;

$(document).ready(function() {
    createDatepicker($startDate, date);
    createDatepicker($endDate, date);
    createDatepicker($popupInstalledDate, date);
    createDatepicker($popupLastestCheckDate, date);
    createDatepicker($popupReplaceDate, date);

    const buildingList = module.BuildingList($buildingType);
    buildingList.request();

    const popupBuildingList = module.BuildingList($popupBuildingType);
    popupBuildingList.request();

    control = createControl();
    control.requestMenuAuthority(group);
});

function createDatepicker($id, date)
{
    let dateStr = '';

    dateStr = $.datepicker.formatDate('yy-mm-dd', date);

    $id.datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        showMonthAfterYear: true
    });

    if ($id.prop('id') === 'popup_installed_date'
        || $id.prop('id') === 'popup_lastest_check_date'
        || $id.prop('id') === 'popup_replace_date') {
        //$id.val(dateStr);
    }

    today = dateStr;
}

function createControl()
{
    let control = {
        selectedStartPage: startPage,
        selectedTotalPage: 0,
        selectSensorNoPk: defaultEmptyValue,
        selectedOption: defaultEmptyValue,
        selectedHomeType: defaultEmptyValue,
        selectedHomeDongPk: defaultEmptyValue,
        selectedHomeHoPk: defaultEmptyValue,
        selectedHomeGrpPk: defaultEmptyValue,
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'start_page', value: self.selectedStartPage });
            data.push({ name: 'view_page_count', value: viewPageCount });
            data.push({ name: 'option', value: self.selectedOption });
            data.push({ name: 'form_data', value: $formEnergyManage.serialize() });

            params.push(
                { name: 'requester', value: REQUESTER },
                { name: 'request', value: LIST_COMMAND },
                { name: 'params', value: JSON.stringify(data) }
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
            self.updateEquipmentList();
        },
        updateEquipmentList: function()
        {
            let self = control;

            const data = self.data['equipment_list'];
            const list = data['list'];
            const dataCount = data['count'];

            const $tr = [];
            let $trEquipment = '';
            let colspan = 10;

            $("#tbody_equipment > tr").remove();

            if (isDisabledAnomalyColumn === true && isDisabledBuildingSelectBox === true) {
                //colspan = 12;
            }

            if (dataCount < 1) {
                $tr.push(`<tr>`);
                $tr.push(`<td id="empty-table-td" colspan="${colspan}">- 장비가 존재하지 않습니다 -</td>`);
                $tr.push(`</tr>`);
            }

            const buildingInfo = self.getBuildingInfo($buildingType);
            const SELECT_ELECTRIC_FLOOR_INFO = buildingInfo['select_electric_floor_info'];
            const SELECT_FLOOR_INFO = buildingInfo['select_floor_info'];

            if (dataCount > 0) {
                const energyNames = module.utility.getBemsUnits2Names();
                $.each(list, function (key, items) {
                    let option = parseInt(items['option']);
                    let homeHoPk = items['home_ho_pk'];
                    let homeGrpPk = items['home_grp_pk'];

					let fgAnomalyName = (isDisabledAnomalyColumn === true && items['fg_anomaly_name'] !== undefined) ? items['fg_anomaly_name'] : '';
					let anomalyScore = (isDisabledAnomalyColumn === true && items['anomaly_score'] !== undefined) ? items['anomaly_score'] : '';

                    let energyName = energyNames[option];

                    /*
						let $choiceCheckbox = $("<input>").attr({
							"type" : "checkbox",
							"class" : "choice_checked",
							"name" : "chk_no[" + key + "]",
							"value" : items['sensor_sn']
						});
                     */

                    let $modifyButton = $("<button>").attr({
                        "type" : "button",
                        "class" : "Btn saveBtn btnEnergyModify",
                    }).html("수정");

                    let floorName = option === 0 ? SELECT_ELECTRIC_FLOOR_INFO[homeHoPk] : SELECT_FLOOR_INFO[homeGrpPk];
                    if (floorName === undefined) {
                        floorName = SELECT_FLOOR_INFO[homeGrpPk];
                    }

                    $tr.push(`<tr data-sensor_sn=${items['sensor_sn']}>`);
                    //$tr.push(`<td>${$choiceCheckbox[0].outerHTML}</td>`);
                    $tr.push(`<td>${items['home_dong_pk']}</td>`);
                    $tr.push(`<td>${homeHoPk}</td>`);
                    $tr.push(`<td>${floorName}</td>`);
                    $tr.push(`<td>${energyName}</td>`);
                    $tr.push(`<td>${items['sensor_sn']}</td>`);
                    $tr.push(`<td>${items['installed_date']}</td>`);
                    $tr.push(`<td>${items['check_period']}</td>`);
                    $tr.push(`<td>${items['lastest_check_date']}</td>`);
                    $tr.push(`<td>${items['replace_date']}</td>`);

                    if (isDisabledAnomalyColumn === true 
						&& isDisabledBuildingSelectBox === true 
						&& items['fg_anomaly_name'] !== undefined) {
                        //$tr.push(`<td>${fgAnomalyName}</td>`);
                        //$tr.push(`<td>${anomalyScore}</td>`);
                    }

					if (isDisabledAnomalyColumn === true && isDisabledBuildingSelectBox === true && fgAnomalyName === '') {
						//$tr.push(`<td></td>`);
						//$tr.push(`<td></td>`);
					}

                    $tr.push(`<td>${$modifyButton[0].outerHTML}</td>`);
                    $tr.push(`</tr>`);
                });
            }

            $trEquipment = $tr.join('');

            $tbodyEquipment.html($trEquipment);

            // 페이징처리
            let pageParam = {
                total: dataCount,
                currentPage: self.selectedStartPage,
                pageCount: pageCount,
                viewPageCount: viewPageCount,
                id: 'equipment_paging',
                key: 'equipment'
            };

            module.page(pageParam);

            // 마지막페이지
            self.selectedTotalPage = Math.ceil(dataCount/viewPageCount);
        },
        getBuildingInfo: function($selector)
        {
            let managerComplexCodePk = $selector.val() === "" ? gComplexCodePk : $selector.val();
            let managerBuildingCode = `B_${managerComplexCodePk}`;
            let managerBuildingName = $("#building_type > option:selected").text();
            managerBuildingName = managerBuildingName === '건물 선택' ? gBuildingName : managerBuildingName;

            const MANAGE_CONFIGS = module.config().getConfig(managerComplexCodePk, managerBuildingCode, managerBuildingName, gSkinType, gIsDevMode);
            const SELECT_ELECTRIC_FLOOR_INFO = MANAGE_CONFIGS['electric_floor_key_data'];
            const SELECT_FLOOR_INFO = MANAGE_CONFIGS['floor_key_data'];

            return {
                'manage_configs' : MANAGE_CONFIGS,
                'select_electric_floor_info' : SELECT_ELECTRIC_FLOOR_INFO,
                'select_floor_info' : SELECT_FLOOR_INFO,
            }
        },
        onSearchValidate: function()
        {
            let self = control;

            if ($buildingType.val() === '' && isDisabledBuildingSelectBox === true){
                alert(VALIDATE_BUILDING_SELECT_EMPTY);
                $buildingType.focus();
                return;
            }

            if ($energyType.val() === ''){
                alert(VALIDATE_ENERGY_TYPE_SELECT_EMPTY);
                $energyType.focus();
                return;
            }

            if (($startDate.val() !== '' && $endDate.val() === '') ||
                ($startDate.val() === '' && $endDate.val() !== '') ){
                alert(VALIDATE_DATE_INPUT_EMPTY);
                return;
            }

            self.selectedStartPage = startPage;
            self.selectedOption = parseInt($energyType.val());

            self.request();
        },
        popupOpen: function()
        {
            standardFormPopup.open();
        },
        popupClose: function()
        {
            standardFormPopup.close();
        },
        requestModifyPopup: function($this)
        {
            let self = control;

            let params = [];
            let data  = [];

            //const sensorNo = $this.closest("tr").find(".choice_checked").val();
            const sensorNo = $this.closest("tr").data('sensor_sn');
            const option = self.selectedOption;

            self.selectSensorNoPk = sensorNo;

            data.push({ name: 'sensor_sn', value: sensorNo });
            data.push({ name: 'option', value: option });

            params.push(
                {name: "requester", value: REQUESTER},
                {name: "request", value: INFO_COMMAND},
                {name: "params", value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestModifyCallback,
                callbackParams: null,
                showAlert: true
            };

            module.request(requestParams);
        },
        requestModifyCallback: function(data, params)
        {
            let self = control;


            if (data['error'] === 'data-error') {
                return;
            }

            const option = self.selectedOption;
            const equipmentData = data['equipment_data'];
            if (equipmentData.length < 1) {
                alert("해당되는 데이터가 존재하지 않습니다.");
                return;
            }

            const buildingInfo = self.getBuildingInfo($buildingType);
            const SELECT_ELECTRIC_FLOOR_INFO = buildingInfo['select_electric_floor_info'];
            const SELECT_FLOOR_INFO = buildingInfo['select_floor_info'];

            const homeType = equipmentData['home_type'];
            const homeDongPk = equipmentData['home_dong_pk'];
            const homeHoPk = equipmentData['home_ho_pk'];
            const homeGrpPk = equipmentData['home_grp_pk'];
            const homeHoNm = equipmentData['home_ho_nm'];
            let floorName = option === 0 ? SELECT_ELECTRIC_FLOOR_INFO[homeHoPk] : SELECT_FLOOR_INFO[homeGrpPk];

            if (floorName === undefined) {
                floorName = SELECT_FLOOR_INFO[homeGrpPk];
            }

            self.selectedHomeType = homeType;
            self.selectedHomeDongPk = homeDongPk;
            self.selectedHomeHoPk = homeHoPk;
            self.selectedHomeGrpPk = homeGrpPk;

            self.initialForm();
            self.popupOpen();

            $popupBuildingType.val(equipmentData['complex_code_pk']).prop('disabled', true);
            $popupEnergyType.val(option).prop('disabled', true);
            $popupEnergyType.trigger('change');
            $popupSensorSn.val(equipmentData['sensor_sn']).prop("readonly", true).addClass("bcReadonly");

            $popupDetailSpec.val(equipmentData['detail_spec']);
            $popupManageLevel.val(equipmentData['manage_level']);
            $popupCheckPeriod.val(equipmentData['check_period']);

            $popupSelectAptFloor.hide();
            $popupSelectAptType.hide();
            $popupSelectAptDong.hide();
            $popupSelectAptHome.hide();

            $aptFloorName.css('display', 'block').html(floorName);
            $aptTypeName.css('display', 'block').html(`${homeType}타입`);
            $aptDongName.css('display', 'block').html(`${homeDongPk}동`);
            $aptHoName.css('display', 'block').html(`${homeHoPk}호 (${homeHoNm})`);

            if (equipmentData['installed_date'] != '') {
                $popupInstalledDate.val(equipmentData['installed_date']);
            }

            if (equipmentData['lastest_check_date'] != '') {
                $popupLastestCheckDate.val(equipmentData['lastest_check_date']);
            }

            if (equipmentData['replace_date'] != '') {
                $popupReplaceDate.val(equipmentData['replace_date']);
            }

            if (isDisabledBuildingSelectBox === true) {
                $("input:radio[name='popup_fg_use']:radio[value=" + equipmentData['fg_use'] + "]").prop("checked", true);
            }
        },
        requestFloorCode($selector, prefix = '')
        {
            let self = control;

            const option = $selector.val();
            if (option === '') {
                return;
            }

            const complexCodePk = $popupBuildingType.val() === '' ? gComplexCodePk : $popupBuildingType.val()
            const keys = module.utility.getBemsEnergyKeyNames();
            const key = keys[option];

            const buildingInfo = self.getBuildingInfo($popupBuildingType);
            const floorInfo = buildingInfo['select_floor_info'];

            const buildingManager = module.BuildingManager('popup_select_apt_floor');
            buildingManager.setEnergyKey(key);
            buildingManager.setComplexCodePk(complexCodePk);
            buildingManager.request(option);
            buildingManager.setFloorKeyData(floorInfo);

            const aptInfo = module.aptInfo(option, 'popup_select_apt_type', 'popup_select_apt_dong', 'popup_select_apt_home');
            aptInfo.setEnergyKey(key);
            aptInfo.setComplexCodePk(complexCodePk);
            aptInfo.request();
        },
        requestEnergyCode: function($selector, prefix = '')
        {
            let self = control;

            let params = [];
            let data = [];

            const complexCodePk = $selector.val() === '' ? gComplexCodePk : $selector.val();

            data.push({ name: 'type', value: energyType });
            data.push({ name: 'complex_code_pk', value: complexCodePk });
            data.push({ name: 'is_type_filter', value: true });

            params.push(
                {name: 'requester', value: 'common'},
                {name: 'request', value: 'energy_code'},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestEnergyCodeCallback,
                callbackParams: prefix,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestEnergyCodeCallback: function(data, params)
        {
            if (data['Error'] === true) {
                return;
            }

            const energyCodes = data['energy_code'];
            const options = ["<option value=''>에너지원</option>"];

            const prefix = (params !== '') ? params : '';

            $.each(energyCodes, function(key, values) {
                options.push(`<option value=${values['option']}>${values['label']}</option>`);
            });

            $(`#${prefix}energy_type`).empty().append(options.join(''));
        },
        requestMenuAuthority: function(groupId)
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({name: 'group_id', value: parseInt(groupId)});

            params.push(
                {name: "requester", value: 'menu'},
                {name: "request", value: 'menu_authority'},
                {name: "params", value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestMenuAuthorityCallback,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestMenuAuthorityCallback: function(data, params)
        {
            let self = control;

            if (data['error'] === 'data-error') {
                return;
            }

            $trSuperSection.css('display', 'none');

            if (data['authority'] < 100) {
                $loginLogPageIcon.attr('src', settingPageIcon);
                $divBuildingSelectBox.css('display', 'none');
                $menuGroupSelector.html(data['group_name']);

                //$(".anomaly_column").css('display', 'none');
                $("#empty-table-td").attr('colspan', 10);

                isDisabledBuildingSelectBox = false;

                // 리스트에 있는 에너지원 업데이트
                self.requestEnergyCode($buildingType);

                // 팝업창에 있는 에너지원 업데이트.. 업체 관리자는 에너지원을 수정못하니까..
                self.requestEnergyCode($buildingType, 'popup_');
            } else {
                $menuGroupSelector.html(defaultMenuGroupName);
                $trSuperSection.attr('style', "display:'';");
            }
        },
        initialForm: function()
        {
            $aptFloorName.css('display', 'none');
            $aptTypeName.css('display', 'none');
            $aptDongName.css('display', 'none');
            $aptHoName.css('display', 'none');

            $popupSelectAptFloor.show();
            $popupSelectAptType.show();
            $popupSelectAptDong.show();
            $popupSelectAptHome.show();

            $popupBuildingType.val(defaultEmptyValue).prop('disabled', false);
            $popupEnergyType.val(defaultEmptyValue).prop('disabled', false);
            $popupSelectAptFloor.val(defaultEmptyValue);
            $popupSelectAptType.val(defaultEmptyValue);
            $popupSelectAptDong.val(defaultEmptyValue);
            $popupSelectAptHome.val(defaultEmptyValue);
            $popupSensorSn.val(defaultEmptyValue).prop("readonly", false).removeClass("bcReadonly");
            $popupInstalledDate.val(defaultEmptyValue);
            $popupDetailSpec.val(defaultEmptyValue);
            $popupManageLevel.val(defaultEmptyValue);
            $popupCheckPeriod.val(defaultEmptyValue);
            $popupLastestCheckDate.val(defaultEmptyValue);
            $popupReplaceDate.val(defaultEmptyValue);

            if (isDisabledBuildingSelectBox === true) {
                $("input:radio[name='popup_fg_use']:radio[value='y']").prop("checked", true);
            }
        },
        requestUpdateData: function(setMode = '')
        {
            let self = control;

            let params = [];
            let data = [];

            let validateMessage = self.isValidatePopup();

            if (validateMessage !== '') {
                alert(validateMessage);
                return;
            }

            const sensorNo = self.selectSensorNoPk;
            const option = $popupEnergyType.val();

            if (setMode !== '') {
                setMode = setMode;
            } else {
                setMode = sensorNo === '' ? 'i' : 'u';
            }

            self.selectedOption = option;

            if (setMode === 'i') {
                // 신규 등록 일 때는 폼 정보로 업데이트
                self.selectedHomeType = $popupSelectAptType.val();
                self.selectedHomeDongPk = $popupSelectAptDong.val();
                self.selectedHomeHoPk = $popupSelectAptHome.val();
                self.selectedHomeGrpPk = $popupSelectAptFloor.val();
                self.selectSensorNoPk = $popupSensorSn.val();
            }

            data.push({ name: 'mode', value: setMode });
            data.push({ name: 'option', value: option });
            data.push({ name: 'sensor_sn', value: self.selectSensorNoPk });
            data.push({ name: 'home_type', value: self.selectedHomeType });
            data.push({ name: 'home_dong_pk', value: self.selectedHomeDongPk });
            data.push({ name: 'home_ho_pk',  value: self.selectedHomeHoPk });
            data.push({ name: 'home_grp_pk', value: self.selectedHomeGrpPk });
            data.push({ name: 'form_data', value: $formSubmitId.serialize() });

            params.push(
                { name: "requester", value: REQUESTER },
                { name: "request", value: SET_COMMAND },
                { name: "params", value: JSON.stringify(data) }
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestUpdateDataCallback,
                callbackParams: null,
                showAlert: true
            };

            module.request(requestParams);
        },
        requestUpdateDataCallback: function(data, params)
        {
            let self = control;
            if (data['error'] === 'dataError') {
                return;
            }

            const result = data['result'];

            if (result['success'] === false) {
                if (result['error_type'] === 'not_right_info') {
                    alert(VALIDATE_HOME_INFO_NOT_RIGHT);
                }

                if (result['error_type'] === 'exist') {
                    alert(VALIDATE_EQUIPMENT_EXIST)
                }

                if (result['error_type'] === 'overlap') {
                    alert(VALIDATE_SENSOR_NO_EXIST)
                }

                self.selectSensorNoPk = '';
                return;
            }

            if (isDisabledBuildingSelectBox === true) {
                $fgUse.val($("input:radio[name='popup_fg_use']:checked").val());
            }

            self.popupClose();
            self.request()
        },
        isValidatePopup: function()
        {
            let self = control;

            let sensorNoPk = self.selectSensorNoPk;
            const sensorNoFilter = /^[a-z0-9_]/g;

            const tempBuildingType = $popupBuildingType.val();
            const tempEnergyType = $popupEnergyType.val();
            const tempSensorNo = $popupSensorSn.val();
            const tempFloor = $popupSelectAptFloor.val();
            const tempAptType = $popupSelectAptType.val();
            const tempAptDong = $popupSelectAptDong.val();
            const tempAptHo = $popupSelectAptHome.val();

            if (sensorNoPk === '') {
                // 입력모드 일 때..
                if (tempBuildingType === '') {
                    return VALIDATE_BUILDING_SELECT_EMPTY;
                }

                if (tempEnergyType === '') {
                    return VALIDATE_ENERGY_TYPE_SELECT_EMPTY;
                }

                if (tempAptType === 'all') {
                    return VALIDATE_APT_TYPE_SELECT_EMPTY;
                }

                if (tempAptDong === 'all') {
                    return VALIDATE_APT_DONG_SELECT_EMPTY;
                }

                if (tempAptHo === 'all') {
                    return VALIDATE_APT_HO_SELECT_EMPTY;
                }

                if (tempFloor === 'all') {
                    return VALIDATE_FLOOR_ALL_SELECT;
                }

                if (tempSensorNo === '') {
                    return VALIDATE_SENSOR_NO_INPUT_EMPTY;
                } else {
                    if (sensorNoFilter.test(tempSensorNo) == false) {
                        return VALIDATE_SENSOR_NO_RULE_VIOLATE;
                    }
                }
            }

            return '';
        },
        createPopup: function()
        {
            let self = control;
            let complexCodePk = '';

            self.selectSensorNoPk = defaultEmptyValue;
            self.selectedHomeDongPk = defaultEmptyValue;
            self.selectedHomeHoPk = defaultEmptyValue;
            self.selectedHomeGrpPk = defaultEmptyValue;
            self.selectedHomeType = defaultEmptyValue;

            self.initialForm();
            self.popupOpen();

            if (isDisabledBuildingSelectBox === false) {
                complexCodePk = gComplexCodePk;
                $popupBuildingType.val(complexCodePk).prop('disabled', true);
            } else {
                $popupBuildingType.val(complexCodePk).prop('disabled', false);
            }
        },
    }

    $btnEnergyEnroll.on("click", function(){
        control.createPopup();
    });

    $btnPopupClose.on("click", function(){
        control.popupClose();
    });

    $btnPopupSave.on("click", function(){
       control.requestUpdateData();
    });

    $buildingType.on("change", function(){
       control.requestEnergyCode($(this));
    });

    $popupBuildingType.on("change", function() {
        control.requestEnergyCode($(this), 'popup_'); // 팝업창에서 건물선택 selectBox..
    });

    $popupEnergyType.on("change", function() {
        control.requestFloorCode($(this)); // 팝업창에 에너지원 선택 시  층 정보 보여줌..
    });

    $btnSearch.on("click", function() {
       control.onSearchValidate();
    });

    $btnEquipmentFirstPage.on("click", function(){
        control.selectedStartPage = 1;
        control.request();
    });

    $btnEquipmentPrevPage.on("click", function(){
        control.selectedStartPage = Number(control.selectedStartPage - 1);
        control.request();
    });

    $btnEquipmentNextPage.on("click", function(){
        control.selectedStartPage = Number(control.selectedStartPage + 1);
        control.request();
    });

    $btnEquipmentLastPage.on("click", function(){
        control.selectedStartPage = Number(control.selectedTotalPage);
        control.request();
    });

    $(document).on("click", ".paging_click", function(e){
        // 페이징번호 클릭시 해당 데이터 조회
        e.preventDefault();

        let self = control;
        let $id = $(this).prop("id");

        let tmp = $id.split('_');

        tmp[2] = Number(tmp[2]);

        self.selectedStartPage = tmp[2];
        self.request();
    });

    $(document).on("click", ".btnEnergyModify", function(){
        control.requestModifyPopup($(this));
    });

    return control;
}