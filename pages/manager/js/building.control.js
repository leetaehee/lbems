let control;

$(document).ready(function() {
	control = createControl();
	control.request();
});

function createControl()
{
	let control = {
		complex: '',
		selectedStartPage: startPage,
		request: function()
		{
            let self = control;

            let params = [];
            let data  = [];

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
			self.initDays();
			self.updateRorenData();
		},
		updateRorenData: function()
		{
			let self = control;
			let complexList = self.data['complex_data']['list'];
			let total = self.data['complex_data']['count'];

			let len = complexList.length;

			let $trComplexList = "";

			if(total > 0){
				$("#tbody_rorean > tr").remove();

				for(let i = 0; i < len; i++){
					let complexData = complexList[i];

					let closingDay = complexData['closing_day_electric'];
					if (CLOSING_DAY_DATA[closingDay] !== undefined) {
						closingDay = CLOSING_DAY_DATA[closingDay];
					}

					$trComplexList += "<tr data-code='"+complexData['complex_code_pk']+"'>";
					$trComplexList += "<td><input type='checkbox' name='chk_no["+i+"]' class='choice_checked' value='"+complexData['complex_code_pk']+"'></td>";
					$trComplexList += "<td>"+complexData['name']+"</td>";
					$trComplexList += "<td>"+complexData['complex_code_pk']+"</td>";
					$trComplexList += "<td>"+complexData['addr']+"</td>";
					$trComplexList += "<td>"+complexData['tel']+"</td>";
					$trComplexList += "<td>"+complexData['fax']+"</td>";
					$trComplexList += "<td>"+complexData['email']+"</td>";
					$trComplexList += "<td>"+complexData['lat']+"</td>";
					$trComplexList += "<td>"+complexData['lon']+"</td>";
					$trComplexList += "<td>"+closingDay+"</td>";
					$trComplexList += "<td><button type='button' class='Btn saveBtn btnRorenModify'>수정</button></td>";
					$trComplexList += "</tr>";
				}
			}else{
				$trComplexList += "<tr><td colspan='15'>- 조회 된 데이터가 존재하지 않습니다. -</td></tr>";
			}

			$tbodyRorean.html($trComplexList);

			// 페이징처리
			let pageParam = {
				total: total,
				currentPage: self.selectedStartPage,
				pageCount: pageCount,
				viewPageCount: viewPageCount, 
				id: "roren_paging",
				key: "roren"
			};

			module.page(pageParam);

			// 마지막페이지
			self.totalPage = Math.ceil(total/viewPageCount);
		},
		requestRorenDetailData: function(code)
		{
			let self = control;

            let params = [];
            let data  = [];

			data.push({
				"complex_code_pk" : code,
				"mode": "select"
			});

			params.push(
				{name: "requester", value: requester},
				{name: "request", value: command},
				{name: "params", value: JSON.stringify(data)}
			);

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: self.requestDetailCallback,
				callbackParams: null,
				showAlert: true
			};
		
			module.request(requestParams);
		},
		requestDetailCallback: function(data, params)
		{
            let self = control;
			
			self.complex = data['complex_data'];
			self.updateRorenDetailData("modify");
		}, 
		updateRorenDetailData: function(type)
		{
			// 팝업 출력 시 데이터 보여주기
			let self = control;

			if (type == "modify") {
				let complex = self.complex;

				self.adminPk = complex['admin_pk'];
				self.complexCodePk = complex['complex_code_pk'];

				$name.val(complex['name']);
				$complexCodePk.val(complex['complex_code_pk']).prop("readonly", true).addClass("bcReadonly");
				$homeDongCnt.val(complex['floor_cnt']);
				$addr.val(complex['addr']);
				$tel.val(complex['tel']);
				$fax.val(complex['fax']);
				$email.val(complex['email']).prop("readonly", true).addClass("bcReadonly");
				$lat.val(complex['lat']);
				$lon.val(complex['lon']);

				$closingDayElectric.val(complex['closing_day_electric']);
			} else {
				self.adminPk = "";
				self.complexCodePk = "";

				$name.val("");
				$complexCodePk.val("").prop("readonly", false).removeClass("bcReadonly");
				$homeDongCnt.val("");
				$addr.val("");
				$tel.val("");
				$fax.val("");
				$email.val("").prop("readonly", true).addClass("bcReadonly");
				$lat.val("");
				$lon.val("");
				$closingDayElectric.val(1);
			}

			self.popupOpen(type);
		},
		saveRorenData: function(mode)
		{
			let self = control;

			let params  = [];
            let data  = [];

			let errorMessage = "";

			// 로렌 하우스 등록 및 수정시 유효성 검증
			errorMessage = self.rorenDataValidate();
			if (errorMessage != "") {
				alert(errorMessage);
				return;
			}

			data.push({ 
				"mode": mode,
				"admin_pk" : self.adminPk,
				"complex_code_pk" : self.complexCodePk,
				"formData" : $formSubmitId.serialize()
			});
			params.push(
				{name: "requester", value: requester},
				{name: "request", value: command},
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

			if (data['error'] === 'Error') {
				return;
			}

			if (data['isOverlap'] === false) {
				alert("등록하려는 데이터가 이미 존재합니다. 확인 후 등록해주세요.");
				return;
			}

			standardFormPopup.close();
			self.request();
		},
		rorenDataValidate: function()
		{
			let name = $name.val();
			let homeDongCnt = $homeDongCnt.val();
			let addr = $addr.val();
			let tel = $tel.val();
			let fax = $fax.val();
			let lat = $lat.val();
			let lon = $lon.val();
			let closingDayElectric = $closingDayElectric.val()*1;

			let phoneFilter = /(^02.{0}|^01.{1}|[0-9]{3})([0-9]+)([0-9]{4})/;

			if (name.length < 1) {
				return '이름을 입력하세요.';
			}

			if (name.length > 20) {
				return '이름은 20자 이내로 입력하세요.';
			}

			if (homeDongCnt.length < 1) {
				return '층 수를 입력하세요.';
			}

			if (addr.length < 1) {
				return '주소를 입력하세요.';
			}

			if (addr.length > 40) {
				return '주소는 40자 이내로 입력하세요.';
			}

			tel = tel.replace(/-/gi, '');

			if (tel.length > 11) {
				return '전화번호는 11자리 이상 초과 할 수 없습니다.'
			}

			if ($.isNumeric(tel) === false) {
				return '전화번호에는 숫자만 입력 할 수 있습니다.';
			}
			
			if (tel.length > 0 && $.isNumeric(tel) === true) {
				$formSubmitId.find("#tel").val(tel.replace(phoneFilter,"$1-$2-$3"));
			}

			fax = fax.replace(/-/gi, '');

			if ($.isNumeric(fax) === false) {
				return 'FAX 에는 숫자만 입력 할 수 있습니다.';
			}

			if (fax.length > 11) {
				return 'FAX 는 11자리 이상 초과 할 수 없습니다.'
			}
			
			if (fax.length > 0 && $.isNumeric(fax) == true) {
				$formSubmitId.find("#fax").val(tel.replace(phoneFilter,"$1-$2-$3"));
			}

			if (lat.length < 1) {
				return "위도를 입력하세요.";
			} else {
				if ($.isNumeric(lat) === false) {
					return '위도에는 숫자만 입력할 수 있습니다.';
				}
			}

			if (lon.length < 1) {
				return '경도를 입력하세요.';
			} else {
				if ($.isNumeric(lon) === false) {
					return "경도에는 숫자만 입력할 수 있습니다.";
				}
			}

			if (closingDayElectric.length < 1) {
				return '마감일을 선택하세요';
			} else {
				closingDayElectric = parseInt(closingDayElectric);

				if (closingDayElectric === false) {
					return '데이터는 숫자이어야 합니다.';
				}

				if (!(closingDayElectric >= CLOSING_DAY_START_DATE_DAY_VALUE && closingDayElectric <= CLOSING_DAY_END_DATE_DAY_VALUE)
					&& closingDayElectric !== CLOSING_DAY_END_DAY_VALUE) {
					return '마감일은 1-28일 또는 말일만 선택 할 수 있습니다.';
				}
			}

			return '';
		},
		updateteRorenDelete: function(code)
		{
			let self = control;

			let params = [];
            let data = [];
			
			data.push({
				"complex_code_pk" : code,
				"mode" : "del"
			});
		
			params.push(
				{name: "requester", value: requester},
				{name: "request", value: command},
				{name: "params", value: JSON.stringify(data)}
			);

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: null,
				callbackParams: null,
				showAlert: true
			};
		
			module.subRequest(requestParams);
			self.request();
		},
		initDays: function()
		{
			// 마감일 콤보 박스에 데이터 추가

			let i = 0;
			let optionData = "";

			// 초기화
			$closingDayElectric.empty();

			// 데이터 추가
			for (i=0; i<28; i++) {
				let temp = "<option value='"+(i+1)+"'>" + (i+1) + "</option>";
					
				optionData += temp;  
			}
			
			optionData += "<option value='99'>말일</option>";

			$closingDayElectric.html(optionData);
		},
		popupOpen: function(type)
		{
			const TITLE_INDEX = type === 'modify' ? 1  : 0;
			$labelPopupTitle.html(`단지 정보 ${POPUP_TITLES[TITLE_INDEX]}`);
			$labelPopupTitleWord.html(POPUP_TITLES[TITLE_INDEX]);

			standardFormPopup.open();
		},
		popupClose: function()
		{
			standardFormPopup.close(); 
		}
	};

	$btnRorenEnroll.on("click", function() {
		let self = control;

		self.updateRorenDetailData("enroll");
	});

	$btnPopupSave.on("click", function() {
		let self = control;
		let mode = "update";

		let email = $email.val();
		let emailFilter =  /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

		if(self.complexCodePk == ""){
			let complexCodePk = $complexCodePk.val();

			if(complexCodePk == ""){
				// 입력시에는 코드번호 입력할 것
				alert("코드를 입력하세요.");
				return;
			}

			if($.isNumeric(complexCodePk) == false){
				alert("데이터는 숫자이어야 합니다.");
				return;
			}

			if(complexCodePk.length > 4 || complexCodePk.length < 4){
				alert("코드는 4자리까지 입력하세요.");
				return;
			}
			mode = "insert";
		}

		if(self.adminPk != ""){
			if (email.length > 0 && emailFilter.test(email) == false) {
					alert("이메일 형식을 확인하세요");
					return;
			}
		}

		self.saveRorenData(mode);
	});

	$btnRorenDelete.on("click", function() {
		// 건물 삭제
		let self = control;
		let code = $(".choice_checked:checked").closest("tr").data("code");

		let len = $(choiceChecked).length;
		if(len < 1){
			alert("단지 정보를 선택하세요");
			return;
		}

		if(len > 1){
			alert("단지 정보는 1개만 삭제 가능합니다.");
			return;
		}

		if(confirm("단지 정보를 정말 삭제하시겠습니까?")){
			self.updateteRorenDelete(code);
		}
	});

	$btnPopupClose.on("click", function() {
		let self = control;
		self.popupClose();
    });

	$btnRorenFirstPage.on("click", function(){
		let self = control;

		self.selectedStartPage = 1;
		self.request();
	});

	$btnRorenPrevPage.on("click", function(){
		let self = control;

		self.selectedStartPage = Number(self.selectedStartPage - 1);
		self.request();
	});

	$btnRorenNextPage.on("click", function(){
		let self = control;

		self.selectedStartPage = Number(self.selectedStartPage + 1);
		self.request();
	});

	$btnRorenLastPage.on("click", function(){
		let self = control;

		self.selectedStartPage = Number(self.totalPage);
		self.request();
	});

	$allChecked.click("click", function() {
		// 전체 선택/해제 
		if($allChecked.prop("checked") == true){
			$(choiceCheck).prop("checked", true);
		}else{
			$(choiceCheck).prop("checked", false);
		}
	});

	$(document).on("click", ".btnRorenModify", function() {
		let self = control;
		let code = $(this).closest("tr").data("code");

		self.requestRorenDetailData(code);
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