const MENU_REQUESTER = 'menu';
const MENU_REQUEST = 'menu_location';
const DOMAIN = window.location.origin;

module.MenuModule = function()
{
    let control = {
        requestPageLocation: function(fileGroup, fileName, menuName, groupPage, groupId, menuId = 0)
        {
            let self = control;
            let params = [];
            let data = [];

            if (groupId === '') {
                groupId = 0;
            }

            data.push({ name: 'group_id', value: parseInt(groupId) });
            data.push({ name: 'menu_id', value: parseInt(menuId) });
            data.push({ name: 'menu_group', value: fileGroup });
            data.push({ name: 'menu_link', value: fileName });
			data.push({ name: 'group_page', value: groupPage });
            data.push({ name: 'menu_name', value: menuName });

            params.push(
                {name: 'requester', value: MENU_REQUESTER},
                {name: 'request', value: MENU_REQUEST},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestPageLocationCallback,
                callbackParams: [],
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestPageLocationCallback: function(data, params)
        {
            if (data == null) {
                return;
            }

            let menus = data['menu'];

            let groupId = parseInt(menus['group_id']);
            let menuId = parseInt(menus['menu_id']);
            let fileName = menus['url'];

            if (Object.keys(menus).length === 0) {
                return;
            }

            let url = DOMAIN + "/pages/index.php?page=" + fileName;

            if (menuId > 0) {
                // 서브가 메뉴가 있는 경우
                url += "&group=" + groupId + "&menu=" + menuId;
            } else {
                // 서브 메뉴가 없는 경우
                url += "&group=" + groupId;
            }

            $(location).attr("href", url);
        }
    };

    return control;
}
