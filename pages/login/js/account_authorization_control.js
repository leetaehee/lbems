let control;

$(document).ready(function() {
    control = createControl();
    control.updateControlStatusResetButton(AUTH_WAIT_SECOND);
});

function createControl()
{
    let control = {
        selectedAuthNumber: '',
        selectedMakeDateTime: '',
        requestAuthNum: function()
        {
            let self = control;

            let params = [];
            let data  = [];

            data.push({ name: 'name', value: $name.val() });
            data.push({ name: 'email', value: $email.val() });

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
            let minute = '';
            let second = '';

            let self = control;
            self.selectedAuthNumber = '';

            if (data['is_validate'] === false) {
                if (data['key'] === 'name' && data['status'] === 'empty') {
                    $labelVaildName.html(VALIDATE_FIND_NAME_VALUE_EMPTY);
                    return;
                }
                if (data['key'] === 'email' && data['status'] === 'empty') {
                    $labelVaildEmail.html(VALIDATE_FIND_EMAIL_VALUE_EMPTY);
                    return;
                }
                if (data['key'] === 'not right') {
                    $labelVaildName.html(VALIDATE_FIND_NAME_VALUE_NOT_RIGHT);
                    $labelVaildEmail.html(VALIDATE_FIND_EMAIL_VALUE_NOT_RIGHT);
                    return;
                }
                if (data['key'] === 'first login') {
                    $labelVaildEmail.html(VALIDATE_FIRST_LOGIN_FAIL);
                    return;
                }
            }

            // 시간설정
            AUTH_WAIT_SECOND = parseInt(DEFAULT_TIME_SECOND);

            self.selectedAuthNumber = data['auth_number'];
            self.selectedMakeDateTime = data['make_datetime'];

            self.updateControlStatusResetButton(AUTH_WAIT_SECOND)

            let timer = setInterval(function() {
                minute = parseInt(AUTH_WAIT_SECOND/60);
                second = parseInt(AUTH_WAIT_SECOND%60);

                AUTH_WAIT_SECOND--;
                if (AUTH_WAIT_SECOND < 0) {
                    clearInterval(timer);
                    self.updateControlStatusResetButton(AUTH_WAIT_SECOND);
                }

                let message = self.setTimerLabel(minute, second, AUTH_WAIT_SECOND);
                $labelAuthTime.html(message);
            }, 1000);
        },
        setTimerLabel: function(m, s, second)
        {
            let label = '';

            if (second < 0) {
                label = '인증번호 요청 시간이 초과 되었습니다. 다시 요청 바랍니다.'
            } else if (second < 60) {
                label = `${s}초`;
            } else {
                label = `${m}분 ${s}초`;
            }

            return label;
        },
        updateControlStatusResetButton: function(second)
        {
            let disabled = true;
            let backgroundColor = PASSWORD_RESET_DISABLED

            if (second > 0) {
                disabled = false;
                backgroundColor = PASSWORD_RESET_ENABLED;
            }

            $btnPasswordResetting.attr("disabled", disabled);
            $btnPasswordResetting.css('background-color', backgroundColor);
        },
        requestReceiveNumber: function()
        {
            let self = control;

            const nameValue = $name.val();
            const emailValue = $email.val();

            $labelVaildEmail.html(EMPTY_VALUE);
            $labelVaildName.html(EMPTY_VALUE);

            if (nameValue.length < 1) {
                $labelVaildName.text(VALIDATE_FIND_NAME_VALUE_EMPTY);
                return;
            }

            if (emailValue.length < 1) {
                $labelVaildEmail.html(VALIDATE_FIND_EMAIL_VALUE_EMPTY);
                return;
            }

            self.requestAuthNum();
        },
        requestChangePassword: function()
        {
            let params = [];
            let data  = [];

            let self = control;
            let authNumber = self.selectedAuthNumber;
            let makeDateTime = self.selectedMakeDateTime;

            if (authNumber === '' && makeDateTime === '') {
                alert('인증번호를 받으시길 바랍니다.');
                return;
            }

            if ($authNum.val().length < 1) {
                alert("인증번호를 입력하세요.");
                return;
            }

            if (AUTH_WAIT_SECOND === 0) {
                alert('인증번호 시간이 만료되었습니다. 다시 요청 해주세요!');
                return;
            }

            data.push({ name: 'name', value: $name.val() });
            data.push({ name: 'email', value: $email.val() });
            data.push({ name: 'auth_number', value: $authNum.val() });

            params.push(
                {name: 'requester', value: requester},
                {name: 'request', value: confirmCommand},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestChangePasswordCallback,
                callbackParams: null,
                showAlert: true
            };

            module.request(requestParams);
        },
        requestChangePasswordCallback: function(data, params)
        {
            if (data['confirm_valid'] === false) {
                alert('인증정보를 확인하여 주세요.');
                return;
            }

            $(location).attr('href', './index.php?page=login/password_change.html');
        }
    };

    $btnReceiveAuth.on('click', function() {
        control.requestReceiveNumber();
    });

    $btnPasswordResetting.on('click', function() {
        control.requestChangePassword();
    });

    return control;
}