let control;

$(document).ready(function() {
	$btnLoginFirstPage.css('visibility', 'hidden');
	$btnLoginPrevPage.css('visibility', 'hidden');
	$btnLoginNextPage.css('visibility', 'hidden');
	$btnLoginLastPage.css('visibility', 'hidden');

    let date = new Date();

	createDatepicker($startDate, true);
	createDatepicker($endDate);

	let buildingList = module.BuildingList($buildingType);
	buildingList.request();

	control = createControl();
	control.requestMenuAuthority(group);
});

function createDatepicker($id, beforeDate = false)
{
	let dateStr = '';
	let date = module.utility.getBaseDate();

	if (beforeDate == true) {
		date.setMonth(date.getMonth()-1)
	}

	dateStr = $.datepicker.formatDate('yy-mm-dd', date);

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
				"formData" : $formLoginLogId.serialize()
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
			self.updateLoginLogData();
		},
		updateLoginLogData: function()
		{
			let self = control;
			let loginLogData = self.data['log_data'];

			let loginLogList = loginLogData['list'];
			let total = loginLogData['count'];

			let i;
			let $trLoginLogList = "";

			if (total > 0) {
				$("#tbody_login_log > tr").remove();

				for (let i = 0; i < loginLogList.length; i++) {
					let loginLogs = loginLogList[i];

					$trLoginLogList += "<tr>";
					$trLoginLogList += "<td>"+loginLogs["log_date"]+"</td>";
					$trLoginLogList += "<td>"+loginLogs["admin_id"]+"</td>";
					$trLoginLogList += "<td>"+loginLogs["name"]+"</td>";
					$trLoginLogList += "<td>"+loginLogs["ip_addr"]+"</td>";
					$trLoginLogList += "<td>"+loginLogs["fg_login"]+"</td>";
					$trLoginLogList += "<td>"+loginLogs["user_agent"]+"</td>";
					$trLoginLogList += "</tr>";
				}
			} else {
				$trLoginLogList += "<tr><td colspan='6'>- 조회 된 데이터가 존재하지 않습니다. -</td></tr>";
			}

			$tbodyLoginLog.html($trLoginLogList);
			
			// 페이징처리
			let pageParam = {
				total: total,
				currentPage: self.selectedStartPage,
				pageCount: pageCount,
				viewPageCount: viewPageCount, 
				id: "login_paging",
				key: "login"
			};

			module.page(pageParam);

			// 마지막페이지
			self.totalPage = Math.ceil(total/viewPageCount);
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

			module.request(requestParams);
		},
		requestMenuAuthorityCallback: function(data, params)
		{
			let self = control;

			if (data['error'] === 'data-error') {
				return;
			}

			if (data['authority'] < 100) {
				$loginLogPageIcon.attr('src', settingPageIcon);
				$divBuildingSelectBox.css('display', 'none');
				$menuGroupSelector.html(data['group_name']);
				isDisabledBuildingSelectBox = false;

				self.request();
			} else {
				$menuGroupSelector.html(defaultMenuGroupName);
			}
		},
	};

	$btnSearch.on("click", function() {
		let self = control;

		if (isDisabledBuildingSelectBox === true && $buildingType.val() === '') {
			alert('건물을 선택하세요.');
			$buildingType.focus();
			return;
		}

		self.selectedStartPage = 1;
		self.request();
	});

	$btnLoginFirstPage.on("click", function() {
		let self = control;

		self.selectedStartPage = 1;
		self.request();
	});

	$btnLoginPrevPage.on("click", function() {
		let self = control;

		self.selectedStartPage = Number(self.selectedStartPage - 1);
		self.request();
	});

	$btnLoginNextPage.on("click", function() {
		let self = control;

		self.selectedStartPage = Number(self.selectedStartPage + 1);
		self.request();
	});

	$btnLoginLastPage.on("click", function() {
		let self = control;

		self.selectedStartPage = Number(self.totalPage);
		self.request();
	});

	$(document).on("click", ".paging_click", function(e) {
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