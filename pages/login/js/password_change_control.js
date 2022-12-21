let control;

$(document).ready(function() {
    control = createControl();
    control.updateControlStatusChangeButton(false);
});

function createControl()
{
    let control = {
        selectedValid: false,
        request: function()
        {
            let self = control;
            let isValid = self.selectedValid;

            self.updateControlStatusChangeButton(isValid);

            if (isValid === false) {
                return;
            }

            let params = [];
            let data  = [];

            data.push({ name: 'old_password', value: $oldPassword.val() });
            data.push({ name: 'new_password', value: $newPassword.val() });
            data.push({ name: 're_password', value: $rePassword.val() });

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
            let errorType = data['error'];

            if (errorType === 'password_wrong') {
                $labelValidPasswordMsg.html(VALIDATE_PASSWORD_RULE_VALUE_VIOLATION);
                return;
            }

            if (errorType === 'same_password') {
                $labelValidPasswordMsg.html(VALIDATE_PASSWORD_OLD_VALUE_SAME);
                return;
            }

            if (data['error'] === 'not_old_password') {
                $labelValidPasswordMsg.html(VALIDATE_PASSWORD_CURRENT_VALUE_SAME);
                return;
            }

            if (errorType === 'late_time') {
                // 인증번호 초과시 다시 인증번호 발급받는 페이지로..
                $labelPasswordError.html(VALIDATE_AUTH_TIME_OVER);
                changePasswordPopup.open();
            }

            if (errorType === 'success'
                && !alert(PASSWORD_CHANGE_SUCCESS_MESSAGE)) {
                // 비밀번호 변경 성공시에는 로그인 페이지로..
                $(location).attr('href', './index.php');
            }
        },
        findPasswordProcess: function()
        {
            let self = control;

            const newPasswordValue = $newPassword.val();
            const rePasswordValue = $rePassword.val();

            const ruleResult1 = PASSWORD_RULES['rule1'].test(newPasswordValue);
            const ruleResult2 = PASSWORD_RULES['rule2'].test(newPasswordValue);
            const ruleResult3 = PASSWORD_RULES['rule3'].test(newPasswordValue);
            const ruleResult4 = PASSWORD_RULES['rule4'].test(newPasswordValue);

            $labelValidPasswordMsg.html(EMPTY_VALUE);
            $labelValidPasswordConfirmMsg.html(EMPTY_VALUE);

            if (newPasswordValue.length < 1) {
                $labelValidPasswordMsg.html(VALIDATE_PASSWORD_NEW_VALUE_EMPTY);
                self.updateControlStatusChangeButton(false);
                return;
            } else {
                self.updateControlStatusChangeButton(true);
            }

            if (rePasswordValue.length < 1) {
                $labelValidPasswordConfirmMsg.html(VALIDATE_PASSWORD_CONFIRM_VALUE_EMPTY);
                self.updateControlStatusChangeButton(false);
                return;
            } else {
                self.updateControlStatusChangeButton(true);
            }

            if (newPasswordValue !== rePasswordValue) {
                $labelValidPasswordConfirmMsg.html(VALIDATE_PASSWORD_BOTH_VALUE_SAME);
                self.updateControlStatusChangeButton(false);
                return;
            } else {
                self.updateControlStatusChangeButton(true);
            }

            if (!(ruleResult1 || ruleResult2 || ruleResult3 || ruleResult4)) {
                $labelValidPasswordMsg.html(VALIDATE_PASSWORD_RULE_VALUE_VIOLATION_LABEL);
                self.updateControlStatusChangeButton(false);
                return;
            } else {
                self.updateControlStatusChangeButton(true);
            }
        },
        updateControlStatusChangeButton: function(isValid)
        {
            let self = control;
            self.selectedValid = isValid;

            let disabled = true;
            let backgroundColor = PASSWORD_CHANGE_DISABLED;

            if (isValid === true) {
                disabled = false;
                backgroundColor = PASSWORD_CHANGE_ENABLED;
            }

            $btnPasswordChange.attr("disabled", disabled);
            $btnPasswordChange.css('background-color', backgroundColor);
        },
        resetForm: function()
        {
            $oldPassword.val(EMPTY_VALUE);
            $newPassword.val(EMPTY_VALUE);
            $rePassword.val(EMPTY_VALUE);
            $labelValidPasswordMsg.html(EMPTY_VALUE);
        },
    };

    $rePassword.on('keyup', function(){
       control.findPasswordProcess();
    });

    $btnPasswordChange.on('click', function(){
        control.request();
    });

    $btnPasswordReset.on('click', function(){
        control.resetForm();
    });

    $btnPopupConfirm.on('click', function(){
        changePasswordPopup.close();
        $(location).attr('href', `./index.php?page=${FIND_ACCOUNT_URL}`);
    });

    return control;
}