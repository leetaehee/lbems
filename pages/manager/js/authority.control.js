let control;

$(document).ready(function() {
	$btnAuthorityFirstPage.css("visibility", "hidden");
	$btnAuthorityPrevPage.css("visibility", "hidden");
	$btnAuthorityNextPage.css("visibility", "hidden");
	$btnAuthorityLastPage.css("visibility", "hidden");

	let BuildingList = module.BuildingList($selectboxRorenType);
	BuildingList.request();

	control = createControl();
	control.initLoginLevelSelectBox();
});

function createControl()
{
	let control = {
		selectedStartPage: startPage,
		request: function()
		{
            let self = control;

            let params = [];
            let data = [];

			data.push({
				"start_page" : self.selectedStartPage,
				"view_page_count" : viewPageCount,
				"mode" : "list",
				"formData" : $formAuthority.serialize()
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
			self.updateAuthorityData();
		},
		updateAuthorityData: function()
		{
			let self = control;
			let authorityData = self.data['authority_data'];

			let authorityList = authorityData['list'];
			let total = authorityData['count'];

			let i;
			let $trAuthorityList = "";

			if (total > 0) {
				$("#tbody_authority > tr").remove();

				for (let i = 0; i < authorityList.length; i++) {
					let authoritys = authorityList[i];

					$trAuthorityList += `<tr data-code=${authoritys['admin_pk']} data-level=${authoritys['login_level']}>`;
					$trAuthorityList += `<td>${authoritys['complex_name']}</td>`;
					$trAuthorityList += `<td>${authoritys['admin_id']}</td>`;
					$trAuthorityList += `<td>${authoritys['admin_name']}</td>`;
					$trAuthorityList += `<td>${authoritys['login_level']}</td>`;
					$trAuthorityList += `<td>${authoritys['hp']}</td>`;
					$trAuthorityList += `<td>${authoritys['email']}</td>`;
					$trAuthorityList += `<td>${authoritys['reg_date']}</td>`;
					$trAuthorityList += `<td>${authoritys['fg_connect']}</td>`;
					$trAuthorityList += `<td>${authoritys['fg_del']}</td>`;
					$trAuthorityList += `<td><button type='button' class='Btn saveBtn btnAuthorityModify'>수정</button></td>`;
					$trAuthorityList += `</tr>`;
				}
			} else {
				$trAuthorityList += "<tr><td colspan='10'>- 조회 된 데이터가 존재하지 않습니다. -</td></tr>";
			}

			$tbodyAuthority.html($trAuthorityList);

			// 페이징처리
			let pageParam = {
				total: total,
				currentPage: self.selectedStartPage,
				pageCount: pageCount,
				viewPageCount: viewPageCount, 
				id: "authority_paging",
				key: "authority"
			};

			module.page(pageParam);

			// 마지막페이지
			self.totalPage = Math.ceil(total/viewPageCount);
		},
		requestAuthorityDetailData: function(code)
		{
			let self = control;

            let params = [];
            let data  = [];

			data.push({
				"admin_pk" : code,
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
			
			self.admin = data['authority_data'];
			self.updateAuthorityDetailData("modify");
		},
		updateAuthorityDetailData: function(type)
		{
			// 팝업 출력 시 데이터 보여주기
			let self = control;

			if (type == "modify") {
				// 수정 버튼 클릭시
				let admin = self.admin;

				// 숨기기
				$popupTrPasswordInitialize.show();

				// 키값 설정
				self.adminPk = admin['admin_pk'];

				$popupRorenType.val(admin['complex_code_pk']).prop('disabled', true);
				$adminId.val(admin['admin_id']).prop("readonly", true).addClass("bcReadonly");
				$name.val(admin['name']);
				$loginLevel.val(admin['login_level']);
				$hp.val(admin['hp']);
				$email.val(admin['email']);

				$("input:radio[name='fg_connect']:radio[value="+admin['fg_connect']+"]").prop("checked", true);
				$("input:radio[name='fg_del']:radio[value="+admin['fg_del']+"]").prop("checked", true);

			} else {
				// 등록버튼 클릭시 
				self.adminPk = '';

				$popupTrPasswordInitialize.hide();

				$popupRorenType.val('').prop('disabled', false);
				$adminId.val('').prop('readonly', false).removeClass('bcReadonly');
				$name.val('');
				$loginLevel.val('');
				$hp.val('');
				$email.val('');

				$("input:radio[name='fg_connect']:radio[value='y']").prop('checked', true);
				$("input:radio[name='fg_del']:radio[value='n']").prop('checked', true);
			}

			self.popupOpen(type);
		},
		saveAuthorityData: function(mode)
		{
			let self = control;

			let params  = [];
            let data  = [];

			let errorMessage = "";

			// 권한 등록 및 수정시 유효성 검증
			errorMessage = self.authorityDataValidate();
			if(errorMessage != ""){
				alert(errorMessage);
				return;
			}

			data.push({ 
				"mode": mode,
				"admin_pk" : self.adminPk,
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
			const adminPk = self.adminPk;

			if (data['isOverlap'] === false) {
				alert("ID가 이미 등록되었습니다. 다른 ID를 등록해주세요.");
				return;
			}

			const newPassword = data['password'];

			if (adminPk === ""
				&& data['isOverlap'] === true) {
				alert(`계정이 생성되었습니다. 비밀번호는 ${newPassword} 입니다.`);
			}

			self.popupClose();
			self.request();
		},
		authorityDataValidate: function()
		{
			// 권한관리 유효성 검증
			let name = $name.val();
			let loginLevel = $loginLevel.val();
			let hp = $hp.val();
			let email = $email.val();

			let phoneFilter = /(^02.{0}|^01.{1}|[0-9]{3})([0-9]+)([0-9]{4})/;
			let emailFilter =  /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

			if (name.length < 1) {
				return '이름을 입력하세요.';
			}

			if (loginLevel.length < 1) {
				return '레벨를 선택하세요.';
			}

			hp = hp.replace(/-/gi, '');

			if (hp.length > 11) {
				return '전화번호는 11자리 이상 초과 할 수 없습니다.'
			}

			if (hp.length > 0 && $.isNumeric(hp) === true) {
				$formSubmitId.find("#hp").val(hp.replace(phoneFilter,'$1-$2-$3'));
			}

			if (email.length > 35) {
				return '이메일은 35자 이내로 입력하세요.';
			}

			if (email.length > 0 && emailFilter.test(email) === false) {
				return '이메일 형식을 확인하세요';
			}
			
			return '';
		},
		updateAuthorityDelete: function()
		{
			let self = control;

			let $checkbox = $(choiceCheck);
			let i;

			let pkData = new Array();

			let params  = [];
            let data  = [];

			for (i = 0; i < $checkbox.length; i++) {
				// 체크 된것만 삭제 할 수 있도록 할 것
				if($checkbox[i].checked == true){
					pkData[i] = $checkbox[i].value;
				}
			}

			data.push({ 
				"mode": "delete",
				"pk" : pkData,
				"complex_code_pk" : $rorenType.val()
			});
			params.push(
				{name: "requester", value: requester},
				{name: "request", value: command},
				{name: "params", value: JSON.stringify(data)
			});

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: self.requestDeleteCallback,
				callbackParams: null,
				showAlert: true
			};
		
			module.subRequest(requestParams);
		},
		requestDeleteCallback: function(data, params)
		{
			let self = control;

			if (data['is_delete'] === true) {
				alert("권한 정보를 삭제 되었습니다.");
			} else {
				if (data['is_delete'] === false) {
					alert("계정은 최소 1개 이상 있어야 합니다.");
					return;
				}
			}

			self.request();
		},
		initLoginLevelSelectBox: function()
		{
			// 권한 콤보박스 
			
			let i = 0;
			let optionData = "<option value=''>권한</option>";

			// 데이터 추가
			for (i = 0; i < $levels.length; i++) {
				let temp = "<option value='"+ $levels[i] +"'>" + $levels[i] + "</option>";
					
				optionData += temp;  
			}

			$authorityType.html(optionData);
			$loginLevel.html(optionData);
		},
		requestPwInitialize: function(adminPk)
		{
			let self = control;

			let params = [];
			let data = [];

			if (adminPk === '') {
				return;
			}

			data.push({ name: 'admin_pk', value: adminPk });
			data.push({ name: 'login_level', value: $loginLevel.val() });

			params.push(
				{ name: 'requester', value: requester },
				{ name: 'request', value: pwInitCommand },
				{ name: 'params', value: JSON.stringify(data) }
			);

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: self.requestPwInitializeCallback,
				callbackParams: null,
				showAlert: true
			};

			module.request(requestParams);
		},
		requestPwInitializeCallback: function(data, params)
		{
			const newPassword = data['password'];

			if (newPassword === '' || newPassword === undefined) {
				alert("KevinLAB 에 문의하세요.");
				return;
			}

			alert(`변경 된 비밀번호는 ${newPassword}입니다.`);
		},
		popupOpen: function(type)
		{
			const TITLE_INDEX = type === 'modify' ? 1  : 0;
			$labelPopupTitle.html(`권한 정보 ${POPUP_TITLES[TITLE_INDEX]}`);
			$labelPopupTitleWord.html(POPUP_TITLES[TITLE_INDEX]);

			standardFormPopup.open();
		},
		popupClose: function()
		{
			standardFormPopup.close(); 
		}
	};

	$btnSearch.on("click", function() {
		let self = control;

		if ($rorenType.val().length < 1) {
			alert('단지을 선택하세요.');
			$rorenType.focus();
			return;
		}

		self.request();
	});

	$btnAtuthorityDelete.on('click', function() {
		// 권한 내역 삭제
		let self = control;

		let len = $(choiceChecked).length;
		if(len < 1){
			alert("권한 정보를 선택하세요");
			return;
		}
	
		if (confirm("권한 정보를 삭제 하시면 로그인 할 수 없습니다. 삭제 하시겠습니까?")) {
			self.updateAuthorityDelete();
		}
	});

	$btnAuthorityEnroll.on("click", function() {
		let self = control;
		self.updateAuthorityDetailData("enroll");
	});

	$btnButtonClose.on("click", function() {
        let self = control;

		self.popupClose();
    });

	$btnPopupSave.on("click", function() {
		let self = control;
		let mode = "update";

		if(self.adminPk == ""){
			let popupRorenType = $popupRorenType.val();
			let adminId = $adminId.val();

			let idFilter = /^[a-z0-9]{3,15}/g;

			if(popupRorenType == ""){
				alert("단지를 선택하세요.");
				return;
			}

			if(adminId == ""){
				alert("ID를 입력하세요.");
				return;
			}else{
				if(idFilter.test(adminId) == false){
					alert("아이디에는 영문자 소문자 또는 숫자로만 이루어져야 합니다.");
					return;
				}
			}

			mode = "insert";
		}

		self.saveAuthorityData(mode);
	});

	$btnAuthorityFirstPage.on("click", function(){
		let self = control;

		self.selectedStartPage = 1;
		self.request();
	});

	$btnAuthorityPrevPage.on("click", function(){
		let self = control;

		self.selectedStartPage = Number(self.selectedStartPage - 1);
		self.request();
	});

	$btnAuthorityNextPage.on("click", function(){
		let self = control;

		self.selectedStartPage = Number(self.selectedStartPage + 1);
		self.request();
	});

	$btnAuthorityLastPage.on("click", function(){
		let self = control;

		self.selectedStartPage = Number(self.totalPage);
		self.request();
	});

	$allChecked.on("click", function() {
		// 전체 선택/해제 
		if($allChecked.prop("checked") == true){
			$(choiceCheck).prop("checked", true);
		}else{
			$(choiceCheck).prop("checked", false);
		}
	});

	$(document).on("click", ".choice_checked", function() {
		// 최고관리자는 삭제 되면 안되서 막아놓음
		let level = $(this).closest("tr").data("level");
		
		if(level == "최고관리자"){
			alert("최고관리자는 선택 할 수 없습니다.");
			$(this).prop("checked", false);
		}
	});

	$(document).on("click", ".btnPasswordInitialize", function() {
		const code = control.adminPk;

		if (confirm(CONFIRM_MESSAGE) === true) {
			control.requestPwInitialize(code);
		}
	});

	$(document).on("click", ".btnAuthorityModify", function() {
		let self = control;
		let code = $(this).closest("tr").data("code");

		self.requestAuthorityDetailData(code);
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