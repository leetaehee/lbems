let frameControl;

$(document).ready(function(){
	frameControl = createFrameControl();
	frameControl.loadingProgress(); // 로딩바
	frameControl.showInfoDetailMenu(selectedMenu); // 디테일 메뉴 보여주기
	frameControl.request(); // 프레임에서 필요한 데이터 조회
});

function createFrameControl()
{
	let frameControl = {
		request: function() 
		{
			let self = frameControl;
            let params = [];

			params.push(
				{name: 'requester', value: frameRequester},
				{name: 'request', value: frameCommand}
			);

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: self.requestCallback,
				callbackParams: null,
				showAlert: true
			};

			module.subRequest(requestParams);
		},
		requestCallback: function(data)
		{
			let self = frameControl;

			let buildingData = data['building_data'];
			let menuData = data['menu_data'];

			// 건물정보 출력
			self.updateBuildingData(buildingData);
			// 사이드 메뉴 정보 생성
			self.updateSideMenu(menuData);
			// 탭 메뉴 정보 생성
			self.updateTabMenu(menuData, selectedMenu);
		},
		requestLogout: function() 
		{
			let self = frameControl;
			let params = [];

			params.push(
				{ name: 'requester', value: 'login' },
				{ name: 'request', value: 'logout' }
			);

			let requestParams = {
				url: requestUrl,
				params: params,
				callback: self.requestLogoutCallback,
				callbackParams: null,
				showAlert: true,
			};

			module.request(requestParams);
		},
		requestLogoutCallback: function()
		{
			module.cookie().removeCookie('bems_admin_auto_login_', '');
            module.cookie().removeCookie('bems_admin_auto_login_id_', false);
            module.cookie().removeCookie('bems_admin_auto_login_key_', '');
            module.cookie().removeCookie('bems_admin_device_key_', '');

            location.href = URL_PATH;
		},
		updateBuildingData: function(data)
		{
			// 건물정보 출력
			$("#label_popup_building_name").html(data['name']);
			$("#label_popup_building_addr").html(data['addr']);
		},
		updateSideMenu: function(data)
		{
			// 메뉴 정보 출력
			let groups = data['groups'];
			let $sideMenu = $("#side_menu");

			$.each(groups, function(key, items) {
				let $aTag;
				let $liTag;

				let subMenus = items['sub_menu'];

				if (subMenus === undefined) {
					$aTag = $("<a></a>").attr({ href : URL_PATH + '?menu=' + key }).html(items['name']);
					$liTag = $("<li></li>").html($aTag);
				}

				if (subMenus !== undefined) {
					let $ul = $("<ul></ul>");
					let $sublist;

					// 서브 메뉴 클릭하기 위한 메인 <a>태그
					let $mainLink = $("<a></a>").attr({class: 'lnb_up btn_side_fold'}).html(items['name']);

					// 서브 링크 생성
					$.each(subMenus, function(subItemKey, subItems) {
						let $subALinkTag = $("<a></a>").attr({ href: URL_PATH + '?menu=' + subItemKey }).html(subItems['name']);

						$sublist = $("<li></li>").html($subALinkTag);
						$ul.append($sublist);
					});

					// 서브 링크 추가
					$liTag = $("<li></li>").attr('class', 'dept').append($mainLink).append($ul);
				}

				// 추가
				$sideMenu.append($liTag);
			});
		},
		updateTabMenu: function(data, selectedMenu)
		{
			// 메뉴 정보 출력
			let groups = data['groups'];
			let $tabMenu = $("#tab_menu");

			$.each(groups, function(key, items) {
				let $aTag;
				let $liTag;

				let subMenus = items['sub_menu'];

				let buttonClickStatus = '';
				if (key === selectedMenu) {
					buttonClickStatus = 'on';
				}

				if (subMenus === undefined) {
					$aTag = $("<a></a>").attr({
						href : URL_PATH + '?menu=' + key,
						id: BYN_NAV_SELECTOR_ID + key,
						class: 'btn_tab',
					}).html(items['name']);
				}

				if (subMenus !== undefined) {
					let keys = Object.keys(subMenus);
					let types = selectedMenu.split('_');
					if (types[0] === key) {
						buttonClickStatus = 'on';
					}

					$aTag = $("<a></a>").attr({
						href : URL_PATH + '?menu=' + keys[0],
						id: BYN_NAV_SELECTOR_ID + key,
						class: 'btn_tab',
					}).html(items['name']);
				}

				$liTag = $("<li></li>").attr('class', buttonClickStatus).html($aTag);

				// 추가
				$tabMenu.append($liTag);
			});
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
		showInfoDetailMenu: function(menu)
		{
			let menuType = menu;

			$.each($detailMenus, function(index, item){
				if (menu.indexOf(item) !== -1) {
					menuType = item;
					return false;
				}
			});

			const $divMenuSelector = $("#div_" + menuType + "_detail_menu");
			if (menuType !== "") {
				$divMenuSelector.css("display", "block");

				// 대메뉴 이벤트 변경
				$("#tab_menu > li").removeClass("on");
				$("#btn_nav_" + menuType).addClass("on");

				// 소메뉴 이벤트 변경
				$("#div_" + menuType + "_detail_menu > ul > li").removeClass("on");
				$("#btn_" + menu).addClass("on");
			}
		},
		onClickSideMenuFold: function($this)
		{
			let $ul = $this.closest('li').find('ul');
			let displayStatus = $ul.css('display');

			switch (displayStatus)
			{
				case 'block':
					$ul.slideUp('slow');
					break;
				case 'none':
					$ul.slideDown('slow');
					break;
			}
		},
	};

	$("#btn_logout").on('click', function(){
		frameControl.requestLogout();
	});

	$("#btn_navigation_menu").on('click', function(){
		$(".allMenuLayer").css('display', 'block');
	});

	$("#btn_close_layer").on('click', function(){
		$(".allMenuLayer").css('display', 'none');
	});

	$(document).on("click", ".btn_side_fold", function(){
		frameControl.onClickSideMenuFold($(this));
	});

	return frameControl;
}