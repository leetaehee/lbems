let control;

$(document).ready(function() {
	let date = new Date();

	createDatepicker($startDate, date);
	createDatepicker($endDate, date, true);

	control = createControl();
	control.request();
	control.initlevel();
});

function createDatepicker($id, date, afterDate = false)
{
	let dateStr = "";

	if (afterDate == true) {
		// 100년뒤로 날짜 설정
		let year = date.getFullYear()  + 10;
		let month = date.getMonth()+1;
		let day  = date.getDate();

		if(month < 10){  month  = "0" + month; }
		if(day < 10){ day = "0" + day; }

		dateStr = year + '-' + month + '-' + day;
	} else {
		dateStr = $.datepicker.formatDate('yy-mm-dd', new Date());
	}

    $id.datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        showMonthAfterYear: true
    });

    $id.val(dateStr);
}

function createControl()
{
	const today = $startDate.val();
	let control = {
		selectedStartPage: startPage,
		selectedCostPk: costPk,
		request: function()
		{
            let self = control;

            let params = [];
            let data = [];

			data.push({
				"start_page" : self.selectedStartPage,
				"view_page_count" : viewPageCount
			});

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
			self.updateEnergyUnitPrice();
		},
		saveEnergyUnitPrice: function(mode)
		{
			let self = control;

			let params = [];
            let data = [];

			let errorMessage = "";
			let energyType = $energyType.val();

			if(energyType == "electric"){
				// 전기 유효성 검증
				errorMessage = self.electricTypeValidate();
			}else{
				// 전기 제외한  유효성 검증
				errorMessage = self.etcTypeValidate();
			}

			if (errorMessage != "") {
				alert(errorMessage);
				return;
			}

			data.push({ 
				"mode": mode,
				"energy_type": energyType,
				"cost_pk": self.selectedCostPk,
				"formData" : $formSubmitId.serialize()
			});

			params.push(
				{name: "requester", value: requester},
				{name: "request", value: "set_unit_price"},
				{name: "params", value: JSON.stringify(data)
			});

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: self.requestSaveCallback,
				callbackParams: null,
				showAlert: true
			};
		
			module.subRequest(requestParams);
		},
		requestSaveCallback: function(data, params)
		{
			let self = control;

			if (data['isOverlap'] === false) {
				alert("등록하려는 데이터가 이미 존재합니다. 확인 후 등록해주세요.");
				return;
			}

			self.request();
		},
		electricTypeValidate: function()
		{
			let used = $used.val();
			let basePrice = $basePrice.val();
			let unitCost = $unitCost.val();
			let gLevel = $gLevel.val();
			
			if ($startDate.val().length < 1) {
				return "시작일을 선택하세요.";
			}

			if ($startDate.val() >= today) {
				return "시작일은 오늘보다 이전일이어야 합니다.";
			}

			if ($endDate.val().length < 1) {
				return "종료일을 선택하세요";
			}

			if ($endDate.val() <= today) {
				return "종료일은 오늘보다 이후 이어야 합니다.";
			}

			if (gLevel.length < 1) {
				return "누진단계를 선택하세요.";
			} else {
				if ($.isNumeric(gLevel) == false) {
					return "누진단계에는 숫자만 입력할 수 있습니다.";
				}

				if ($.inArray(Number(gLevel), gLevelArray) == -1) {
					return "누진단계 값이 상이합니다.";	
				}
			}

			if (used.length < 1) {
				return "사용량을 입력하세요.";
			} else {
				if ($.isNumeric(used) == false) {
					return "사용량에는 숫자만 입력할 수 있습니다.";
				}
			}

			if (basePrice.length < 1) {
				return "기본요금(원)을 입력하세요.";
			} else {
				if ($.isNumeric(basePrice) == false) {
					return "기본요금(원)에는 숫자만 입력할 수 있습니다.";
				}
			}

			if (unitCost.length < 1) {
				return "단가(원)을 입력하세요.";
			} else {
				if ($.isNumeric(unitCost) == false) {
					return "단가(원)에는 숫자만 입력할 수 있습니다.";
				}
			}

			return "";
		},
		etcTypeValidate: function()
		{
			let basePrice = $basePrice.val();
			let unitCost = $unitCost.val();
			
			if (basePrice.length < 1) {
				return "기본요금(원)을 입력하세요.";
			} else {
				if ($.isNumeric(basePrice) == false) {
					return "기본요금(원)에는 숫자만 입력할 수 있습니다.";
				}
			}

			if (unitCost.length < 1) {
				return "단가(원)을 입력하세요.";
			} else {
				if ($.isNumeric(unitCost) == false) {
					return "단가(원)에는 숫자만 입력할 수 있습니다.";
				}
			}

			return "";
		},
		updateteUnitPriceDelete: function()
		{
			let self = control;

			let params = [];
            let data = [];
			
			data.push({
				"cost_pk" : $(choiceChecked).val(),
				"energy_type" : $(".choice_checked:checked").closest("tr").data("energy_type"),
				"mode" : "del"
			});
		
			params.push(
				{name: "requester", value: requester},
				{name: "request", value: "set_unit_price"},
				{name: "params", value: JSON.stringify(data)}
			);

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: null,
				callbackParams: null,
				showAlert: true
			};
		
			module.request(requestParams);
			
			self.request();
		},
		getPopupData: function($this)
		{
			let self = control;

			let params = [];
            let data = [];

			let $tr =  $this.closest('tr');
			const costPk = $tr.data('cost_pk');
			
			data.push({
				'cost_pk' : costPk,
				'energy_type' : $tr.data('energy_type'),
				'mode' : 'select'
			});

			params.push(
				{ name: 'requester', value: requester },
				{ name: 'request', value: 'set_unit_price' },
				{ name: 'params', value: JSON.stringify(data) }
			);

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: self.requestSelectCallback,
				callbackParams: null,
				showAlert: true
			};
		
			module.subRequest(requestParams);

			self.popupOpen(costPk);
		},
		requestSelectCallback: function(data, param)
		{
			let self = control;

			let priceData = data.priceData;

			if (priceData['cost_pk'] == "") {
				alert("데이터를 조회 할 수 없습니다.");
				return;
			}

			if (priceData['energy_type'] == "electric") {
				// 수정 불가능 항목은 비활성화
				$energyType.prop("disabled", true);
				$gLevel.prop("disabled", true).removeClass("bcReadonly");
				$used.prop("readonly", false).removeClass("bcReadonly");

				$startDate.datepicker("option", "disabled", false).removeClass("bcReadonly");
				$endDate.datepicker("option", "disabled", false).removeClass("bcReadonly");

				$energyType.val(priceData['energy_type']);
				$gLevel.val(priceData['g_level']);
				$basePrice.val(priceData['base_price']);
				$unitCost.val(priceData['unit_cost']);
				$used.val(priceData['used']);
				$startDate.val(priceData['start_date']);
				$endDate.val(priceData['end_date'])
			} else {
				// 수정 불가능 항목은 비활성화
				$energyType.prop("disabled", true);
				$gLevel.prop("disabled", true);

				$used.prop("readonly", true).addClass("bcReadonly");

				$startDate.datepicker("option", "disabled", true).addClass("bcReadonly");
				$endDate.datepicker("option", "disabled", true).addClass("bcReadonly");

				$energyType.val(priceData['energy_type']);
				$basePrice.val(priceData['base_price']);
				$unitCost.val(priceData['unit_cost']);
				$used.val(0);
				$gLevel.val(1);
			}

			self.selectedCostPk = priceData['cost_pk'];
		},
		updateEnergyUnitPrice: function()
		{
			let self = control;

			let data = self.data['unitCosts'];
			
			let electric = data['electric'];
			let etc = data['etc'];
			
			let electricCount = data['electricCount'];
			let etcCount = data['etcCount'];

			let $trUnitCosts = "";

			let total = Number(etcCount);

			$("#tbody_enery_unit_price > tr").remove();

			if (total > 0) {

				// 전기 이외에 정보
				for (let i = 0; i < total; i++){
					let $closingDayColumn = "closing_day_" + etc[i]['energy_type'];
					let closingDay = etc[i][$closingDayColumn] == '99' ? '말' : etc[i][$closingDayColumn];

					$trUnitCosts += "<tr data-cost_pk='"+etc[i]['cost_pk']+"' data-energy_type='"+etc[i]['energy_type']+"'>";
					$trUnitCosts += "<td><input type='checkbox' name='chk_no["+i+"]' class='choice_checked' value='"+etc[i]['cost_pk']+"'></td>";
					$trUnitCosts += "<td>"+etc[i]['energy_type_name']+"</td>";
					$trUnitCosts += "<td>"+etc[i]['start_date']+"</td>";
					$trUnitCosts += "<td>"+etc[i]['end_date']+"</td>";
					$trUnitCosts += "<td>"+ closingDay +"일</td>";
					$trUnitCosts += "<td>"+etc[i]['g_level']+"</td>";
					$trUnitCosts += "<td>"+etc[i]['used']+"</td>";
					$trUnitCosts += "<td>"+etc[i]['base_price']+"</td>";
					$trUnitCosts += "<td>"+etc[i]['unit_cost']+"<span class='fs13 fcLgray'>(원/"+etc[i]['energy_type_unit']+")</span></td>";
					$trUnitCosts += "<td><button type='button' class='Btn saveBtn price_modify'>수정</button></td>";
					$trUnitCosts += "</tr>";
				}
			} else {
				$trUnitCosts += "<tr><td colspan='10'>- 에너지 단가 데이터가 존재하지 않습니다 -</td></tr>";
			}

			$tbodyEnergyUnitPrice.html($trUnitCosts);

			// 페이징처리
			let pageParam = {
				total: total,
				currentPage: self.selectedStartPage,
				pageCount: pageCount,
				viewPageCount: viewPageCount, 
				id: "price_paging",
				key: "price"
			};

			module.page(pageParam);

			// 마지막페이지
			self.totalDongPage = Math.ceil(total/viewPageCount);
		},
		initlevel: function()
		{
			let i = 0;
			let optionStr;

			for(i = 0; i < 3; i++){
				optionStr += "<option value='"+(i+1)+"'>"+(i+1)+"</option>";
			}

			$gLevel.html(optionStr);
		},
		popupEnrollForm: function()
		{
			let self = control;
			
			// 초기화
			$energyType.prop("disabled", false);
			$gLevel.prop("disabled", false).removeClass("bcReadonly");
			$used.prop("readonly", false).removeClass("bcReadonly");

			$startDate.datepicker("option", "disabled", false).removeClass("bcReadonly");
			$endDate.datepicker("option", "disabled", false).removeClass("bcReadonly");

			$energyType.val("");
			$gLevel.val(1);
			$basePrice.val("");
			$unitCost.val("");
			$used.val("");

			self.selectedCostPk = "";
			self.popupOpen();
		},
		popupOpen: function(costPk = 0)
		{
			const TITLE_INDEX = costPk > 0 ? 1  : 0;
			$labelPopupTitle.html(`에너지 단가 정보 ${POPUP_TITLES[TITLE_INDEX]}`);
			$labelPopupTitleWord.html(POPUP_TITLES[TITLE_INDEX]);

			standardFormPopup.open();
		},
		popupClose: function()
		{
			standardFormPopup.close(); 
		}
	};

	$allChecked.click("click", function() {
		// 전체 선택/해제 
		if($allChecked.prop("checked") == true){
			$(choiceCheck).prop("checked", true);
		}else{
			$(choiceCheck).prop("checked", false);
		}
	});

	$priceEnroll.click(function() {
		let self = control;

		self.popupEnrollForm();
	});

	$btnPriceButtonClose.on("click", function() {
        let self = control;
		self.popupClose();
    });

	$btnPriceButtonSave.on("click", function() {
		let self = control;
		let mode = "update";

		if($energyType.val() == ""){
			alert("에너지원을 선택하세요!");
			return;
		}

		if(self.selectedCostPk == ""){
			mode = "insert";
		}

		self.saveEnergyUnitPrice(mode);
	});

	$energyType.on("change", function() {
		if($(this).val() != "electric" && $(this).val() != ""){
			$gLevel.prop("disabled", true).addClass("bcReadonly");
			$used.prop("readonly", true).addClass("bcReadonly");

			$startDate.datepicker("option", "disabled", true).addClass("bcReadonly");
			$endDate.datepicker("option", "disabled", true).addClass("bcReadonly");
		}else{
			$gLevel.prop("disabled", false).removeClass("bcReadonly");
			$used.prop("readonly", false).removeClass("bcReadonly");

			$startDate.datepicker("option", "disabled", false).removeClass("bcReadonly");
			$endDate.datepicker("option", "disabled", false).removeClass("bcReadonly");
		}
	});

	$priceDelete.click(function() {
		// 단가 관리 삭제 
		let self = control;

		let len = $(choiceChecked).length;
		if(len < 1){
			alert("에너지 단가 정보를 선택하세요");
			return;
		}

		if(len > 1){
			alert("에너지 단가 정보는 1개만 삭제 가능합니다.");
			return;
		}

		if(confirm("에너지 단가 정보를 정말 삭제하시겠습니까?")){
			self.updateteUnitPriceDelete();
		}
	});

	$btnDongFirstPage.on("click", function(){
		let self = control;

		self.selectedStartPage = 1;
		self.request();
	});

	$btnDongPrevPage.on("click", function(){
		let self = control;

		self.selectedStartPage = Number(self.selectedStartPage - 1);
		self.request();
	});

	$btnDongNextPage.on("click", function(){
		let self = control;

		self.selectedStartPage = Number(self.selectedStartPage + 1);
		self.request();
	});

	$btnDongLastPage.on("click", function(){
		let self = control;

		self.selectedStartPage = Number(self.totalDongPage);
		self.request();
	});

	$(document).on("click", ".price_modify", function(){
		let self = control;
		self.getPopupData($(this));
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