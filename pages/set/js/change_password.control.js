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

            let params  = [];
            let data  = [];

            data.push({ name: 'old_password', value: $oldPassword.val() });
            data.push({ name: 'new_password', value: $newPassword.val() });
            data.push({ name: 're_password', value: $rePassword.val() });
            data.push({ name: 'is_session', value: true })

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

            module.subRequest(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;

            if (data['error'] === 'password_wrong') {
                //self.popupMessage(VALIDATE_PASSWORD_RULE_VALUE_VIOLATION);
                $labelValidMessage.html(VALIDATE_PASSWORD_RULE_VALUE_VIOLATION);
                return;
            }

            if (data['error'] === 'same_password') {
                //self.popupMessage(VALIDATE_PASSWORD_OLD_VALUE_SAME);
                $labelValidMessage.html(VALIDATE_PASSWORD_OLD_VALUE_SAME);
                return;
            }

            if (data['error'] === 'not_old_password') {
                //self.popupMessage(VALIDATE_PASSWORD_CURRENT_VALUE_SAME);
                $labelValidMessage.html(VALIDATE_PASSWORD_CURRENT_VALUE_SAME);
                return;
            }

            if (data['error'] === 'success') {
                successPasswordPopup.open();
            }
        },
        findPasswordProcess: function()
        {
            let self = control;

            const oldPasswordValue = $oldPassword.val();
            const newPasswordValue = $newPassword.val();
            const rePasswordValue = $rePassword.val();

            const ruleResult1 = passwordRules['rule1'].test(newPasswordValue);
            const ruleResult2 = passwordRules['rule2'].test(newPasswordValue);
            const ruleResult3 = passwordRules['rule3'].test(newPasswordValue);
            const ruleResult4 = passwordRules['rule4'].test(newPasswordValue);

            $labelValidMessage.html('');

            if (oldPasswordValue.length < 1) {
                $labelValidMessage.html(VALIDATE_PASSWORD_OLD_VALUE_EMPTY);
                self.updateControlStatusChangeButton(false);
                return;
            } else {
                self.updateControlStatusChangeButton(true);
            }

            if (newPasswordValue.length < 1) {
                $labelValidMessage.html(VALIDATE_PASSWORD_NEW_VALUE_EMPTY);
                self.updateControlStatusChangeButton(false);
                return;
            } else {
                self.updateControlStatusChangeButton(true);
            }

            if (rePasswordValue.length < 1) {
                $labelValidMessage.html(VALIDATE_PASSWORD_CONFIRM_VALUE_EMPTY);
                self.updateControlStatusChangeButton(false);
                return;
            } else {
                self.updateControlStatusChangeButton(true);
            }

            if (newPasswordValue !== rePasswordValue) {
                $labelValidMessage.html(VALIDATE_PASSWORD_BOTH_VALUE_SAME);
                self.updateControlStatusChangeButton(false);
                return;
            } else {
                self.updateControlStatusChangeButton(true);
            }

            if (!(ruleResult1 || ruleResult2 || ruleResult3 || ruleResult4)) {
                $labelValidMessage.html(VALIDATE_PASSWORD_RULE_VALUE_VIOLATION_LABEL);
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

            $labelValidMessage.html(EMPTY_VALUE);
        },
        popupMessage: function(message)
        {
            $labelPasswordError.html(message);
            changePasswordPopup.open();
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
    });

    $btnPopupSuccessConfirm.on('click', function(){
        frameControl.requestLogout(false);
    });

    return control;
}