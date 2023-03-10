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
					$trAuthorityList += `<td><button type='button' class='Btn saveBtn btnAuthorityModify'>??????</button></td>`;
					$trAuthorityList += `</tr>`;
				}
			} else {
				$trAuthorityList += "<tr><td colspan='10'>- ?????? ??? ???????????? ???????????? ????????????. -</td></tr>";
			}

			$tbodyAuthority.html($trAuthorityList);

			// ???????????????
			let pageParam = {
				total: total,
				currentPage: self.selectedStartPage,
				pageCount: pageCount,
				viewPageCount: viewPageCount, 
				id: "authority_paging",
				key: "authority"
			};

			module.page(pageParam);

			// ??????????????????
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
			// ?????? ?????? ??? ????????? ????????????
			let self = control;

			if (type == "modify") {
				// ?????? ?????? ?????????
				let admin = self.admin;

				// ?????????
				$popupTrPasswordInitialize.show();

				// ?????? ??????
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
				// ???????????? ????????? 
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

			// ?????? ?????? ??? ????????? ????????? ??????
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
				alert("ID??? ?????? ?????????????????????. ?????? ID??? ??????????????????.");
				return;
			}

			const newPassword = data['password'];

			if (adminPk === ""
				&& data['isOverlap'] === true) {
				alert(`????????? ?????????????????????. ??????????????? ${newPassword} ?????????.`);
			}

			self.popupClose();
			self.request();
		},
		authorityDataValidate: function()
		{
			// ???????????? ????????? ??????
			let name = $name.val();
			let loginLevel = $loginLevel.val();
			let hp = $hp.val();
			let email = $email.val();

			let phoneFilter = /(^02.{0}|^01.{1}|[0-9]{3})([0-9]+)([0-9]{4})/;
			let emailFilter =  /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

			if (name.length < 1) {
				return '????????? ???????????????.';
			}

			if (loginLevel.length < 1) {
				return '????????? ???????????????.';
			}

			hp = hp.replace(/-/gi, '');

			if (hp.length > 11) {
				return '??????????????? 11?????? ?????? ?????? ??? ??? ????????????.'
			}

			if (hp.length > 0 && $.isNumeric(hp) === true) {
				$formSubmitId.find("#hp").val(hp.replace(phoneFilter,'$1-$2-$3'));
			}

			if (email.length > 35) {
				return '???????????? 35??? ????????? ???????????????.';
			}

			if (email.length > 0 && emailFilter.test(email) === false) {
				return '????????? ????????? ???????????????';
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
				// ?????? ????????? ?????? ??? ??? ????????? ??? ???
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
				alert("?????? ????????? ?????? ???????????????.");
			} else {
				if (data['is_delete'] === false) {
					alert("????????? ?????? 1??? ?????? ????????? ?????????.");
					return;
				}
			}

			self.request();
		},
		initLoginLevelSelectBox: function()
		{
			// ?????? ???????????? 
			
			let i = 0;
			let optionData = "<option value=''>??????</option>";

			// ????????? ??????
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
				alert("KevinLAB ??? ???????????????.");
				return;
			}

			alert(`?????? ??? ??????????????? ${newPassword}?????????.`);
		},
		popupOpen: function(type)
		{
			const TITLE_INDEX = type === 'modify' ? 1  : 0;
			$labelPopupTitle.html(`?????? ?????? ${POPUP_TITLES[TITLE_INDEX]}`);
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
			alert('????????? ???????????????.');
			$rorenType.focus();
			return;
		}

		self.request();
	});

	$btnAtuthorityDelete.on('click', function() {
		// ?????? ?????? ??????
		let self = control;

		let len = $(choiceChecked).length;
		if(len < 1){
			alert("?????? ????????? ???????????????");
			return;
		}
	
		if (confirm("?????? ????????? ?????? ????????? ????????? ??? ??? ????????????. ?????? ???????????????????")) {
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
				alert("????????? ???????????????.");
				return;
			}

			if(adminId == ""){
				alert("ID??? ???????????????.");
				return;
			}else{
				if(idFilter.test(adminId) == false){
					alert("??????????????? ????????? ????????? ?????? ???????????? ??????????????? ?????????.");
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
		// ?????? ??????/?????? 
		if($allChecked.prop("checked") == true){
			$(choiceCheck).prop("checked", true);
		}else{
			$(choiceCheck).prop("checked", false);
		}
	});

	$(document).on("click", ".choice_checked", function() {
		// ?????????????????? ?????? ?????? ????????? ????????????
		let level = $(this).closest("tr").data("level");
		
		if(level == "???????????????"){
			alert("?????????????????? ?????? ??? ??? ????????????.");
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
		// ??????????????? ????????? ?????? ????????? ??????
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