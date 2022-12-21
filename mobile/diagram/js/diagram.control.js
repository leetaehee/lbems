let control;

$(document).ready(function(){
	control = createControl();
	control.requestKeyInfo();
	control.request();
});

function createControl()
{
	let control = {
		selectedDateType : DEFAULT_DATE_TYPE,
		request: function()
		{
			let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'date_type',  value: self.selectedDateType });

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

            if (data === undefined || data === null || data === '') {
            	return;
			}

            self.data = data;

            self.clearLabel();
			self.updateIndependenceData(); // 자립률 정보 표기
			self.updateDiagramData(); // 계통도 주기별로 사용량 출력
		},
		onPeriodButtonClicked: function($this, dateType)
		{
			let self = control;

			// 버튼에 CSS 변경처리
			$.each($periodButtons, function(index, item) {
				$(this).removeClass("on");
			});
			$this.addClass('on');

			// 주기 
			self.selectedDateType = dateType;
			self.request();
		},
		updateIndependenceData: function()
		{
			let self = control;
			let data = self.data['independence_data'];

			let rate = data['rate'];

			$labelIndependenceGrade.html(data['grade']); // 자립률 등급
			$labelIndependenceRate.html(module.utility.addComma(rate.toFixed(0))); // 에너지자립률
		},
		updateDiagramData: function()
		{
			let self = control;

			let diagramData = self.data['diagram_data'];
			if (diagramData === undefined || diagramData === '' || diagramData === null) {
				return;
			}

			$.each(diagramData, function(key, value) {
				$("#" + LABEL_PREFIX + key + USED_SUFFIX).html(module.utility.addComma(value.toFixed(0)));
			});
		},
		clearLabel: function()
		{
			$labelIndependenceGrade.html('-');
			$labelIndependenceRate.html(0);

			$.each($labelUseds, function(index, item) {
				item.html(0);
			});
		},
		requestKeyInfo: function()
		{
			const USE_KEY_DATA = CONFIGS['mobile']['diagram'];

			$.each(USE_KEY_DATA, function(index, item){
				let $labelUsed = $("#" + LABEL_PREFIX + item + USED_SUFFIX);
				$labelUseds.push($labelUsed);
			});
		},
	};

	$btnDaily.on('click', function() {
		control.onPeriodButtonClicked($(this), DAILY_DATE_TYPE);
	});

	$btnMonth.on('click', function() {
		control.onPeriodButtonClicked($(this), MONTH_DATE_TYPE);
	});

	$btnYear.on('click', function() {
		control.onPeriodButtonClicked($(this), YEAR_DATE_TYPE);
	});

	return control;
}