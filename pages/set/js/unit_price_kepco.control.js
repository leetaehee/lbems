let control;

$(document).ready(function() {
    control = createControl();
    control.requestMenuAuthority(group);
});

function createControl()
{
    let control = {
        selectedStartPage: startPage,
        selectedElectricType: defaultElectricType,
        selectedIsUseManage: defaultIsUseManage,
        selectedCostNo: defaultZeroValue,
        selectedTotalPage: 0,
        request: function()
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'electric_type', value: self.selectedElectricType });
            data.push({ name: 'start_page', value: self.selectedStartPage });
            data.push({ name: 'view_page_count', value: viewPageCount });
            data.push({ name: 'is_use_manage', value: self.selectedIsUseManage });

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
            self.updatePriceList();
        },
        updatePriceList: function()
        {
            let self = control;

            const isUseManage = self.selectedIsUseManage;

            const data = self.data['electric_prices'];
            const list = data['list'];
            const etcPrices = data['price_etc'];
            const dataCount = parseInt(data['count']);

            let $trUnitCosts = '';

            $("#tbody_energy_unit_price > tr").remove();

            if (dataCount < 1) {
                $trUnitCosts = `<tr><td id="empty-table-td" colspan="${tableEmptyColspan}">- 에너지 단가 데이터가 존재하지 않습니다 -</td></tr>`;
            }

            if (dataCount > 0) {
                $.each(list, function(key, items) {
                    let seasonCode = items['summerGubun'];

                    $trUnitCosts += `<tr style="cursor: pointer" data-cost_no="${items['idx']}">`;
                    $trUnitCosts += `<td>${self.getElectricTypeName(items['electricType'])}</td>`;
                    $trUnitCosts += `<td>${self.getGUBJTypeGubunName(items['typeGubun'])}</td>`;
                    $trUnitCosts += `<td>${self.getLowHighTypeGubunName(items['typeGubun2'])}</td>`;
                    $trUnitCosts += `<td>${self.getSelectName(items['typeSelect'])}</td>`;
                    $trUnitCosts += `<td>${SEASON_TYPES[seasonCode]}</td>`;
                    $trUnitCosts += `<td>${module.utility.addComma(items['defaultPrice'])}</td>`;
                    $trUnitCosts += `<td>${module.utility.addComma(items['cost'])}</td>`;
                    $trUnitCosts += `<td>${etcPrices['etcPrice1']}</td>`;
                    $trUnitCosts += `<td>${etcPrices['etcPrice2']}</td>`;
                    $trUnitCosts += `<td>${etcPrices['etcPrice3']}</td>`;
                    $trUnitCosts += `<td>${etcPrices['etcPrice4']}</td>`;

                    if (isUseManage === true) {
                       $trUnitCosts += `<td><button type="button" class="Btn saveBtn price_modify">수정</button></td>`;
                    }

                    $trUnitCosts += `</tr>`;
                });
            }

            $tbodyEnergyUnitPrice.html($trUnitCosts);

            // 페이징처리
            let pageParam = {
                total: dataCount,
                currentPage: self.selectedStartPage,
                pageCount: pageCount,
                viewPageCount: viewPageCount,
                id: "price_paging",
                key: "price"
            };

            module.page(pageParam);

            // 마지막페이지
            self.selectedTotalPage = Math.ceil(dataCount/viewPageCount);
        },
        getElectricTypeName: function(electricType)
        {
            let electricTypeName = '';

            switch (electricType) {
                case PRICE_TYPES['normal']:
                    electricTypeName = '일반용';
                    break;
                case PRICE_TYPES['industry']:
                    electricTypeName = '산업용';
                    break;
            }

            return electricTypeName;
        },
        getLowHighTypeGubunName: function(typeGubun)
        {
            let typeName = '';

            switch (typeGubun) {
                case PRICE_TYPES['low'] :
                    typeName = '저압';
                    break;
                case PRICE_TYPES['high'] :
                    typeName = '고압';
                    break;
                case PRICE_TYPES['high1'] :
                    typeName = '고압A';
                    break;
                case PRICE_TYPES['high2'] :
                    typeName = '고압B';
                    break;
                case PRICE_TYPES['high3'] :
                    typeName = '고압C';
                    break;
                default:
                    typeName = '-';
                    break;
            }

            return typeName;
        },
        getGUBJTypeGubunName: function(typeGubun)
        {
            let typeName = '';

            switch (typeGubun) {
                case PRICE_TYPES['type1']:
                    typeName = '갑1';
                    break;
                case PRICE_TYPES['type2']:
                    typeName = '갑2';
                    break;
                case PRICE_TYPES['type3']:
                    typeName = '을';
                    break;
                default:
                    typeName = '-';
            }

            return typeName;
        },
        getSelectName: function(select)
        {
            let selectName = '';

            switch (select)
            {
                case PRICE_TYPES['select1']:
                    selectName = '선택1';
                    break;
                case PRICE_TYPES['select2']:
                    selectName = '선택2';
                    break;
                case PRICE_TYPES['select3']:
                    selectName = '선택3';
                    break;
                default:
                    selectName = '-';
            }

            return selectName;
        },
        requestPopup: function($this)
        {
            let self = control;
            self.selectedCostNo = defaultZeroValue;

            let params = [];
            let data = [];

            let costNo =  $this.closest("tr").data('cost_no');

            data.push({ name: 'cost_no', value: costNo });

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: kepcoCommand },
                {name: "params", value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestPopupCallback,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestPopupCallback: function(data, params)
        {
            let self = control;
            const costData = data['cost_data'];
            const etcPrices = data['etc_price'];

           if (costData.length < 1) {
               alert("해당되는 데이터가 존재하지 않습니다.");
               return;
           }

           self.initialForm(); // 초기화
           self.selectedCostNo = costData['idx'];

           let seasonCode = costData['summerGubun'];

           $labelElectricTypeName.html(self.getElectricTypeName(costData['electricType']));
           $labelTypeGubunName.html(self.getGUBJTypeGubunName(costData['typeGubun']));
           $labelTypeGubun2Name.html(self.getLowHighTypeGubunName(costData['typeGubun2']));
           $labelTypeSelectName.html(self.getSelectName(costData['typeSelect']));
           $labelSummerGubunName.html(SEASON_TYPES[seasonCode]);
           $labelSection.html(costData['section']);
           $labelStatusLevel.html(costData['level']);
           $labelApplyStartDate.html(costData['startDate']);
           $labelApplyEndDate.html(costData['endDate']);
           $popupDefaultPrice.val(costData['defaultPrice']);
           $popupCost.val(costData['cost']);
           $labelEtcPrice1.html(etcPrices['etcPrice1']);
           $labelEtcPrice2.html(etcPrices['etcPrice2']);
           $labelEtcPrice3.html(etcPrices['etcPrice3']);
           $labelEtcPrice4.html(etcPrices['etcPrice4']);

           standardFormPopup.open();
        },
        requestSavePriceInfo: function()
        {
            let self = control;

            let params = [];
            let data = [];

            const errorMessage = self.formDataValidate();

            if (errorMessage != "") {
                alert(errorMessage);
                return;
            }

            data.push({ name: 'cost_no', value: self.selectedCostNo });
            data.push({ name: 'form_data', value: $formSubmitId.serialize() });

            params.push(
                {name: "requester", value: requester},
                {name: "request", value: setKepcoCommand},
                {name: "params", value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestSavePriceInfoCallback,
                callbackParams: null,
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestSavePriceInfoCallback: function(data, params)
        {
            let self = control;

            if (data['error'] === 'dataError') {
                return;
            }

            if (data['error'] === 'validateError') {
                alert('숫자 데이터를 입력하세요.')
                return;
            }

            self.popupClose();
            self.request();
        },
        formDataValidate: function()
        {
            let errorMessage = '';
            $.each($popupInput, function(index, selector) {
                let val  = selector.val();
                if (val.length < 1) {
                    errorMessage = `${$popupText[index]}를 입력하세요.`;
                    return false;
                } else {
                    if ($.isNumeric(val) == false) {
                        errorMessage = `${$popupText[index]}에는 숫자만 입력할 수 있습니다.`;
                        return false;
                    }
                }
            });

            return errorMessage;
        },
        initialForm: function()
        {
            $labelElectricTypeName.html(defaultEmptyValue);
            $labelTypeGubunName.html(defaultEmptyValue);
            $labelTypeGubun2Name.html(defaultEmptyValue);
            $labelTypeSelectName.html(defaultEmptyValue);
            $labelSummerGubunName.html(defaultEmptyValue);
			$labelSection.html(defaultEmptyValue);
			$labelStatusLevel.html(defaultEmptyValue);
			$labelApplyStartDate.html(defaultEmptyValue);
			$labelApplyEndDate.html(defaultEmptyValue);
            $popupDefaultPrice.val(defaultEmptyValue);
            $popupCost.val(defaultEmptyValue);
            $labelEtcPrice1.val(defaultEmptyValue);
            $labelEtcPrice2.val(defaultEmptyValue);
            $labelEtcPrice3.val(defaultEmptyValue);
			$labelEtcPrice4.val(defaultEmptyValue);
        },
        popupClose: function()
        {
            standardFormPopup.close();
        },
        requestMenuAuthority: function(groupId)
        {
            let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'group_id', value: parseInt(groupId) });

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

            module.request(requestParams);
        },
        requestMenuAuthorityCallback: function(data, params)
        {
            let self = control;

            if (data['error'] === 'data-error') {
                return;
            }

            const authority = parseInt(data['authority']);

            if (authority === 100) {
                $loginLogPageIcon.attr('src', settingPageIcon);
                $menuGroupSelector.html(data['group_name']);
            } else {
                tableEmptyColspan = 11;

                $("#empty-table-td").attr('colspan', tableEmptyColspan);
                $(".modify-button-area").css('display', 'none');

                $menuGroupSelector.html(defaultMenuGroupName);

                self.selectedIsUseManage = false;
            }

            self.request();
        },
    };

    $btnPriceButtonClose.on("click", function() {
        control.popupClose();
    });

    $btnPriceButtonSave.on("click", function() {
        control.requestSavePriceInfo();
    });

    $btnPriceFirstPage.on("click", function(){
        control.selectedStartPage = 1;
        control.request();
    });

    $btnPricePrevPage.on("click", function(){
        control.selectedStartPage = Number(control.selectedStartPage - 1);
        control.request();
    });

    $btnPriceNextPage.on("click", function(){
        control.selectedStartPage = Number(control.selectedStartPage + 1);
        control.request();
    });

    $btnPriceLastPage.on("click", function(){
        control.selectedStartPage = Number(control.selectedTotalPage);
        control.request();
    });

    $(document).on("click", ".price_modify", function(){
        control.requestPopup($(this));
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

    return control;
}