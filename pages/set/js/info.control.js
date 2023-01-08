let control;

$(document).ready(function() {
	control = createControl();

	control.initDays();
	control.request();
});

function createControl()
{
	let control = {
		selectedEmail: '',
		request: function()
		{
			let self = control;

			let params  = [];
			let data  = [];

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
			self.updateInfoData();
		},
		updateInfoData: function()
		{
			// 초기 데이터 출력
			let self = control;
			let complexData = self.data['complex_data'];

			self.complexData = complexData;

			self.updateBasicData();
			self.updateEnergyTargetData();
		},
		updateBasicData: function()
		{
			// 기본정보 출력
			let self = control;
			let complexData = self.complexData['data'];
			let adminData = self.complexData['admin'];

			$name.val(complexData['name']);
			$addr.val(complexData['addr']);
			$complexCodePk.val(complexData['complex_code_pk']);
			$homeCnt.val(complexData['home_cnt']);
			$homeDongCnt.val(complexData['floor_cnt']);
			$tel.val(complexData['tel']);
			$fax.val(complexData['fax']);
			$email.val(adminData['email']);
			$buildingArea.val(complexData['building_area']);

			// 수정 불가능 코드
			$complexCodePk.prop('readonly', true).addClass('bcReadonly');
			$email.prop('readonly', true).addClass('bcReadonly');
			$buildingArea.prop('readonly', true).addClass('bcReadonly');
			$homeDongCnt.prop('readonly', true).addClass('bcReadonly');
			$closingDayElectric.prop({
				'readonly': true,
				'disabled' : true
			}).addClass('bcReadonly');

			// adminPk 지정
			self.adminPk = adminData['admin_pk'];
			self.selectedEmail = adminData['email'];
		},
		updateEnergyTargetData: function()
		{
			let self = control;

			let complexData = self.complexData['data'];
			let sensorTypes = self.complexData['types'];

			for (let i = 0; i < sensorTypes.length; i++) {
				let keyName = 'closing_day_' + sensorTypes[i];

				let $selector = $('#' + keyName);
				let $id = $selector.prop('id');

				let fcVal = complexData[keyName];

				if ($id === undefined && fcVal === undefined) {
					continue;
				}

				// 마감일 설정
				$selector.val(fcVal);
			}
		},
		saveData: function($event, $formId, $partId, energyKey, option = '')
		{
			$event.preventDefault();

			// 저장
			let self = control;

			let params  = [];
			let data  = [];

			let errorMessage = '';
			let selectedOption = '';
			let closingDay = '';

			if ($partId == "basic") {
				// 기본정보 저장
				errorMessage = self.basicFormValidate($formId);
			} else {
				// 에너지원 시간정보 저장
				errorMessage = self.energyTargetFormValidate($formId, $partId);
			}

			if (errorMessage != ""){
				alert(errorMessage);
				return;
			}

			if (typeof(option) === 'number') {
				selectedOption = option;
				closingDay = $("#closing_day_" + energyKey).val();
			}

			data.push({ name: 'mode', value: $partId });
			data.push({ name: 'option', value: selectedOption });
			data.push({ name: 'closing_day', value: closingDay });
			data.push({ name: 'admin_pk', value: self.adminPk });
			data.push({ name: 'form_data', value: $formId.serialize() });

			params.push(
				{name: "requester", value: requester},
				{name: "request", value: "set_save"},
				{name: "params", value: JSON.stringify(data)}
			);

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
			if (data['error'] === 'Error') {
				return;
			}

			location.replace(`./index.php?page=${CURRENT_PAGE}`);
		},
		basicFormValidate: function($formId)
		{
			// 기본정보 유효성 체크
			let emailFilter =  /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
			let phoneFilter = /(^02.{0}|^01.{1}|[0-9]{3})([0-9]+)([0-9]{4})/;

			let name = $formId.find("#name").val();
			let addr = $formId.find("#addr").val();
			//let homeDongCnt = $formId.find("#home_dong_cnt").val();
			let tel = $formId.find("#tel").val();
			let fax = $formId.find("#fax").val();
			//let email = $formId.find("#email").val();
			//let buildingArea = $formId.find("#building_area").val();

			if (name.length < 1) {
				return "단지명을 입력하세요.";
			}
			if (name.length > 11) {
				return '이름은 11자 이내로 입력하세요.';
			}

			if (addr.length < 1) {
				return "주소를 입력하세요.";
			}

			if (addr.length > 40) {
				return '주소는 40자 이내로 입력하세요.';
			}

			/*
			if (homeDongCnt.length < 1) {
				return "층 수를 입력하세요";
			} else {
				if ($.isNumeric(homeDongCnt) === false) {
					return "층 수에는 숫자만 입력할 수 있습니다.";
				}
			}
			 */

			tel = tel.replace(/-/gi, '');
			if (tel.length < 1) {
				return "전화번호를 입력하세요";
			} else {
				if ($.isNumeric(tel) === false) {
					return "전화번호에는 숫자만 입력할 수 있습니다.";
				}

				if (tel.length > 11) {
					return '전화번호는 11자리 이상 초과 할 수 없습니다.'
				}
				$formId.find("#tel").val(tel.replace(phoneFilter,"$1-$2-$3"));
			}

			fax = fax.replace(/-/gi, '');
			if (fax.length < 1) {
				return "FAX를 입력하세요";
			} else {
				if ($.isNumeric(fax) === false) {
					return "FAX에는 숫자만 입력할 수 있습니다.";
				}

				if (fax.length > 11) {
					return 'FAX 는 11자리 이상 초과 할 수 없습니다.'
				}
				$formId.find("#fax").val(fax.replace(phoneFilter,"$1-$2-$3"));
			}

			/*
			if (email.length < 1) {
				return "이메일을 입력하세요.";
			} else {
				if (emailFilter.test(email) == false) {
					return "이메일 형식을 확인하세요.";
				}
			}
			 */

			return "";
		},
		energyTargetFormValidate: function($formId, $partId)
		{
			// 에너지원 사용량 유효성 체크
			let $closingDay = $("#closing_day_" + $partId).val();

			// 마감일 있는 항목만 체크한다.
			if(typeof $closingDay != "undefined"){
				if($closingDay.length < 1){
					return "마감일을 선택하세요";
				}else{
					if($.isNumeric($closingDay) == false){
						return "마감일에는 숫자만 입력할 수 있습니다.";
					}
				}
			}

			return "";
		},
		initDays: function()
		{
			// 마감일 콤보 박스에 데이터 추가
			let i = 0;
			let optionData = "";

			// 초기화
			$closingDayElectric.empty();

			// 데이터 추가
			for(i=0; i<28; i++){
				let temp = "<option value='"+(i+1)+"'>" + (i+1) + "</option>";

				optionData += temp;
			}

			// 말일 추가
			optionData += "<option value='99'>말일</option>";

			$closingDayElectric.html(optionData);
		},
	};

	$btnBasicSave.on("click", function(event) {
		control.saveData(event, $formBasic, 'basic');
	});

	$btnElectric.on("click", function(event) {
		alert('마감일은 케빈랩에 문의하세요.');
		//control.saveData(event, $formTimeElectric,'closing_day', 'electric', 0);
	});

	$email.on("keyup", function() {
		alert('단지 담당자 이메일 수정은 케빈랩에 문의 해주세요.');

		// 초기화..
		$(this).val(control.selectedEmail);
	});

	return control;
}