let control;

$(document).ready(function() {
    control = createControl();
    control.requestHTMLRender();
    control.requestEvent();
    control.request();
});

function createControl()
{
    let control = {
        selectedSpecialTypes: '',
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: command},
                {name: "params", value: JSON.stringify(data)}
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
            self.updateInfoData();
        },
        updateInfoData: function()
        {
            // 초기 데이터 출력
            let self = control;

            let complexData = self.data['complex_data'];

            let sensorTypes = complexData['types'];
            let data = complexData['data'];

            let specials = complexData['specials'];
            self.selectedSpecialTypes = specials;

            // 에너지원
            for (let i = 0; i < sensorTypes.length; i++) {
                let sensorSelector = self.getOilDyuRealKey(sensorTypes[i]);
                let sensorType = self.getRealStandardValue(sensorSelector);

                let standardColumn = 'limit_val_' + sensorType;
                let standardValue = data[standardColumn];

                let $hourSelector = $('#' + sensorSelector + '_hour');
                let $daySelector = $('#' + sensorSelector + '_day');
                let $monthSelector = $('#' + sensorSelector + '_month');
                let $yearSelector = $('#' + sensorSelector + '_year');

                if (standardValue === undefined) {
                    continue;
                }

                if ($hourSelector.prop('id') === undefined || $daySelector.prop('id') === undefined
                    || $monthSelector.prop('id') === undefined || $yearSelector.prop('id') === undefined) {
                    continue;
                }

                let standards = standardValue.split('/');

                // 기준값 설정
                $hourSelector.val(standards[0]);
                $daySelector.val(standards[1]);
                $monthSelector.val(standards[2]);
                $yearSelector.val(standards[3]);
            }
            
            if (CONFIGS['is_use_environment'] === true) {
                // 환경정보 기준값
                let finedustStandardValue = data['limit_val_finedust'];
                let co2StandardValue = data['limit_val_co2'];

                let finedustStandards = finedustStandardValue.split('/');
                let co2Standards = co2StandardValue.split('/');

                let $finedustCo2Selector = $("#finedust_co2");
                let $finedustFm25Selector = $("#finedust_fm25");

                if (finedustStandardValue === undefined) {
                    return;
                }

                if (co2StandardValue === undefined) {
                    return;
                }

                if ($finedustCo2Selector.prop('id') === undefined
                    || $finedustFm25Selector.prop('id') === undefined) {
                    return;
                }

                $finedustCo2Selector.val(co2Standards[0]);
                $finedustFm25Selector.val(finedustStandards[1]);
            }

            if (CONFIGS['is_use_environment'] === false) {
                // 미세먼지 기준값
                let finedustStandardValue = data['limit_val_finedust'];
                let finedustStandards = finedustStandardValue.split('/');

                let $finedustFM10Selector = $("#finedust_fm10");
                let $finedustFM25Selector = $("#finedust_fm25");

                if (finedustStandardValue === undefined) {
                    return;
                }

                if ($finedustFM10Selector.prop('id') === undefined
                    || $finedustFM25Selector.prop('id') === undefined) {
                    return;
                }

                $finedustFM10Selector.val(finedustStandards[0]);
                $finedustFM25Selector.val(finedustStandards[1]);
            }
        },
        getOilDyuRealKey: function(key)
        {
            return key == 'oil_dyu' ? 'heating' : key; // 등유만 단독 예외처리..
        },
        getRealStandardValue: function(keyName)
        {
            let self = control;

            if (replaceKeyMaps[keyName] !== undefined) {
                return replaceKeyMaps[keyName];
            }

            let specials = self.selectedSpecialTypes;
            if (specials[keyName] === undefined) {
                return keyName;
            }

            return specials[keyName]['sensor_type'];
        },
        saveData: function($event, $formId, energyKey, option = 0)
        {
            // 저장
            $event.preventDefault();

            let self = control;

            let params = [];
            let data = [];

            let errorMessage = '';

            if (energyKey === 'finedust') {
                // 미세먼지 저장
                errorMessage = self.finedustValidate($formId);
            } else if (energyKey === 'envrionment') {
                errorMessage = self.finedustAndCo2Validate($formId);
            } else {
                // 에너지원 시간정보 저장
                errorMessage = self.energyTargetFormValidate(energyKey);
            }

            if (errorMessage != '') {
                alert(errorMessage);
                return;
            }

            data.push({ name: 'option', value: option });
            data.push({ name: 'form_data', value: $formId.serialize() });
            data.push({ name: 'energy_key', value: energyKey });

            params.push(
                {name: 'requester', value: requester },
                {name: 'request', value: 'set_standard_save' },
                {name: 'params', value: JSON.stringify(data) }
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.go,
                callbackParams: null,
                showAlert: true
            };

            module.request(requestParams);
        },
        go: function()
        {
            control.request();
        },
        energyTargetFormValidate: function($partId)
        {
            // 에너지원 사용량 유효성 체크
            let $hour = $("#" + $partId + "_hour").val();
            let $day = $("#" + $partId + "_day").val();
            let $month = $("#" + $partId + "_month").val();
            let $year = $("#" + $partId + "_year").val();

            if ($hour.length < 1) {
                return VALIDATE_TIME_VALUE_EMPTY;
            } else {
                if ($.isNumeric($hour) == false) {
                    return VALIDATE_TIME_VALUE_ONLY_INTEGER;
                }

                if(Number($hour) > Number($day)){
                    return VALIDATE_TIME_VALUE_OVER;
                }
            }

            if ($day.length < 1) {
                return VALIDATE_DAY_VALUE_EMPTY;
            } else {
                if ($.isNumeric($day) == false) {
                    return VALIDATE_DAY_VALUE_ONLY_INTEGER;
                }

                if (Number($day) > Number($month)) {
                    return VALIDATE_DAY_VALUE_OVER;
                }
            }

            if ($month.length < 1) {
                return VALIDATE_MONTH_VALUE_EMPTY;
            } else {
                if ($.isNumeric($month) == false) {
                    return VALIDATE_MONTH_VALUE_ONLY_INTEGER;
                }

                if (Number($month) > Number($year)) {
                    return VALIDATE_MONTH_VALUE_OVER;
                }
            }

            if ($year.length < 1) {
                return VALIDATE_YEAR_VALUE_EMPTY;
            } else {
                if ($.isNumeric($year) == false) {
                    return VALIDATE_YEAR_VALUE_ONLY_INTEGER;
                }
            }

            const standardString = `${$hour}/${$day}/${$month}/${$year}`;
            if (standardString.length + 3 > STANDARD_STRING_TOTAL_LENGTH) {
                return VALIDATE_VALUE_LENGTH_CHECK;
            }

            return '';
        },
        finedustAndCo2Validate: function($formId)
        {
            let finedustCO2 = $formId.find("#finedust_co2").val();
            let finedustFM25 = $formId.find("#finedust_fm25").val();

            if (finedustCO2.length < 1) {
                return VALIDATE_CO2_VALUE_EMPTY;
            } else {
                if ($.isNumeric(finedustCO2) == false) {
                    return VALIDATE_CO2_VALUE_ONLY_INTEGER;
                }
            }

            if (finedustFM25.length < 1) {
                return VALIDATE_PM25_VALUE_EMPTY;
            } else {
                if ($.isNumeric(finedustFM25) == false) {
                    return VALIDATE_PM25_VALUE_ONLY_INTEGER;
                }
            }

            return '';
        },
        finedustValidate: function($formId)
        {
            let finedustFM10 = $formId.find("#finedust_fm10").val();
            let finedustFM25 = $formId.find("#finedust_fm25").val();

            if (finedustFM10.length < 1) {
                return VALIDATE_PM10_VALUE_EMPTY;
            } else {
                if ($.isNumeric(finedustFM10) == false) {
                    return VALIDATE_PM10_VALUE_ONLY_INTEGER;
                }
            }

            if (finedustFM25.length < 1) {
                return VALIDATE_PM25_VALUE_EMPTY;
            } else {
                if ($.isNumeric(finedustFM25) == false) {
                    return VALIDATE_PM25_VALUE_ONLY_INTEGER;
                }
            }

            return '';
        },
        requestEvent: function()
        {
            let self = control;

            let params = [];
            let data = [];

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: setCommand},
                {name: "params", value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.updateEventHandler,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        updateEventHandler: function(data, params)
        {
            let self = control;

            if (data['error'] === 'Error') {
                return;
            }

            const finedustType = (CONFIGS['is_use_environment'] === true) ? 'envrionment' : 'finedust';

            $.each(data, function(energyType, item) {
                // 냉장 냉동설비에 대해서..
                energyType = self.changeEnergyType(energyType);

                // 등유에 대해서..
                energyType = self.getOilDyuRealKey(energyType);

                const $formId = $("#form_time_" + energyType);

                $("#btn_" + energyType).on("click", function(event) {
                    self.saveData(event, $formId, energyType, parseInt(item));
                });
            });

            $("#btn_finedust").on("click", function(event) {
                control.saveData(event, $("#form_time_finedust"), finedustType, 0);
            });
        },
        requestHTMLRender: function()
        {
            let self = control;

            $buildingText.html(SET_MENU_BUILDING_TEXT);

            $.each(settingData, function(group, groupItems) {
                let groupName = groupItems['group'];
                let inputTitles = groupItems['input_boxes'];
                let inputs = groupItems['items'];

                const $forms = [];
                let formString = '';
                let formTagId = '';

                $.each(inputs, function(key, value){
                    let groupTitle = `${groupName} - ${key}`;
                    if (groupName === key) {
                        groupTitle = groupName;
                    }

                    // <p> 태그 생성
                    let $pTag = $("<p></p>")
                        .attr('class', pClasses)
                        .html(`${groupTitle}`);

                    value = self.changeEnergyType(value);

                    // <div> <table> 그룹으로 만들기
                    let $tableGroupTag;
                    if (group === 'device') {
                        inputs = inputs[groupName];
                        $tableGroupTag = self.getFinedustTableTag(group, inputs, inputTitles);
                    } else {
                        $tableGroupTag = self.getTimeTableTag(group, value, inputTitles);
                    }

                    formTagId = FORM_PREFIX + value;
                    if (Array.isArray(value) === true && value.length > 0) {
                        formTagId = FORM_PREFIX + 'finedust';
                    }

                    let $formTag = $("<form></form>")
                        .attr('id', formTagId)
                        .append($pTag[0].outerHTML)
                        .append($tableGroupTag[0].outerHTML);

                    $forms.push($formTag[0].outerHTML);
                });

                formString = $forms.join('');
                $sectionGroup.append(formString);
            });
        },
        changeEnergyType: function(energyType)
        {
            let realEnergyType = energyType;

            if (jQuery.inArray(energyType, equipmentTypes) >= 0) {
                realEnergyType = EQUIPMENT_NAME;
            }

            return realEnergyType;
        },
        getTimeTableTag: function(key, itemKey, inputTitles)
        {
            let self = control;

            const $tds = [];
            const $cols = [];

            const spanIcon = $("<span></span>").attr('class', thTagIconClass);
            
            let inputIngNo = 0;

            $.each(TIME_COLS, function(index, value){
                let sequence = (index + 1);

                let $col = $("<col>").css("width", value + "%");
                let $td;

                if (sequence % 2 === 1) {
                    // 홀수인 경우 <th> 태그 사용..
                    $td = $("<th></th>").append(`${spanIcon[0].outerHTML} ${inputTitles[inputIngNo]}`);
                } else {
                    // 짝수인 경우 <td> 태그 사용..
                    let inputKeyName = itemKey + LABEL_TIME_SUFFIX[inputIngNo];
                    let $input = $("<input>")
                        .attr({
                            'type': 'text',
                            'id': inputKeyName,
                            'name': inputKeyName,
                            'class': w100Class,
                        });

                    $td = $("<td></td>").html($input[0].outerHTML);
                    inputIngNo = inputIngNo + 1;
                }

                $cols.push($col[0].outerHTML);
                $tds.push($td[0].outerHTML);
            });

            return self.makeTableTag(itemKey, $cols, $tds);
        },
        getFinedustTableTag: function(key, inputs, inputTitles)
        {
            let self = control;

            const $tds = [];
            const $cols = [];

            let $tdColspan;
            const inputLength = inputs.length;

            const spanIcon = $("<span></span>").attr('class', thTagIconClass);

            $.each(FINEDUST_COLS, function(index, value){
                // <col> 태그 동적생성
                let $col = $("<col>").css("width", value + "%");
                $cols.push($col[0].outerHTML);
            });

            $.each(inputs, function(index, key){
                // <th>, </td> 태그 동적 생성
                // <input> 태그 동적 생성
                let $th = $("<th></th>").append(`${spanIcon[0].outerHTML} ${inputTitles[index]}`);

                let $input = $("<input>")
                    .attr({
                        'type': 'text',
                        'id': key,
                        'name': key,
                        'class': w100Class,
                    });
                let $td = $("<td></td>").append($input[0].outerHTML);

                $tds.push($th[0].outerHTML);
                $tds.push($td[0].outerHTML);
            });

            // 여백 colspan 처리
            const colspanNum = FINEDUST_COLS.length - (inputLength * COLSPAN_STANDARD_VALUE);
            $tdColspan = $("<td></td>")
                .attr('colspan', colspanNum)
                .css('background-color', "rgb(" + colspanColor + ")");

            $tds.push($tdColspan[0].outerHTML);

            return self.makeTableTag(BTN_FINEDUST_KEY_NAME, $cols, $tds);
        },
        makeTableTag: function(key, $cols, $tds)
        {
            const $div = $("<div></div>");
            const $table = $("<table></table>");
            const $colGroup = $("<colgroup>");
            const $tbodyGroup = $("<tbody></tbody>");
            const $button = $("<button></button>");

            $colGroup.append($cols.join(''));
            $tbodyGroup.append("<tr>" +  $tds.join('') + "</tr>");
            $button.attr({
                'type': 'button',
                'id': BTN_PREFIX + key,
                'class': btnClasses
            }).html(BTN_DISPLAY_SAVE_NAME);

            // 테이블 태그정보 추가
            $table.attr('class', w100Class)
                  .append($colGroup[0].outerHTML)
                  .append($tbodyGroup[0].outerHTML);

            $div.attr('class', divClasses)
                .append($table[0].outerHTML)
                .append($button[0].outerHTML);

            return $div;
        }
    };

    return control;
}