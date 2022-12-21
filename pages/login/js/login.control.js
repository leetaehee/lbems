let control;

$(document).ready(function() {
    control = createControl();
    control.checkAutoLogin();
});

function createControl()
{
    let control = {
        selectedAutoLoginChecked: $checkBoxAutoLogin.prop('checked'),
        request: function()
        {
            let self = control;
            let params = [];
            let data = [];

            let deviceId = module.deviceinfo().getDeviceId();
            let loginKey = module.cookie().getCookie(AutologinKeyCookieLabel);

            // 체크박스 상태 체크하기
            self.setAutoLoginChecked();

            data.push({ name: 'input_id', value: $inputId.val() });
            data.push({ name: 'input_passwd', value: $inputPasswd.val() });
            data.push({ name: 'device_key', value: deviceId });
            data.push({ name: 'auto_login', value: self.selectedAutoLoginChecked });
            data.push({ name: 'login_key', value: loginKey});

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

            let deviceId = module.deviceinfo().getDeviceId();
            const result = data['result'];

            if (data['is_success'] === false) {
                $checkBoxAutoLogin.prop('checked', false);
                module.cookie().setCookie(AutologinCookieLabel, self.selectedAutoLoginChecked);
                return;
            }

            if (data['password_error'] === true
                && data['first_login'] === true) {
                $labelPasswordErrorMessage.html(VALIDATE_PASSWORD_CHECK_NOT_RIGHT);
                findAccountPopup.open();
                return;
            }

            if (data['first_login'] === true) {
                $(location).attr('href', self.getIndexPageUrl(REQUEST_PASSWORD_CHANGE_URL));
                return;
            }
            if (data['id_error'] === true) {
                $labelIdErrorTitle.html(ID_ERROR_TITLE);
                $labelIdErrorMessage.html(VALIDATE_ID_CHECK_EXIST);
                findIdPopup.open();
                return;
            }

            if (data['password_error'] === true) {
                $labelPasswordErrorMessage.html(VALIDATE_PASSWORD_CHECK_NOT_RIGHT);
                findAccountPopup.open();
                return;
            }

            if (data['login_blocking'] === true) {
                $labelPasswordErrorMessage.html(VALIDATE_ACCOUNT_NOT_RIGHT);
                initializePasswordPopup.open();
                return;
            }

            if (data['empty'] === true) {
                findAccountPopup.open();
                return;
            }

            if (data['dashboard_error'] !== undefined
                && data['dashboard_error'] === true) {
                alert(MANAGER_INQUIRY_MESSAGE);
                return;
            }

            let id = $inputId.val();

            module.cookie().setCookie(AutologinIdCookieLabel, id);
            module.cookie().setCookie(AutologinCookieLabel, self.selectedAutoLoginChecked);
            module.cookie().setCookie(AutologinKeyCookieLabel, data['login_key']);
            module.cookie().setCookie(AutologinDeviceKeyCookieLabel, deviceId);

            if (result.length === 0) {
                location.reload();
                return;
            }

           location.href =`./index.php?page=${result['url']}&group=${result['group_id']}&menu=${result['menu_id']}`;
        },
        requestLoginValidate: function($this)
        {
            let self = control;

            // 브라우저 체크
            let isAllow = self.getBrowserCheck();
            if (isAllow === false) {
                alert('인터넷 익스플로러에서는 사용 할 수없습니다. 크롬 브라우저를 이용하세요.');
                return;
            }

            if ($inputId.val() === '') {
                alert('아이디를 입력하세요.');
                return;
            }

            if ($inputPasswd.val() === '') {
                alert('비밀번호를 입력하세요.');
                return;
            }

            self.request();
        },
        checkAutoLogin: function()
        {
            let self = control;

            let auto = module.cookie().getCookie(AutologinCookieLabel) === 'true';
            let id = module.cookie().getCookie(AutologinIdCookieLabel);

            self.selectedAutoLoginChecked = auto;

            $inputId.val(id);
            $checkBoxAutoLogin.prop('checked', auto);

            if (auto == true && id.length >= 1) {
                self.request();
            }
        },
        setAutoLoginChecked: function()
        {
            let self = control;

            self.selectedAutoLoginChecked = false;

            if ($checkBoxAutoLogin.prop('checked') === true) {
                self.selectedAutoLoginChecked = true;
            }
        },
        getBrowserCheck: function()
        {
            let agent = navigator.userAgent.toLowerCase();
            let isAllow = true;

            if ((navigator.appName === 'Netscape' && navigator.userAgent.search('Trident') !== -1)
                || (agent.indexOf('mise') != -1)) {
                isAllow = false;
                return isAllow;
            }

            return isAllow;
        },
        getIndexPageUrl: function(page)
        {
            return `./index.php?page=${page}`;
        },
    };

    $btnLogin.on('click', function() {
        control.requestLoginValidate($(this));
    });

    $btnFindAccount.on('click', function() {
        $(location).attr('href', control.getIndexPageUrl(FIND_ACCOUNT_URL));
    });

    $btnPlayLogin.on('click', function() {
        findAccountPopup.close();
    });

    $btnPopupFindId.on('click', function(){
        $labelIdErrorTitle.html(FIND_ID_NOTICE_TITLE);
        $labelIdErrorMessage.html(FIND_ID_NOTICE_CONTENT);
        findIdPopup.open();
    });

    $btnFindIdPopupClose.on('click', function() {
        findIdPopup.close();
    });

    $btnPasswordResetting.on('click', function() {
        $(location).attr('href', control.getIndexPageUrl(FIND_ACCOUNT_URL));
    });

    return control;
}