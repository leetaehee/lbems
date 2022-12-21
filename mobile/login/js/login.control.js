let control;

$(document).ready(function(){
    control = createControl();
    control.checkAutoLogin();
});

function createControl() {
    let control = {
        selectedAutoLoginChecked: $checkBoxAutoLogin.prop('checked'),
        request: function ()
        {
            let self = control;

            let params = [];
            let data = [];

            let deviceId = module.deviceinfo().getDeviceId();
            let loginKey = module.cookie().getCookie(AutologinKeyCookieLabel);

            // 체크박스 상태 체크하기
            self.setAutoLoginChecked();

            data.push({ name: 'input_id', value: $inputId.val() });
            data.push({ name: 'input_passwd', value: $inputPassword.val() });
            data.push({ name: 'device_id', value: deviceId });
            data.push({ name: 'auto_login', value: self.selectedAutoLoginChecked });
            data.push({ name: 'login_key', value: loginKey});

            params.push(
                {name: 'requester', value: requester},
                {name: 'request', value: command},
                {name: 'params',  value: JSON.stringify(data)}
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

            let id = $inputId.val();
            let deviceId = module.deviceinfo().getDeviceId();

            if (data['is_success'] === false) {
                $checkBoxAutoLogin.prop('checked', false);
                module.cookie().setCookie(AutologinCookieLabel, self.selectedAutoLoginChecked);
                return;
            }

            if (data['id_error'] === true) {
                alert("아이디가 존재 하지 않습니다.")
                return;
            }

            if (data['password_error'] === true) {
                alert("비밀번호를 확인하세요")
                return;
            }

            if (data['first_login'] === true) {
                alert("현재 기본 비밀번호로 지정되어있습니다. 웹에서 로그인을 진행하세요!");
                return;
            }

            if (data['login_blocking'] === true) {
                alert("계정이 정지 되었습니다. 케빈랩에 문의하세요.")
                return;
            }

            if (data['empty'] === true) {
                alert("오류! 다시 로그인을 하세요.")
                return;
            }

            if (data['factory'] === true) {
                alert("현재 지원하지 않은 버전입니다.");
                return; // 공장은 막음..
            }

            if (data['not_access'] === true) {
                alert("현재 지원하지 않은 버전입니다.");
                return;
            }

            module.cookie().setCookie(AutologinIdCookieLabel, id);
            module.cookie().setCookie(AutologinCookieLabel, self.selectedAutoLoginChecked);
            module.cookie().setCookie(AutologinKeyCookieLabel, data['login_key']);
            module.cookie().setCookie(AutologinDeviceKeyCookieLabel, deviceId);

            // 홈 메뉴로 이동
            location.href = "index.php?menu=home";
        },
        checkAutoLogin: function()
        {
            let self = control;

            let auto = module.cookie().getCookie(AutologinCookieLabel) === "true";
            let id = module.cookie().getCookie(AutologinIdCookieLabel);

            self.selectedAutoLoginChecked = auto;

            $inputId.val(id);
            $checkBoxAutoLogin.prop('checked', auto);

            if (auto === true && id.length >= 1) {
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
        updateFindAccountPopup: function(status = 'open')
        {
            if ($searchLayer.css('display') === 'none' && status === 'open') {
                $searchLayer.css('display', 'block');
            } else {
                $searchLayer.css('display', 'none');
            }
        },
    };

    $btnLogin.on("click", function(){
        control.request();
    });

    $btnFindAccount.on("click", function() {
        control.updateFindAccountPopup('open');
    })

    $btnFindAccountClose.on("click", function() {
        control.updateFindAccountPopup('close');
    });

    return control;
}