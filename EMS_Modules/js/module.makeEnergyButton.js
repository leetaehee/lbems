const $energyButtonGroup = $("#energy_btn_group");
const $buttonPrefix = 'btn_';

module.makeEnergyButton = function(autoLoading = true)
{
    let control = {
        _callback : null,
        request: function()
        {
            let self = control;
            let params = [];
            let data = [];

            data.push({ name: 'group', value: group });
            data.push({ name: 'menu', value: menu });

            params.push(
                {name: 'requester', value: 'menu'},
                {name: 'request', value: 'energy_button'},
                {name: 'params', value: JSON.stringify(data)}
            );

            let requestParams = {
                url: requestUrl,
                params: params,
                callback: self.requestCallback,
                callbackParams: [],
                showAlert: true
            };

            module.subRequest(requestParams);
        },
        requestCallback: function(data, params)
        {
            let self = control;
            self.data = data;

            if (data === null) {
                // 에러가 발생한 경우 동적 버튼 기능은 작동하지 않음.
                return;
            }

            $("#energy_btn_group > button").remove();

            // 버튼 생성
            self.updateEnergyTypeButtons();

            // 이벤트 등록
            self.energyButtonClickEvent();

            if (autoLoading === true) {
                // 첫번째 버튼에 트리거 실행하기
                self.updateFirstButtonTrigger();
            }
        },
        updateEnergyTypeButtons: function()
        {
            let self = control;
            let buttonData = self.data;

            let objLength = Object.keys(buttonData).length;
            if (objLength < 1) {
                // 설정된 정보가 없는 경우 실행하지 않는다.
                return;
            }

            // 버튼 동적으로 생성
            $.each(buttonData, function(index, item) {
                let $id = $buttonPrefix + item['key'];
                let $button = $("<button></button>").attr({
                    type: 'button',
                    id: $id,
                }).html(item['name']);
                $energyButtonGroup.append($button);
            });

            let $firstButton = $("#energy_btn_group > button").first();

            // 첫번째 버튼에 클릭효과 주기
            $firstButton.addClass("on");
        },
        updateFirstButtonTrigger: function()
        {
            const $energyButtons = $("#energy_btn_group > button");
            const btnId = $energyButtons.first().prop('id');

            $("#" + btnId).trigger("click");
        },
        energyButtonClickEvent: function()
        {
            let self = control;
            let buttons = self.data;

            if (self._callback instanceof Function == false) {
                return;
            }

            $.each(buttons, function(index, item){
                let $btnId = 'btn_' + item['key'];

                $("#" + $btnId).on("click", function(){
                    self._callback($(this), parseInt(item['reference_index']));
                });
            });
        },
    };

    return control;
}
