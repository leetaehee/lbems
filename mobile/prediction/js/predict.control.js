let control;

$(document).ready(function(){
	control = createControl();
	control.request();
});
function createControl()
{
	let control = {
		selectedOption: DEFAULT_OPTION,
		request: function()
		{
			let self = control;

            let params = [];
            let data = [];

            data.push({ name: 'option', value: self.selectedOption });

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

            self.data = data;

			// 예측 프로그레스바 표시
			self.updateProgressRate();
		},
		updateProgressRate: function()
		{
			let self = control;

			let option = self.selectedOption;
			let predictData = self.data['predict_data'];

			let units = module.utility.getBemsUnits2();
			let unit = units[option];

			$.each(predictData, function(key, items) {
				let currentValue = parseInt(items['current']['data']);
				let predictValue = parseInt(items['predict']['data']);

				let rate = DATE_TYPE_USED_RATES[key];
				let currentPercent = self.getProgressRate(currentValue, rate);
				let predictPercent = self.getProgressRate(predictValue, rate);

				$("#label_" + key + "_current_used_progressbar").css('width', currentPercent + '%');
				$("#label_" + key + "_current_used_progressbar").html(module.utility.addComma(currentValue) + ' ' + unit);

				$("#label_" + key + "_except_used_progressbar").css('width', predictPercent + '%');
				$("#label_" + key + "_except_used_progressbar").html(module.utility.addComma(predictValue) + ' ' + unit);
			});
		},
		getProgressRate: function(rate, val)
		{
			let progressRate = Math.floor((rate/(val * BASE_VAL)));

			if (progressRate === 0) {
				progressRate = 25;
			} else {
				progressRate = (progressRate < 30) ? 27 : progressRate;
			}

			return module.utility.getValidPercent(progressRate);
		},
	};

	return control;
}